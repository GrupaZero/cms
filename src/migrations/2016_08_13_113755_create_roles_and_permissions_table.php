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
        $entities    = ['content', 'block', 'user', 'file'];
        foreach ($entities as $entity) {
            $permissions[] = [
                'name'     => $entity . '-create',
                'category' => $entity
            ];
            $permissions[] = [
                'name'     => $entity . '-read',
                'category' => $entity
            ];
            $permissions[] = [
                'name'     => $entity . '-update',
                'category' => $entity
            ];
            $permissions[] = [
                'name'     => $entity . '-delete',
                'category' => $entity
            ];
        }

        // Options are different
        $permissions[] = [
            'name'     => 'options-read',
            'category' => 'options'
        ];
        $permissions[] = [
            'name'     => 'options-update-general',
            'category' => 'options'
        ];
        $permissions[] = [
            'name'     => 'options-update-seo',
            'category' => 'options'
        ];

        Permission::insert($permissions);

        $adminRole = Role::create(['name' => 'Admin']);
        $user      = User::find(1);
        $user->roles()->attach($adminRole);
        $adminRole->permissions()->attach(Permission::all(['id'])->pluck('id')->toArray());

        $moderatorRole = Role::create(['name' => 'Moderator']);
        $permissionIds = Permission::whereIn('category', ['block', 'content', 'files'])
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $moderatorRole->permissions()->attach($permissionIds);

    }
}
