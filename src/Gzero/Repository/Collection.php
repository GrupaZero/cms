<?php namespace Gzero\Repository;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Collection
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Collection extends \Illuminate\Support\Collection {

    /**
     * @var int
     */
    protected $total;

    /**
     * Collection controller
     *
     * @param array $items Result form query
     * @param int   $total Total number of elements
     */
    public function __construct(array $items = [], $total = null)
    {
        $this->total = (integer) $total;
        $this->items = $items;
    }

    /**
     * Get total
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }
}
