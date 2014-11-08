<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Content as ContentEntity;
use Gzero\Entity\Lang;

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

    protected $children;

    public function load(ContentEntity $content, Lang $lang)
    {
        parent::load($content, $lang);
        $this->children = $this->contentRepo->getChildren($this->content);
//        $this->contentRepo->loadThumb($this->children);
        return $this;
    }

    public function render()
    {
        return \View::make(
            'content.category',
            [
                'content'  => $this->content,
                'children' => $this->children
            ]
        );
    }
}
