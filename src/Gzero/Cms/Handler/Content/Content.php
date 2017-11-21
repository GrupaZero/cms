<?php namespace Gzero\Cms\Handler\Content;

use Gzero\Cms\Models\Content as ContentEntity;
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
    protected $translations;

    /** @var */
    protected $author;

    /** @var */
    protected $language;

    /** @var ContentService */
    protected $contentRepo;

    /** @var FileService */
    protected $fileRepo;

    /** @var \DaveJamesMiller\Breadcrumbs\Manager */
    protected $breadcrumbs;

    /** @var Request */
    protected $request;

    /**
     * Content constructor
     *
     * @param ContentService $contentRepo Content repository
     * @param FileService    $fileRepo    File repository
     * @param Request        $request     Request object
     */
    public function __construct(ContentService $contentRepo, FileService $fileRepo, Request $request)
    {
        $this->contentRepo = $contentRepo;
        $this->fileRepo    = $fileRepo;
        $this->breadcrumbs = app('breadcrumbs');
        $this->request     = $request;
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
        // @TOD Use view presenter for active translation and languages
        if ($language) { // Right now we don't need lang
            $this->content = $content->load('route.translations', 'translations', 'author');
            $this->language = $language;
        }
        $this->files = $this->fileRepo->getEntityFiles($content, [['is_active', '=', true]]);
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
                'content'      => $this->content,
                'language'     => $this->language,
                'translations' => $this->translations,
                'author'       => $this->author,
                'images'       => $this->files->filter(
                    function ($file) {
                        return $file->type === 'image';
                    }
                ),
                'documents'    => $this->files->filter(
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
        $this->breadcrumbs->register(
            $this->content->type,
            function ($breadcrumbs) use ($language) {
                $breadcrumbs->push(trans('gzero-core::common.home'), routeMl('home'));

                $contentUrl    = $this->content->getPath($language->code);
                $titles        = $this->contentRepo->getTitlesTranslationFromUrl($contentUrl, $language->code);
                $titlesAndUrls = $this->contentRepo->matchTitlesWithUrls($titles, $contentUrl, $language->code);

                foreach ($titlesAndUrls as $item) {
                    $breadcrumbs->push($item['title'], $item['url']);
                }
            }
        );
    }
}
