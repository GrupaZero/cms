<?php namespace Gzero\Core\Handler\Content;

use Gzero\Entity\Content as ContentEntity;
use Gzero\Entity\Lang;
use Gzero\Repository\ContentRepository;
use Gzero\Repository\FileRepository;
use Illuminate\Http\Request;
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
     * @var
     */
    protected $type;

    /**
     * Content constructor
     *
     * @param ContentRepository $contentRepo Content repository
     * @param FileRepository    $fileRepo    File repository
     * @param Request           $request     Request object
     */
    public function __construct(ContentRepository $contentRepo, FileRepository $fileRepo, Request $request)
    {
        parent::__construct($contentRepo, $fileRepo, $request);
        $this->type = 'category';
    }

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
}
