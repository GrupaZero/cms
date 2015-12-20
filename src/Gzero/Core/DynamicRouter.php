<?php namespace Gzero\Core;

use Gzero\Repository\ContentRepository;
use Illuminate\Events\Dispatcher;
use Gzero\Entity\Lang;
use Gzero\Core\Handler\Content\ContentTypeHandler;
use Illuminate\Support\Facades\View;
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
     * @var ContentRepository
     */
    private $repository;

    /**
     * The events dispatcher
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * DynamicRouter constructor
     *
     * @param ContentRepository $repository Content repository
     * @param Dispatcher        $events     Events dispatcher
     */
    public function __construct(ContentRepository $repository, Dispatcher $events)
    {
        $this->repository = $repository;
        $this->events     = $events;
    }

    /**
     * Handles dynamic content rendering
     *
     * @param String $url  Url address
     * @param Lang   $lang Lang entity
     *
     * @return View
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function handleRequest($url, Lang $lang)
    {
        try {
            $content = $this->repository->getByUrl($url, $lang->code);
            // Only if page is visible on public
            if (!empty($content) && $content->canBeShown()) {
                if (!$content->isActive) {
                    app('session')->flash(
                        'messages',
                        [
                            [
                                'code' => 'warning',
                                'text' => trans('common.contentNotPublished')
                            ]
                        ]
                    );
                }
                $this->events->fire('router.contentMatched', [$content]);
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
        $type = app()->make('content_type:' . $typeName);
        if (!$type instanceof ContentTypeHandler) {
            throw new \ReflectionException("Type: $typeName must implement ContentTypeInterface");
        }
        return $type;
    }
}
