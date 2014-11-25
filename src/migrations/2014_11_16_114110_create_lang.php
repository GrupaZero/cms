<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLang extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'Langs',
            function (Blueprint $table) {
                $table->string('code', 2)->index();
                $table->string('i18n', 5);
                $table->boolean('isEnabled');
                $table->boolean('isDefault');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('Langs');
    }

}
