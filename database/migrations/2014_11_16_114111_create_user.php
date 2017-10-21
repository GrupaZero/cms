<?php

use Gzero\Entity\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUser extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'users',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('nick')->unique()->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->boolean('is_admin')->default(false);
                $table->rememberToken();
                $table->timestamps();
            }
        );

        // Seed users
        $this->seedUsers();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }

    /**
     * Seed users
     *
     * @return void
     */
    private function seedUsers()
    {
        // Create user
        $user = User::create(
            [
                'email'     => 'admin@gzero.pl',
                'nick'  => 'Admin',
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'password'  => Hash::make('test')
            ]
        );

        $user->is_admin = 1;
        $user->save();
    }
}
