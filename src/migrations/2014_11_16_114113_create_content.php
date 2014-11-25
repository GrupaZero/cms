<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContent extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'Contents',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('path')->default('/');
                $table->integer('weight');
                $table->boolean('isActive');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
            }
        );

        Schema::create(
            'ContentTranslations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('langCode', 2);
                $table->integer('contentId')->unsigned();
                $table->string('title');
                $table->text('body');
                $table->boolean('isActive');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
                $table->foreign('contentId')->references('id')->on('Contents')->onDelete('CASCADE');
                $table->foreign('langCode')->references('code')->on('Langs')->onDelete('CASCADE');
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
        Schema::drop('ContentTranslations');
        Schema::drop('Contents');
    }

}
