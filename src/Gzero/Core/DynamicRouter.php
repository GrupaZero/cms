<?php namespace Gzero\Core;

use Gzero\Repository\ContentRepository;
use Illuminate\Foundation\Application;
use Gzero\Entity\Lang;
use Gzero\Core\Handler\Content\ContentTypeHandler;
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

    /**
     * @var Application
     */
    private $app;

    /**
     * @var ContentRepository
     */
    private $repository;

    /**
     * DynamicRouter constructor
     *
     * @param Application       $app        Laravel application
     * @param ContentRepository $repository Content repository
     */
    public function __construct(Application $app, ContentRepository $repository)
    {
        $this->app        = $app;
        $this->repository = $repository;
    }

    /**
     * Handles dynamic content rendering
     *
     * @param String $url  Url address
     * @param Lang   $lang Lang entity
     *
     * @return \View
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function handleRequest($url, Lang $lang)
    {
        try {
            $content = $this->repository->getByUrl($url, $lang->code);
            // Only if page is visible on public
            if (!empty($content) && $content->isActive) {
                $type = $this->resolveType($content->type);
                return $type->load($content, $lang)->render();
            } else {
                throw new NotFoundHttpException();
            }
        } catch (\ReflectionException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Dynamically resolve type of content
     *
     * @param String $typeName Type name
     *
     * @return ContentTypeHandler
     * @throws \ReflectionException
     */
    private function resolveType($typeName)
    {
        $type = $this->app->make('content_type:' . $typeName);
        if (!$type instanceof ContentTypeHandler) {
            throw new \ReflectionException("Type: $typeName must implement ContentTypeInterface");
        }
        return $type;
    }
}
