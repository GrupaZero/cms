<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Content as ContentEntity;
use Gzero\Entity\Lang;

interface ContentTypeHandler {

    /**
     * @param ContentEntity $content
     * @param Lang          $lang
     *
     * @return mixed
     */
    public function load(ContentEntity $content, Lang $lang);

    /**
     * Returns complete View for specific type
     *
     * @return \View
     */
    public function render();
} 
