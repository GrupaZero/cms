<?php

use Gzero\Entity\Permission;
use Gzero\Entity\Role;
use Gzero\Entity\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesAndPermissionsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'ACLRoles',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
            }
        );

        Schema::create(
            'ACLPermissions',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('category');
            }
        );

        Schema::create(
            'ACLRolePermissions',
            function (Blueprint $table) {
                $table->integer('permissionId')->unsigned()->index();
                $table->integer('roleId')->unsigned()->index();
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
                $table->foreign('permissionId')->references('id')->on('ACLPermissions')->onDelete('CASCADE');
                $table->foreign('roleId')->references('id')->on('ACLRoles')->onDelete('CASCADE');

            }
        );

        Schema::create(
            'ACLUserRoles',
            function (Blueprint $table) {
                $table->integer('userId')->unsigned()->index();
                $table->integer('roleId')->unsigned()->index();
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
                $table->foreign('userId')->references('id')->on('Users')->onDelete('CASCADE');
                $table->foreign('roleId')->references('id')->on('ACLRoles')->onDelete('CASCADE');

            }
        );

        $this->createRolesAndPermissions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ACLUserRoles');
        Schema::dropIfExists('ACLRolePermissions');
        Schema::dropIfExists('ACLPermissions');
        Schema::dropIfExists('ACLRoles');
    }

    /**
     * It creates base permissions
     */
    private function createRolesAndPermissions()
    {
        $permissions = [];

        // Core permissions
        $permissions[] = [
            'id'       => 1,
            'name'     => 'admin-api-access',
            'category' => 'general'
        ];

        // Resources permissions - we use first 200 for core permissions
        $id       = 200;
        $entities = ['content', 'block', 'user', 'file', 'role'];
        foreach ($entities as $entity) {
            $permissions[] = [
                'id'       => $id++,
                'name'     => $entity . '-create',
                'category' => $entity
            ];
            $permissions[] = [
                'id'       => $id++,
                'name'     => $entity . '-read',
                'category' => $entity
            ];
            $permissions[] = [
                'id'       => $id++,
                'name'     => $entity . '-update',
                'category' => $entity
            ];
            $permissions[] = [
                'id'       => $id++,
                'name'     => $entity . '-delete',
                'category' => $entity
            ];
        }

        // Options are different
        $permissions[] = [
            'id'       => $id++,
            'name'     => 'options-read',
            'category' => 'options'
        ];
        $permissions[] = [
            'id'       => $id++,
            'name'     => 'options-update-general',
            'category' => 'options'
        ];
        $permissions[] = [
            'id'       => $id++,
            'name'     => 'options-update-seo',
            'category' => 'options'
        ];

        Permission::insert($permissions);

        $adminRole = Role::create(['name' => 'Admin']);
        $user      = User::find(1);
        $user->roles()->attach($adminRole);
        $adminRole->permissions()->attach(Permission::all(['id'])->pluck('id')->toArray());

        $moderatorRole = Role::create(['name' => 'Moderator']);
        $permissionIds = Permission::whereIn('category', ['block', 'content', 'file'])
            ->orWhereIn('name', ['admin-api-access'])
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $moderatorRole->permissions()->attach($permissionIds);

    }
}
