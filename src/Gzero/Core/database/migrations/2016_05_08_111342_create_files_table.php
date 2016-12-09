<?php

use Gzero\Entity\FileType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'file_types',
            function (Blueprint $table) {
                $table->string('name')->index()->unique();
                $table->text('extensions')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            }
        );

        Schema::create(
            'files',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->string('name');
                $table->string('extension');
                $table->integer('size')->nullable();
                $table->string('mime_type');
                $table->text('info')->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('type')->references('name')->on('file_types')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'file_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('lang_code', 2);
                $table->integer('file_id')->unsigned();
                $table->string('title');
                $table->text('description');
                $table->timestamps();
                $table->unique(['file_id', 'lang_code']);
                $table->foreign('file_id')->references('id')->on('files')->onDelete('CASCADE');
                $table->foreign('lang_code')->references('code')->on('langs')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'uploadables',
            function (Blueprint $table) {
                $table->integer('file_id')->unsigned()->index();
                $table->integer('uploadable_id')->unsigned()->nullable();
                $table->string('uploadable_type')->nullable();
                $table->integer('weight')->default(0);
                $table->timestamps();
                $table->foreign('file_id')->references('id')->on('files')->onDelete('CASCADE');
            }
        );

        // Seed file types
        $this->seedFileTypes();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uploadables');
        Schema::dropIfExists('file_translations');
        Schema::dropIfExists('files');
        Schema::dropIfExists('file_types');
    }

    /**
     * Seed file types
     *
     * @return void
     */
    private function seedFileTypes()
    {
        foreach (['image', 'document', 'video', 'music'] as $type) {
            FileType::firstOrCreate(['name' => $type, 'is_active' => true]);
        }
    }

}
