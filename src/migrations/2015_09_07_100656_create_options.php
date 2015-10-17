<?php

use Gzero\Entity\Lang;
use Gzero\Entity\OptionCategory;
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

        // Seed options
        $this->seedOptions();
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

    /**
     * Seed options from gzero config to 'main' category
     *
     * @return void
     */
    private function seedOptions()
    {
        // gzero config options
        $options = [
            'general' => [
                'siteName'        => [],
                'siteDesc'        => [],
                'defaultPageSize' => [],
            ],
            'seo'     => [
                'seoDescLength'     => [],
                'googleAnalyticsId' => [],
            ]
        ];

        // Propagate Lang options based on gzero config
        foreach ($options as $categoryKey => $category) {
            foreach ($options[$categoryKey] as $key => $option) {
                foreach (Lang::all()->toArray() as $lang) {
                    $options[$categoryKey][$key][$lang['code']] = config('gzero.' . $key);
                }
            }
        }

        // Seed options
        foreach ($options as $category => $option) {
            OptionCategory::create(['key' => $category]);
            foreach ($option as $key => $value) {
                OptionCategory::find($category)->options()->create(
                    ['key' => $key, 'value' => $value]
                );
            }
        }
    }
}
