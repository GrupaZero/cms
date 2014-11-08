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
     * @var ContentRepository
     */
    protected $contentRepo;

    /**
     * @param Application       $app
     * @param ContentRepository $contentRepo
     */
    public function __construct(Application $app, ContentRepository $contentRepo)
    {
        $this->app         = $app;
        $this->contentRepo = $contentRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContentEntity $content, Lang $lang)
    {
        // $this->parents = $this->contentRepo->findAncestors($content); // Ancestors nodes
        // $this->contentRepo->loadThumb($this->parents); // Thumbs for all contents
        // $this->content = $this->parents->pop(); // Removing our node
        $this->content = $content;
        return $this;
    }

    public function render()
    {
        return \View::make('content.content', ['content' => $this->content, 'parents' => null]);
    }
}
