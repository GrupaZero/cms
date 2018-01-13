<?php namespace Gzero\Cms\Handlers\Content;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Models\Language;
use Gzero\Core\Repositories\FileReadRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContentHandler implements ContentTypeHandler {

    /** @var Content */
    protected $content;

    /** @var ContentReadRepository */
    protected $repository;

    /** @var FileReadRepository */
    protected $fileRepo;

    /** @var \DaveJamesMiller\Breadcrumbs\Manager */
    protected $breadcrumbs;

    /** @var Request */
    protected $request;

    /**
     * Content constructor
     *
     * @param FileReadRepository $fileRepo File repository
     * @param Request            $request  Request object
     */
    public function __construct(FileReadRepository $fileRepo, Request $request)
    {
        $this->fileRepo = $fileRepo;
        $this->request  = $request;
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
        //$files = $this->fileRepo->getEntityFiles($content, [['is_active', '=', true]]);
        $files = collect();

        self::buildBreadcrumbs($content, $language);

        return response()->view(
            'gzero-cms::contents.content',
            [
                'content'   => $content,
                'images'    => $files->filter(
                    function ($file) use ($content) {
                        return $file->type === 'image' && $file->id !== $content->thumb_id;
                    }
                ),
                'documents' => $files->filter(
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
    public static function buildBreadcrumbs(Content $content, Language $language)
    {
        resolve('breadcrumbs')->register(
            $content->type->name,
            function ($breadcrumbs) use ($content, $language) {
                $breadcrumbs->push(trans('gzero-core::common.home'), routeMl('home'));

                $titlesAndUrls = (new ContentReadRepository)->getAncestorsTitlesAndPaths($content, $language);

                $titlesAndUrls->each(function ($item) use ($breadcrumbs, $language) {
                    $breadcrumbs->push($item->title, urlMl($item->path, $language->code));
                });
            }
        );
    }
}
