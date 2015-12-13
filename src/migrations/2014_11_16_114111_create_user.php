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
            'Users',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('firstName')->nullable();
                $table->string('lastName')->nullable();
                $table->string('rememberToken');
                $table->boolean('isAdmin')->default(0);
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
            }
        );

        Schema::create(
            'PasswordReminders',
            function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token')->index();
                $table->timestamp('created_at');
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
        Schema::dropIfExists('PasswordReminders');
        Schema::dropIfExists('Users');
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
                'firstName' => 'John',
                'lastName'  => 'Doe',
                'password'  => Hash::make('test')

            ]
        );

        $user->isAdmin = 1;
        $user->save();
    }
}
