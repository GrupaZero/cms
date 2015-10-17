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
            'Langs',
            function (Blueprint $table) {
                $table->string('code', 2)->index();
                $table->string('i18n', 5);
                $table->boolean('isEnabled');
                $table->boolean('isDefault');
                $table->timestamp('createdAt');
                $table->timestamp('updatedAt');
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
        Schema::drop('Langs');
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
                'isEnabled' => true,
                'isDefault' => true
            ]
        );

        Lang::firstOrCreate(
            [
                'code'      => 'pl',
                'i18n'      => 'pl_PL',
                'isEnabled' => true
            ]
        );

        Lang::firstOrCreate(
            [
                'code'      => 'de',
                'i18n'      => 'de_DE',
                'isEnabled' => false
            ]
        );

        Lang::firstOrCreate(
            [
                'code'      => 'fr',
                'i18n'      => 'fr_FR',
                'isEnabled' => false
            ]
        );
    }
}
