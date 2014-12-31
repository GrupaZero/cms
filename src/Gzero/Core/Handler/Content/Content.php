<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Lang;
use Gzero\Entity\Content as ContentEntity;
use Gzero\Repository\ContentRepository;
use Illuminate\Foundation\Application;

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
     * @param ContentRepository $contentRepo content reposityory
     */
    public function __construct(Application $app, ContentRepository $contentRepo)
    {
        $this->app         = $app;
        $this->contentRepo = $contentRepo;
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
        // $this->parents = $this->contentRepo->findAncestors($content); // Ancestors nodes
        // $this->contentRepo->loadThumb($this->parents); // Thumbs for all contents
        // $this->content = $this->parents->pop(); // Removing our node
        $this->content      = $content;
        $this->translations = $content->translations()->where('langCode', '=', $lang->code)->first();
        $this->author       = $content->author;
        return $this;
    }

    /**
     * Renders content
     *
     * @return View
     */
    public function render()
    {
        return \View::make(
            'content.content',
            ['content' => $this->content, 'translations' => $this->translations, 'author' => $this->author, 'parents' => null]
        );
    }
}
