<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Content as ContentEntity;
use Gzero\Entity\Lang;
use View;

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

    /**
     * Load data from database
     *
     * @param ContentEntity $content Content entity
     * @param Lang          $lang    Current lang entity
     *
     * @return $this|mixed
     */
    public function load(ContentEntity $content, Lang $lang)
    {
        parent::load($content, $lang);
        $this->children = $this->contentRepo->getChildren(
            $content,
            [
                'isActive' => ['value' => true, 'relation' => null]
            ]
        );
        //$this->contentRepo->loadThumb($this->children);
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
            'content.category',
            [
                'content'      => $this->content,
                'translations' => $this->translations,
                'author'       => $this->author,
                'children'     => $this->children
            ]
        );
    }
}
