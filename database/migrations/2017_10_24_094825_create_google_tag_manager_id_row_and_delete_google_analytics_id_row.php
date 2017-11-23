<?php

use Gzero\Core\Models\Language;
use Gzero\Core\Models\OptionCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleTagManagerIdRowAndDeleteGoogleAnalyticsIdRow extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('options')->where('key', 'google_analytics_id')->delete();

        $options = [
            'seo' => [
                'google_tag_manager_id' => [],
            ]
        ];

        $this->createOptions($options);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('options')->where('key', 'google_tag_manager_id')->delete();

        $options = [
            'seo' => [
                'google_analytics_id' => [],
            ]
        ];

        $this->createOptions($options);
    }

    /**
     * Create options based on given array.
     *
     * @param array $options
     *
     * @return void
     */
    public function createOptions(array $options)
    {

        // Propagate Lang options based on gzero config
        foreach ($options as $categoryKey => $category) {
            foreach ($options[$categoryKey] as $key => $option) {
                foreach (Language::all()->toArray() as $lang) {
                    $options[$categoryKey][$key][$lang['code']] = config('gzero.' . $categoryKey . '.' . $key);
                }
            }
        }

        // Seed options
        foreach ($options as $category => $option) {
            foreach ($option as $key => $value) {
                OptionCategory::find($category)->options()->create(
                    ['key' => $key, 'value' => $value]
                );
            }
        }
    }
}
