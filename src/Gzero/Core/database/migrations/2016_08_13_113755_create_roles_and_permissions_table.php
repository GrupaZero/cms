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
            'acl_roles',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->timestamps();
            }
        );

        Schema::create(
            'acl_permissions',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('category');
                $table->timestamps();
            }
        );

        Schema::create(
            'acl_permission_role',
            function (Blueprint $table) {
                $table->integer('permission_id')->unsigned()->index();
                $table->integer('role_id')->unsigned()->index();
                $table->timestamps();
                $table->foreign('permission_id')->references('id')->on('acl_permissions')->onDelete('CASCADE');
                $table->foreign('role_id')->references('id')->on('acl_roles')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'acl_user_role',
            function (Blueprint $table) {
                $table->integer('user_id')->unsigned()->index();
                $table->integer('role_id')->unsigned()->index();
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
                $table->foreign('role_id')->references('id')->on('acl_roles')->onDelete('CASCADE');

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
        Schema::dropIfExists('acl_user_role');
        Schema::dropIfExists('acl_permission_role');
        Schema::dropIfExists('acl_permissions');
        Schema::dropIfExists('acl_roles');
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
