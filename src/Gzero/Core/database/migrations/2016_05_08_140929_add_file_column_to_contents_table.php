<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFileColumnToContentsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'contents',
            function (Blueprint $table) {
                $table->integer('file_id')->unsigned()->nullable()->after('parent_id');
                $table->foreign('file_id')->references('id')->on('files')->onDelete('SET NULL');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'contents',
            function (Blueprint $table) {
                $table->dropForeign(['file_id']);
                $table->dropColumn('file_id');
            }
        );
    }
}
