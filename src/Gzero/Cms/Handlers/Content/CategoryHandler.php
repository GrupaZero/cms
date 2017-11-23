<?php namespace Gzero\Cms\Handlers\Content;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Services\FileService;
use Gzero\Core\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class CategoryHandler implements ContentTypeHandler {

    /** @var ContentReadRepository */
    protected $repo;

    /** @var FileService */
    protected $fileRepo;

    /** @var Request */
    protected $request;

    /** @var Collection */
    protected $children;

    /**
     * CategoryHandler constructor.
     *
     * @param ContentReadRepository $repo     Content repository
     * @param FileService           $fileRepo File repository
     * @param Request               $request  Request
     */
    public function __construct(ContentReadRepository $repo, FileService $fileRepo, Request $request)
    {
        $this->repo     = $repo;
        $this->fileRepo = $fileRepo;
        $this->request  = $request;
    }

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
        $children = $this->repo->getChildren($content)->setPath($this->request->url());
        $files    = $this->fileRepo->getEntityFiles($content, [['is_active', '=', true]]);

        ContentHandler::buildBreadcrumbs($content, $language);

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
