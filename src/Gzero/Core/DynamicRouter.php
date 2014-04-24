<?php namespace Gzero\Core;

use Gzero\Handlers\Content\ContentTypeHandler;
use Gzero\Models\Lang;
use Gzero\Repositories\Content\ContentRepository;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class DynamicRouter
 *
 * @package    Gzero
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class DynamicRouter {

    private $app, $contentRepo;

    public function __construct(Application $app, ContentRepository $content)
    {
        $this->app         = $app;
        $this->contentRepo = $content;
    }

    /**
     * Handles dynamic content rendering
     *
     * @param String $url
     * @param Lang   $lang
     *
     * @return \View
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function handleRequest($url, Lang $lang)
    {
        try {
            $page = $this->contentRepo->getByUrl($url, $lang);
            if (!empty($page->is_active)) { // Only if page is visible on public
                $type = $this->resolveType($page->getTypeName());
                return $type->load($page, $lang)->render();
            } else {
                throw new NotFoundHttpException();
            }
        } catch (\ReflectionException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @param String $typeName
     *
     * @return ContentTypeHandler
     * @throws \ReflectionException
     */
    private function resolveType($typeName)
    {
        $type = $this->app->make('type:' . $typeName);
        if (!$type instanceof ContentTypeHandler) {
            throw new \ReflectionException("Type: $typeName must implement ContentTypeInterface");
        }
        return $type;
    }
}
