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
    private $contentRepo;

    /**
     * @param Application       $app
     * @param ContentRepository $contentRepo
     */
    public function __construct(Application $app, ContentRepository $contentRepo)
    {
        $this->app         = $app;
        $this->contentRepo = $contentRepo;
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
            $content = $this->contentRepo->getByUrl($url, $lang);
            if (!empty($content) and $content->isActive()) { // Only if page is visible on public
                $type = $this->resolveType($content->getType()->getName());
                return $type->load($content, $lang)->render();
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
        $type = $this->app->make('content_type:' . $typeName);
        if (!$type instanceof ContentTypeHandler) {
            throw new \ReflectionException("Type: $typeName must implement ContentTypeInterface");
        }
        return $type;
    }
}
