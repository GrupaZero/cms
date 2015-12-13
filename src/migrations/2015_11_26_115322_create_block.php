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
                $table->boolean('isActive');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
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
                $table->json('filter')->nullable();
                $table->json('options')->nullable();
                $table->integer('weight');
                $table->boolean('isActive');
                $table->boolean('isCacheable');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
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
                $table->text('body');
                $table->json('customFields')->nullable();
                $table->boolean('isActive');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
                $table->foreign('blockId')->references('id')->on('Blocks')->onDelete('CASCADE');
                $table->foreign('langCode')->references('code')->on('Langs')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'Widgets',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->json('args')->nullable();
                $table->boolean('isActive');
                $table->boolean('isCacheable');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
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
