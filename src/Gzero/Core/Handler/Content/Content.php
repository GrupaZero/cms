<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Lang;
use Gzero\Entity\Content as ContentEntity;
use Gzero\Repository\ContentRepository;
use Gzero\Repository\FileRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Content
 *
 * @package    Gzero\ContentTypes
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
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
     * @var ContentRepository
     */
    protected $contentRepo;

    /**
     * @var FileRepository
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
     * @param ContentRepository $contentRepo Content repository
     * @param FileRepository    $fileRepo    File repository
     * @param Request           $request     Request object
     */
    public function __construct(ContentRepository $contentRepo, FileRepository $fileRepo, Request $request)
    {
        $this->contentRepo = $contentRepo;
        $this->fileRepo    = $fileRepo;
        $this->breadcrumbs = app('breadcrumbs');
        $this->request     = $request;
    }

    /**
     * Load data from database
     *
     * @param ContentEntity $content Content entity
     * @param Lang          $lang    Current lang entity
     *
     * @return $this
     */
    public function load(ContentEntity $content, Lang $lang)
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
            'content',
            function ($breadcrumbs) use ($lang, $url) {
                $breadcrumbs->push(trans('common.home'), $url);
                foreach (explode('/', $this->content->getUrl($lang->code)) as $urlPart) {
                    $url  .= '/' . $urlPart;
                    $name = ucwords(str_replace('-', ' ', $urlPart));
                    $breadcrumbs->push($name, $url);
                }
            }
        );
    }
}
