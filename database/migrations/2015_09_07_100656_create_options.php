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
            'option_categories',
            function (Blueprint $table) {
                $table->string('key');
                $table->timestamps();
                $table->primary('key');
            }
        );

        Schema::create(
            'options',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');
                $table->string('category_key');
                $table->text('value');
                $table->timestamps();
                $table->foreign('category_key')->references('key')->on('option_categories')->onDelete('CASCADE');
                $table->index(['category_key', 'key']);
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
        Schema::dropIfExists('options');
        Schema::dropIfExists('option_categories');
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
                'site_name'          => [],
                'site_desc'          => [],
                'default_page_size'  => [],
                'cookies_policy_url' => [],
            ],
            'seo'     => [
                'desc_length'         => [],
                'google_analytics_id' => [],
            ]
        ];

        // Propagate Lang options based on gzero config
        foreach ($options as $categoryKey => $category) {
            foreach ($options[$categoryKey] as $key => $option) {
                foreach (Lang::all()->toArray() as $lang) {
                    if ($categoryKey != 'general') {
                        $options[$categoryKey][$key][$lang['code']] = config('gzero.' . $categoryKey . '.' . $key);
                    } else {
                        $value = $this->getDefaultValueForGeneral($key);

                        $options[$categoryKey][$key][$lang['code']] = $value;
                    }
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

    /**
     * It generates default value for general options
     *
     * @param $key
     *
     * @return mixed|string
     */
    private function getDefaultValueForGeneral($key)
    {
        switch ($key) {
            case 'site_name':
                $value = config('app.name');
                break;
            case 'site_desc':
                $value = "GZERO-CMS Content management system.";
                break;
            default:
                $value = config('gzero.' . $key);
                return $value;
        }
        return $value;
    }
}
