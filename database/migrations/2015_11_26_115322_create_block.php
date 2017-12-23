<?php

use Gzero\Cms\Models\BlockType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlock extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'block_types',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            }
        );

        Schema::create(
            'blocks',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('type_id')->unsigned()->nullable();
                $table->string('region')->nullable();
                $table->string('theme')->nullable();
                $table->string('blockable_type')->nullable();
                $table->integer('author_id')->unsigned()->nullable();
                $table->text('filter')->nullable();
                $table->text('options')->nullable();
                $table->integer('weight')->default(0);
                $table->boolean('is_active')->default(false);
                $table->boolean('is_cacheable')->default(false);
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable();
                $table->index(['type_id', 'blockable_type']);
                $table->foreign('author_id')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('type_id')->references('id')->on('block_types')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'block_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('language_code', 2);
                $table->integer('block_id')->unsigned();
                $table->integer('author_id')->unsigned()->nullable();
                $table->string('title');
                $table->text('body')->nullable();
                $table->text('custom_fields')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
                $table->foreign('block_id')->references('id')->on('blocks')->onDelete('CASCADE');
                $table->foreign('language_code')->references('code')->on('languages')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'widgets',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->text('args')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('is_cacheable')->default(false);
                $table->timestamps();
            }
        );

        // Seed block types
        $this->seedBlockTypes();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('block_translations');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('block_types');
        Schema::dropIfExists('widgets');
    }

    /**
     * Seed block types
     *
     * @return void
     */
    private function seedBlockTypes()
    {
        foreach (['basic', 'menu', 'slider', 'widget'] as $type) {
            BlockType::firstOrCreate(['name' => $type, 'is_active' => true]);
        }
    }

}
