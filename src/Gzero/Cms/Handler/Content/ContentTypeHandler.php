<?php namespace Gzero\Cms\Handler\Content;

use Gzero\Cms\Models\Content as ContentEntity;
use Gzero\Core\Models\Language;
use Illuminate\Support\Facades\View;

interface ContentTypeHandler {

    /**
     * Load data from database
     *
     * @param ContentEntity $content Content
     * @param Language      $lang    Current language
     *
     * @return $this
     */
    public function load(ContentEntity $content, Language $lang);

    /**
     * Returns complete View for specific type
     *
     * @return View
     */
    public function render();
}
