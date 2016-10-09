<?php

use Gzero\Entity\ContentType;
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
                $table->string('name')->index()->unique();
                $table->boolean('isActive');
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
            }
        );

        Schema::create(
            'Contents',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->string('theme')->nullable();
                $table->integer('authorId')->unsigned()->nullable();
                $table->string('path', 255)->nullable();
                $table->integer('parentId')->unsigned()->nullable();
                $table->integer('level')->default(0);
                $table->integer('weight')->default(0);
                $table->integer('rating')->default(0);
                $table->integer('visits')->default(0);
                $table->boolean('isOnHome')->default(false);
                $table->boolean('isCommentAllowed')->default(false);
                $table->boolean('isPromoted')->default(false);
                $table->boolean('isSticky')->default(false);
                $table->boolean('isActive')->default(false);
                $table->timestamp('publishedAt')->useCurrent();
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
                $table->index(['type', 'path', 'parentId', 'level']);
                $table->foreign('authorId')->references('id')->on('Users')->onDelete('SET NULL');
                $table->foreign('parentId')->references('id')->on('Contents')->onDelete('CASCADE');
                $table->foreign('type')->references('name')->on('ContentTypes')->onDelete('CASCADE');
                $table->timestamp('deletedAt')->nullable();
            }
        );

        Schema::create(
            'ContentTranslations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('langCode', 2);
                $table->integer('contentId')->unsigned();
                $table->string('title');
                $table->text('teaser');
                $table->text('body');
                $table->string('seoTitle');
                $table->string('seoDescription');
                $table->boolean('isActive');
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
                $table->foreign('contentId')->references('id')->on('Contents')->onDelete('CASCADE');
                $table->foreign('langCode')->references('code')->on('Langs')->onDelete('CASCADE');
            }
        );

        // Seed content types
        $this->seedContentTypes();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ContentTranslations');
        Schema::dropIfExists('Contents');
        Schema::dropIfExists('ContentTypes');
    }

    /**
     * Seed content types
     *
     * @return void
     */
    private function seedContentTypes()
    {
        foreach (['content', 'category'] as $type) {
            ContentType::firstOrCreate(['name' => $type, 'isActive' => true]);
        }
    }

}
