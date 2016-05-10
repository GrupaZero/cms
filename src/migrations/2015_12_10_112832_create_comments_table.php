<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'comments',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->string('title')->nullable();
                $table->text('body');
                $table->integer('parent_id')->nullable();
                $table->integer('lft')->nullable();
                $table->integer('rgt')->nullable();
                $table->integer('depth')->nullable();
                $table->morphs('commentable');
                $table->integer('user_id')->unsigned();
                $table->index('user_id');
                $table->index('commentable_id');
                $table->index('commentable_type');
                $table->foreign('user_id')->references('id')->on('Users');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('comments');
    }
}
