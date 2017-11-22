<?php namespace Gzero\Cms\Handler\Content;

use Gzero\Cms\Models\Content as ContentEntity;
use Gzero\Core\Models\Language;
use Gzero\Core\Parsers\StringParser;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
     * @param ContentEntity $content  Content
     * @param Language      $language Current language
     *
     * @return $this|mixed
     */
    public function load(ContentEntity $content, Language $language)
    {
        parent::load($content, $language);
        $this->children = $this->repository->getChildren($content)->setPath($this->request->url());

        return $this;
    }

    /**
     * Renders category
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return response()->view(
            'gzero-cms::contents.category',
            [
                'content'     => $this->content,
                'translation' => $this->translation,
                'images'      => $this->files->filter(
                    function ($file) {
                        return $file->type === 'image';
                    }
                ),
                'documents'   => $this->files->filter(
                    function ($file) {
                        return $file->type === 'document';
                    }
                ),
                'children'    => $this->children
            ]
        );
    }
}
