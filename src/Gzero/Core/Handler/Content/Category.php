<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Content as ContentEntity;
use Gzero\Entity\Lang;
use Illuminate\Support\Facades\View;

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
                ['isActive', '=', true]
            ],
            [
                ['isPromoted', 'DESC'],
                ['isSticky', 'DESC'],
                ['weight', 'ASC']
            ],
            $this->request->get('page', 1),
            config('gzero.defaultPageSize', 20)
        )->setPath($this->request->url());

        return $this;
    }

    /**
     * Renders category
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
            'category',
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
