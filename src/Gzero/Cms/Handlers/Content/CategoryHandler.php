<?php namespace Gzero\Cms\Handlers\Content;

use Gzero\Cms\Models\Content;
use Gzero\Core\Models\Language;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class CategoryHandler extends ContentHandler implements ContentTypeHandler {

    /**
     * @var Collection
     */
    protected $children;

    /**
     * Load data from database
     *
     * @param Content  $content  Content
     * @param Language $language Current language
     *
     * @return $this|mixed
     */
    public function handle(Content $content, Language $language): Response
    {
        $children = $this->repository->getChildren($content)->setPath($this->request->url());
        $files    = $this->fileRepo->getEntityFiles($content, [['is_active', '=', true]]);

        $this->buildBreadcrumbsFromUrl($content, $language);

        return response()->view(
            'gzero-cms::contents.category',
            [
                'content'     => $content,
                'translation' => $content->getActiveTranslation($language->code),
                'images'      => $files->filter(
                    function ($file) {
                        return $file->type === 'image';
                    }
                ),
                'documents'   => $files->filter(
                    function ($file) {
                        return $file->type === 'document';
                    }
                ),
                'children'    => $children
            ]
        );
    }
}
