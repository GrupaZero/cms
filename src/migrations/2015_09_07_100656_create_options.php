<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptions extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'OptionCategories',
            function (Blueprint $table) {
                $table->string('key')->index();
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
                $table->primary('key');
            }
        );

        Schema::create(
            'Options',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');
                $table->string('categoryKey');
                $table->text('value');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
                $table->foreign('categoryKey')->references('key')->on('OptionCategories')->onDelete('CASCADE');
                $table->index(['categoryKey', 'key']);
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
        Schema::drop('Options');
        Schema::drop('OptionCategories');
    }

}
