<?php namespace Gzero\Cms\Handlers\Content;

use Gzero\Cms\Models\Content;
use Gzero\Core\Models\Language;
use \Illuminate\Http\Response;

interface ContentTypeHandler {

    /**
     * Returns complete View for specific type
     *
     * @param Content  $content  Content
     * @param Language $language Language
     *
     * @return Response
     */
    public function handle(Content $content, Language $language): Response;
}
