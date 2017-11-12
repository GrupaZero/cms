<?php namespace Gzero\Core\Handler\Content;

use Gzero\Cms\Models\Content as ContentEntity;
use Gzero\Cms\Services\ContentService;
use Gzero\Cms\Services\FileService;
use Gzero\Core\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Content implements ContentTypeHandler {

    /**
     * @var ContentEntity
     */
    protected $content;

    /**
     * @var Collection
     */
    protected $files;

    /**
     * @var
     */
    protected $translations;

    /**
     * @var
     */
    protected $author;

    /**
     * @var ContentService
     */
    protected $contentRepo;

    /**
     * @var FileService
     */
    protected $fileRepo;

    /**
     * @var \DaveJamesMiller\Breadcrumbs\Manager
     */
    protected $breadcrumbs;

    /**
     * @var Request
     */
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
     * @param ContentEntity $content Content
     * @param Language      $lang    Current language
     *
     * @return $this
     */
    public function load(ContentEntity $content, Language $lang)
    {
        if ($lang) { // Right now we don't need lang
            $this->content = $content->load('route.translations', 'translations', 'author');
        }
        $this->files = $this->fileRepo->getEntityFiles($content, [['is_active', '=', true]]);
        $this->buildBreadcrumbsFromUrl($lang);

        return $this;
    }

    /**
     * Renders content
     *
     * @return View
     */
    public function render()
    {
        return view(
            'contents.content',
            [
                'content'      => $this->content,
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
     * @param Lang $lang Current lang entity
     *
     * @return void
     */
    protected function buildBreadcrumbsFromUrl($lang)
    {
        $url = (config('gzero.multilang.enabled')) ? '/' . $lang->code : '';
        $this->breadcrumbs->register(
            $this->content->type,
            function ($breadcrumbs) use ($lang, $url) {
                $breadcrumbs->push(trans('common.home'), $url);

                $contentUrl    = $this->content->getUrl($lang->code);
                $titles        = $this->contentRepo->getTitlesTranslationFromUrl($contentUrl, $lang->code);
                $titlesAndUrls = $this->contentRepo->matchTitlesWithUrls($titles, $contentUrl, $lang->code);

                foreach ($titlesAndUrls as $item) {
                    $breadcrumbs->push($item['title'], $item['url']);
                }
            }
        );
    }
}
