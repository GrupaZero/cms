<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Content as ContentEntity;
use Gzero\Entity\Lang;
use Illuminate\Support\Facades\View;

interface ContentTypeHandler {

    /**
     * Load data from database
     *
     * @param ContentEntity $content Content entity
     * @param Lang          $lang    Current lang entity
     *
     * @return $this
     */
    public function load(ContentEntity $content, Lang $lang);

    /**
     * Returns complete View for specific type
     *
     * @return View
     */
    public function render();
}
