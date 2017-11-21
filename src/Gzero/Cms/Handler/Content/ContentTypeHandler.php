<?php namespace Gzero\Cms\Handler\Content;

use Gzero\Cms\Models\Content as ContentEntity;
use Gzero\Core\Models\Language;
use \Illuminate\Http\Response;

interface ContentTypeHandler {

    /**
     * Load data from database
     *
     * @param ContentEntity $content  Content
     * @param Language      $language Current language
     *
     * @return $this
     */
    public function load(ContentEntity $content, Language $language);

    /**
     * Returns complete View for specific type
     *
     * @return Response
     */
    public function render();
}
