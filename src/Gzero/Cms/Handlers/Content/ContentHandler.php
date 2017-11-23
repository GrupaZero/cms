<?php namespace Gzero\Cms\Handlers\Content;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Services\FileService;
use Gzero\Core\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContentHandler implements ContentTypeHandler {

    /** @var Content */
    protected $content;

    /** @var ContentReadRepository */
    protected $repository;

    /** @var FileService */
    protected $fileRepo;

    /** @var \DaveJamesMiller\Breadcrumbs\Manager */
    protected $breadcrumbs;

    /** @var Request */
    protected $request;

    /**
     * Content constructor
     *
     * @param ContentReadRepository $repository ContentReadRepository repository
     * @param FileService           $fileRepo   File repository
     * @param Request               $request    Request object
     */
    public function __construct(ContentReadRepository $repository, FileService $fileRepo, Request $request)
    {
        $this->repository  = $repository;
        $this->fileRepo    = $fileRepo;
        $this->breadcrumbs = resolve('breadcrumbs');
        $this->request     = $request;
    }

    /**
     * Load data from database
     *
     * @param Content  $content  Content
     * @param Language $language Current language
     *
     * @return Response
     */
    public function handle(Content $content, Language $language): Response
    {
        $files = $this->fileRepo->getEntityFiles($content, [['is_active', '=', true]]);
        $this->buildBreadcrumbsFromUrl($content, $language);

        return response()->view(
            'gzero-cms::contents.content',
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
                )
            ]
        );
    }

    /**
     * Register breadcrumbs
     *
     * @param Content  $content  Content
     * @param Language $language Current lang entity
     *
     * @return void
     */
    protected function buildBreadcrumbsFromUrl(Content $content, Language $language)
    {
        // @TODO REMOVE THIS OR REMOVE CONTENT SERVICE ONLY?
        $this->breadcrumbs->register(
            $content->type->name,
            function ($breadcrumbs) use ($content, $language) {
                $breadcrumbs->push(trans('gzero-core::common.home'), routeMl('home'));

                $titlesAndUrls = $this->repository->getAncestorsTitlesAndPaths($content, $language);

                $titlesAndUrls->each(function ($item) use ($breadcrumbs) {
                    $breadcrumbs->push($item->title, $item->path);
                });
            }
        );
    }
}
