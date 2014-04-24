<?php namespace Gzero\Handler\Content;

use Gzero\Models\Content\Content as ContentModel;
use Gzero\Models\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Category
 *
 * @package    Gzero\ContentTypes
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Category extends Content {

    /**
     * @var \Gzero\EloquentBaseModel\Model\Collection
     */
    protected $children;

    public function load(ContentModel $content, Lang $lang)
    {
        parent::load($content, $lang);
//        $this->children = $this->contentRepo->getChildren($this->content);
//        $this->contentRepo->loadThumb($this->children);
        return $this;
    }

    public function render()
    {
        return \View::make(
            'category',
            array(
                'content'  => $this->content,
                'children' => $this->children
            )
        );
    }
}
