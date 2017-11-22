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
                $table->integer('thumb_id')->unsigned()->nullable()->after('parent_id');
                $table->foreign('thumb_id')->references('id')->on('files')->onDelete('SET NULL');
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
                $table->dropForeign(['thumb_id']);
                $table->dropColumn('thumb_id');
            }
        );
    }
}
