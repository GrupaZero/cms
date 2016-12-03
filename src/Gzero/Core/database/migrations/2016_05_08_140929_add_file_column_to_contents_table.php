<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFileColumnToContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Contents', function (Blueprint $table) {
            $table->integer('fileId')->unsigned()->nullable()->after('parentId');
            $table->foreign('fileId')->references('id')->on('Files')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Contents', function (Blueprint $table) {
            $table->dropForeign(['fileId']);
            $table->dropColumn('fileId');
        });
    }
}
