<?php namespace Gzero\Cms\Handler\Content;

use Gzero\Cms\Models\Content as ContentEntity;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Services\ContentService;
use Gzero\Cms\Services\FileService;
use Gzero\Core\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Content implements ContentTypeHandler {

    /** @var ContentEntity */
    protected $content;

    /** @var Collection */
    protected $files;

    /** @var */
    protected $translation;

    /** @var ContentReadRepository */
    protected $repository;

    /** @var ContentService */
    protected $contentService;

    /** @var FileService */
    protected $fileRepo;

    /** @var \DaveJamesMiller\Breadcrumbs\Manager */
    protected $breadcrumbs;

    /** @var Request */
    protected $request;

    /**
     * Content constructor
     *
     * @param ContentReadRepository $repository     ContentReadRepository repository
     * @param ContentService        $contentService Content service
     * @param FileService           $fileRepo       File repository
     * @param Request               $request        Request object
     */
    public function __construct(
        ContentReadRepository $repository,
        ContentService $contentService,
        FileService $fileRepo,
        Request $request
    ) {
        $this->repository     = $repository;
        $this->contentService = $contentService;
        $this->fileRepo       = $fileRepo;
        $this->breadcrumbs    = app('breadcrumbs');
        $this->request        = $request;
    }

    /**
     * Load data from database
     *
     * @param ContentEntity $content  Content
     * @param Language      $language Current language
     *
     * @return $this
     */
    public function load(ContentEntity $content, Language $language)
    {
        $this->content     = $content;
        $this->translation = $content->getActiveTranslation($language->code);
        $this->files       = $this->fileRepo->getEntityFiles($content, [['is_active', '=', true]]);
        $this->buildBreadcrumbsFromUrl($language);

        return $this;
    }

    /**
     * Renders content
     *
     * @return View
     */
    public function render()
    {
        return response()->view(
            'gzero-cms::contents.content',
            [
                'content'     => $this->content,
                'translation' => $this->translation,
                'images'      => $this->files->filter(
                    function ($file) {
                        return $file->type === 'image';
                    }
                ),
                'documents'   => $this->files->filter(
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
     * @param Language $language Current lang entity
     *
     * @return void
     */
    protected function buildBreadcrumbsFromUrl(Language $language)
    {
        // @TODO REMOVE THIS OR REMOVE CONTENT SERVICE ONLY?
        $this->breadcrumbs->register(
            $this->content->type,
            function ($breadcrumbs) use ($language) {
                $breadcrumbs->push(trans('gzero-core::common.home'), routeMl('home'));

                $contentUrl    = $this->content->getPath($language->code);
                $titles        = $this->contentService->getTitlesTranslationFromUrl($contentUrl, $language->code);
                $titlesAndUrls = $this->contentService->matchTitlesWithUrls($titles, $contentUrl, $language->code);

                foreach ($titlesAndUrls as $item) {
                    $breadcrumbs->push($item['title'], $item['url']);
                }
            }
        );
    }
}
