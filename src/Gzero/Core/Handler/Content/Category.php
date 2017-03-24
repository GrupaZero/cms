<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Content as ContentEntity;
use Gzero\Entity\Lang;
use Illuminate\Support\Collection;
use Illuminate\View\View;

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
     * @var Collection
     */
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
                ['is_active', '=', true]
            ],
            [
                ['is_promoted', 'DESC'],
                ['is_sticky', 'DESC'],
                ['weight', 'ASC'],
                ['published_at', 'DESC']
            ],
            $this->request->get('page', 1),
            option('general', 'default_page_size', 20)
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
        return view(
            'contents.category',
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
                ),
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
    protected function buildBreadcrumbsFromUrl($lang)
    {
        $url = (config('gzero.multilang.enabled')) ? '/' . $lang->code : '';
        $this->breadcrumbs->register(
            'category',
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
