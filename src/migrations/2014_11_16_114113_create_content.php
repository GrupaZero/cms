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
            'ContentTypes',
            function (Blueprint $table) {
                $table->string('name')->index();
                $table->boolean('isActive');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
            }
        );

        Schema::create(
            'Contents',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->integer('authorId')->unsigned()->nullable();
                $table->string('path', 255)->nullable();
                $table->integer('parentId')->unsigned()->nullable();
                $table->integer('level')->default(0);
                $table->integer('weight');
                $table->boolean('isActive');
                $table->index(['type', 'path', 'parentId', 'level']);
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
                $table->foreign('authorId')->references('id')->on('Users')->onDelete('SET NULL');
                $table->foreign('parentId')->references('id')->on('Contents')->onDelete('CASCADE');
                $table->foreign('type')->references('name')->on('ContentTypes')->onDelete('CASCADE');
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
        Schema::drop('ContentTypes');
    }

}