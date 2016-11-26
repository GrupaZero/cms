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
            'BlockTypes',
            function (Blueprint $table) {
                $table->string('name')->index()->unique();
                $table->boolean('isActive')->default(false);
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
            }
        );

        Schema::create(
            'Blocks',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->string('region')->nullable();
                $table->string("theme")->nullable();
                $table->integer("blockableId")->unsigned()->nullable();
                $table->string("blockableType")->nullable();
                $table->integer('authorId')->unsigned()->nullable();
                $table->text('filter')->nullable();
                $table->text('options')->nullable();
                $table->integer('weight')->default(0);
                $table->boolean('isActive')->default(false);
                $table->boolean('isCacheable')->default(false);
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
                $table->timestamp('deletedAt')->nullable();
                $table->index(['blockableId', 'blockableType']);
                $table->foreign('authorId')->references('id')->on('Users')->onDelete('SET NULL');
                $table->foreign('type')->references('name')->on('BlockTypes')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'BlockTranslations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('langCode', 2);
                $table->integer('blockId')->unsigned();
                $table->string('title');
                $table->text('body')->nullable();
                $table->text('customFields')->nullable();
                $table->boolean('isActive')->default(false);
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
                $table->foreign('blockId')->references('id')->on('Blocks')->onDelete('CASCADE');
                $table->foreign('langCode')->references('code')->on('Langs')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'Widgets',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->text('args')->nullable();
                $table->boolean('isActive')->default(false);
                $table->boolean('isCacheable')->default(false);
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
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
        Schema::dropIfExists('BlockTranslations');
        Schema::dropIfExists('Blocks');
        Schema::dropIfExists('BlockTypes');
        Schema::dropIfExists('Widgets');
    }

    /**
     * Seed block types
     *
     * @return void
     */
    private function seedBlockTypes()
    {
        foreach (['basic', 'menu', 'slider', 'content', 'widget'] as $type) {
            BlockType::firstOrCreate(['name' => $type, 'isActive' => true]);
        }
    }

}
