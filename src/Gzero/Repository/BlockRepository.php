<?php namespace Gzero\Repository;

use Gzero\Entity\Content;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Events\Dispatcher;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class BlockRepository extends BaseRepository {

    /**
     * @var Content
     */
    protected $model;

    /**
     * The events dispatcher
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * Content repository constructor
     *
     * @param Content    $content Content model
     * @param Dispatcher $events  Events dispatcher
     */
    public function __construct(Content $content, Dispatcher $events)
    {
        $this->model  = $content;
        $this->events = $events;
    }

    // @codingStandardsIgnoreStart

    /**
     * Eager load relations for eloquent collection.
     * We use this function in handlePagination method!
     * @SuppressWarnings("unused")
     *
     * @param EloquentCollection $results Eloquent collection
     *
     * @return void
     */
    protected function listEagerLoad($results)
    {
        // TODO: Implement listEagerLoad() method.
    }

    // @codingStandardsIgnoreEnd
}
