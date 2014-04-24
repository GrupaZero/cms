<?php namespace Gzero\Handler\Content;

use Gzero\Models\Content\Content as ContentModel;
use Gzero\Models\Lang;

interface ContentTypeHandler {

    /**
     * @param ContentModel $content
     * @param Lang         $lang
     *
     * @return $this
     */
    public function load(ContentModel $content, Lang $lang);

    /**
     * Returns complete View for specific type
     *
     * @return \View
     */
    public function render();
} 
