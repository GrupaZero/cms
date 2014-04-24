<?php namespace Gzero\Handler\Content;

use Gzero\Models\Content\Content as ContentModel;
use Gzero\Models\Lang;
use Gzero\Repositories\Content\ContentRepository;

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

    protected $parents;
    protected $content;
    protected $contentRepo;

    public function __construct(ContentRepository $content)
    {
        $this->contentRepo = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContentModel $content, Lang $lang)
    {
        $this->parents = $this->contentRepo->getAncestors($content); // Ancestors nodes
//        $this->contentRepo->loadThumb($this->parents); // Thumbs for all contents
        $this->content = $this->parents->pop(); // Removing our node
        return $this;
    }

    public function render()
    {
        return \View::make('content', array('content' => $this->content, 'parents' => $this->parents));
    }

}
