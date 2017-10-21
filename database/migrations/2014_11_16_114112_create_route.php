<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoute extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'routes',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('routable_id')->unsigned()->nullable();
                $table->string('routable_type')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            }
        );

        Schema::create(
            'route_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('lang_code', 2);
                $table->integer('route_id')->unsigned();
                $table->string('url')->index();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
                $table->foreign('route_id')->references('id')->on('routes')->onDelete('CASCADE');
                $table->foreign('lang_code')->references('code')->on('langs')->onDelete('CASCADE');
                $table->unique(['lang_code', 'route_id']); // Only one translation in specific language
                $table->unique(['lang_code', 'url']); // Unique url in specific language
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
        Schema::dropIfExists('route_translations');
        Schema::dropIfExists('routes');
    }

}
