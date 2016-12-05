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
            'content_types',
            function (Blueprint $table) {
                $table->string('name')->index()->unique();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            }
        );

        Schema::create(
            'contents',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->string('theme')->nullable();
                $table->integer('author_id')->unsigned()->nullable();
                $table->string('path', 255)->nullable();
                $table->integer('parent_id')->unsigned()->nullable();
                $table->integer('level')->default(0);
                $table->integer('weight')->default(0);
                $table->integer('rating')->default(0);
                $table->integer('visits')->default(0);
                $table->boolean('is_on_home')->default(false);
                $table->boolean('is_comment_allowed')->default(false);
                $table->boolean('is_promoted')->default(false);
                $table->boolean('is_sticky')->default(false);
                $table->boolean('is_active')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();
                $table->index(['type', 'path', 'parent_id', 'level']);
                $table->foreign('author_id')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('parent_id')->references('id')->on('contents')->onDelete('CASCADE');
                $table->foreign('type')->references('name')->on('content_types')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'content_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('lang_code', 2);
                $table->integer('content_id')->unsigned();
                $table->string('title')->nullable();
                $table->text('teaser')->nullable();
                $table->text('body')->nullable();
                $table->string('seo_title')->nullable();
                $table->string('seo_description')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
                $table->foreign('content_id')->references('id')->on('contents')->onDelete('CASCADE');
                $table->foreign('lang_code')->references('code')->on('langs')->onDelete('CASCADE');
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
        Schema::dropIfExists('content_translations');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('content_types');
    }

    /**
     * Seed content types
     *
     * @return void
     */
    private function seedContentTypes()
    {
        foreach (['content', 'category'] as $type) {
            ContentType::firstOrCreate(['name' => $type, 'is_active' => true]);
        }
    }

}
