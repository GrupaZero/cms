<?php

use Gzero\Entity\FileType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'FileTypes',
            function (Blueprint $table) {
                $table->string('name')->index()->unique();
                $table->text('extensions')->nullable();
                $table->boolean('isActive')->default(false);
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
            }
        );

        Schema::create(
            'Files',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->string('name');
                $table->string('extension');
                $table->integer('size')->nullable();
                $table->string('mimeType');
                $table->text('info')->nullable();
                $table->integer('createdBy')->unsigned()->nullable();
                $table->boolean('isActive')->default(false);
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
                $table->foreign('createdBy')->references('id')->on('Users')->onDelete('SET NULL');
                $table->foreign('type')->references('name')->on('FileTypes')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'FileTranslations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('langCode', 2);
                $table->integer('fileId')->unsigned();
                $table->string('title');
                $table->text('description');
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
                $table->unique(['fileId', 'langCode']);
                $table->foreign('fileId')->references('id')->on('Files')->onDelete('CASCADE');
                $table->foreign('langCode')->references('code')->on('Langs')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'Uploadables',
            function (Blueprint $table) {
                $table->integer('fileId')->unsigned()->index();
                $table->integer('uploadableId')->unsigned()->nullable();
                $table->string('uploadableType')->nullable();
                $table->integer('weight')->default(0);
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent();
                $table->foreign('fileId')->references('id')->on('Files')->onDelete('CASCADE');
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
        Schema::dropIfExists('Uploadables');
        Schema::dropIfExists('FileTranslations');
        Schema::dropIfExists('Files');
        Schema::dropIfExists('FileTypes');
    }

    /**
     * Seed file types
     *
     * @return void
     */
    private function seedFileTypes()
    {
        foreach (['image', 'document', 'video', 'music'] as $type) {
            FileType::firstOrCreate(['name' => $type, 'isActive' => true]);
        }
    }

}
