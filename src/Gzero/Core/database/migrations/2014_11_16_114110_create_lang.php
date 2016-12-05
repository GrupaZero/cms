<?php

use Gzero\Entity\Lang;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLang extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'langs',
            function (Blueprint $table) {
                $table->string('code', 2)->index();
                $table->string('i18n', 5);
                $table->boolean('is_enabled')->default(false);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            }
        );

        // Seed langs
        $this->seedLangs();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('langs');
    }

    /**
     * Seed langs
     *
     * @return void
     */
    private function seedLangs()
    {
        Lang::firstOrCreate(
            [
                'code'      => 'en',
                'i18n'      => 'en_US',
                'is_enabled' => true,
                'is_default' => true
            ]
        );

        Lang::firstOrCreate(
            [
                'code'      => 'pl',
                'i18n'      => 'pl_PL',
                'is_enabled' => true
            ]
        );

        Lang::firstOrCreate(
            [
                'code'      => 'de',
                'i18n'      => 'de_DE',
                'is_enabled' => false
            ]
        );

        Lang::firstOrCreate(
            [
                'code'      => 'fr',
                'i18n'      => 'fr_FR',
                'is_enabled' => false
            ]
        );
    }
}
