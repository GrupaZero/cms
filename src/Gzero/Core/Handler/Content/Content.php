<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Lang;
use Gzero\Entity\Content as ContentEntity;
use Gzero\Repository\ContentRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\View;
use DaveJamesMiller\Breadcrumbs\Facade as Breadcrumbs;

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
     * Content constructor
     *
     * @param Application       $app         Laravel application
     * @param ContentRepository $contentRepo content repository
     */
    public function __construct(Application $app, ContentRepository $contentRepo)
    {
        $this->app         = $app;
        $this->contentRepo = $contentRepo;
        $this->registerBreadcrumbs();
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

    public function registerBreadcrumbs(){
        Breadcrumbs::register('home', function($breadcrumbs)
        {
            $breadcrumbs->push(trans('HOME'), '/');
        });

        Breadcrumbs::register('content', function($breadcrumbs, $content)
        {
            $breadcrumbs->parent('home');
            $breadcrumbs->push($content->title, '#');
        });
    }
}
