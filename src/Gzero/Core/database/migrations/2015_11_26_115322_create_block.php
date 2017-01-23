<?php

use Gzero\Entity\BlockType;
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
                $table->string('name');
                $table->boolean('is_active')->default(false);
                $table->primary('name');
                $table->timestamps();
            }
        );

        Schema::create(
            'blocks',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->string('region')->nullable();
                $table->string('theme')->nullable();
                $table->integer('blockable_id')->unsigned()->nullable();
                $table->string('blockable_type')->nullable();
                $table->integer('author_id')->unsigned()->nullable();
                $table->text('filter')->nullable();
                $table->text('options')->nullable();
                $table->integer('weight')->default(0);
                $table->boolean('is_active')->default(false);
                $table->boolean('is_cacheable')->default(false);
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable();
                $table->index(['blockable_id', 'blockable_type']);
                $table->foreign('author_id')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('type')->references('name')->on('block_types')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'block_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('lang_code', 2);
                $table->integer('block_id')->unsigned();
                $table->string('title');
                $table->text('body')->nullable();
                $table->text('custom_fields')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
                $table->foreign('block_id')->references('id')->on('blocks')->onDelete('CASCADE');
                $table->foreign('lang_code')->references('code')->on('langs')->onDelete('CASCADE');
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
        foreach (['basic', 'menu', 'slider', 'content', 'widget'] as $type) {
            BlockType::firstOrCreate(['name' => $type, 'is_active' => true]);
        }
    }

}
