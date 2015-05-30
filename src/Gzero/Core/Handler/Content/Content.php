<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Lang;
use Gzero\Entity\Content as ContentEntity;
use Gzero\Repository\ContentRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\View;

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
     * @var Application
     */
    protected $app;

    /**
     * @var
     */
    protected $parents;

    /**
     * @var
     */
    protected $content;

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
     * @var DaveJamesMiller\Breadcrumbs\Manager
     */
    protected $breadcrumbs;

    /**
     * Content constructor
     *
     * @param Application       $app         Laravel application
     * @param ContentRepository $contentRepo content repository
     */
    public function __construct(Application $app, ContentRepository $contentRepo)
    {
        $this->app         = $app;
        $this->contentRepo = $contentRepo;
        $this->breadcrumbs = $this->app->make('breadcrumbs');
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
        $this->buildBradcrumbsFromUrl($lang);

        return $this;
    }

    /**
     * Renders content
     *
     * @return View
     */
    public function render()
    {
        return View::make(
            'content.content',
            [
                'content'      => $this->content,
                'translations' => $this->translations,
                'author'       => $this->author,
                'parents'      => null
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
    protected function buildBradcrumbsFromUrl($lang)
    {
        $url = '/' . $lang->code . '/';
        $this->breadcrumbs->register(
            'content',
            function ($breadcrumbs) use ($lang, $url) {
                $breadcrumbs->push('Start', $url);
                foreach (explode('/', $this->content->getUrl($lang->code)) as $urlPart) {
                    $url .= $urlPart . '/';
                    $name = ucwords(str_replace('-', ' ', $urlPart));
                    $breadcrumbs->push($name, $url);
                }
            }
        );
    }
}
