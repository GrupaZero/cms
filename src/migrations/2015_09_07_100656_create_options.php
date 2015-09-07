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
            }
        );

        Schema::create(
            'Options',
            function (Blueprint $table) {
                $table->string('key');
                $table->string('categoryKey');
                $table->text('value');
                $table->foreign('categoryKey')->references('key')->on('OptionCategories')->onDelete('CASCADE');
                $table->primary(['key', 'categoryKey']);
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
