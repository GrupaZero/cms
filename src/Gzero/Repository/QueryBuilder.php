<?php namespace Gzero\Repository;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Query
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2017, Adrian Skierniewski
 */
class QueryBuilder {

    /**
     * Default number of items per page
     */
    const ITEMS_PER_PAGE = 20;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $filters;
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $sorts;
    /**
     * @var string
     */
    protected $searchQuery;

    /**
     * @var int
     */
    protected $pageSize;

    /**
     * Query constructor.
     *
     * @param array $filters  Criteria array
     * @param array $sorts    Order by array
     * @param null  $search   Search query
     * @param int   $pageSize Page size
     */
    public function __construct(array $filters = [], array $sorts = [], $search = null, $pageSize = self::ITEMS_PER_PAGE)
    {
        $this->filters     = collect($filters);
        $this->sorts       = collect($sorts);
        $this->searchQuery = $search;
        $this->pageSize    = $pageSize;
    }

    /**
     * It resets query builder
     *
     * @return void
     */
    public function reset()
    {
        $this->filters     = collect([]);
        $this->sorts       = collect([]);
        $this->searchQuery = null;
        $this->pageSize    = self::ITEMS_PER_PAGE;
    }

    /**
     * It resets filters
     *
     * @return void
     */
    public function resetFilters()
    {
        $this->filters = collect([]);
    }

    /**
     * It resets filters
     *
     * @return void
     */
    public function resetSorts()
    {
        $this->sorts = collect([]);
    }

    /**
     * Adds next criteria entry
     *
     * @param string $name      Column name
     * @param string $condition Condition
     * @param mixed  $value     Value
     *
     * @return void
     */
    public function addFilter(string $name, string $condition, $value)
    {
        $this->filters->push([$name, $condition, $value]);
    }

    /**
     * Adds next order by entry
     *
     * @param string $name      Column name
     * @param string $direction Direction ASC|DESC
     *
     * @return void
     */
    public function addSort(string $name, string $direction = 'DESC')
    {
        $this->sorts->push([$name, $direction]);
    }

    /**
     * It sets search query
     *
     * @param string $search Search string
     *
     * @return void
     */
    public function setSearchQuery(string $search)
    {
        $this->searchQuery = $search;
    }

    /**
     * Set page size
     *
     * @param int $pageSize Page size
     *
     * @return void
     */
    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * Checks if search query is present
     *
     * @return bool
     */
    public function hasSearchQuery()
    {
        return (bool) $this->searchQuery;
    }

    /**
     * Get criteria collection
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get order by collection
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSorts()
    {
        return $this->sorts;
    }

    /**
     * Get search query
     *
     * @return string
     */
    public function getSearchQeury()
    {
        return $this->searchQuery;
    }

    /**
     * Get page size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Query factory method
     *
     * @param array $filters  Criteria array
     * @param array $sorts    Order by array
     * @param null  $search   Search query
     * @param int   $pageSize Page size
     *
     * @return QueryBuilder
     */
    public static function with(array $filters = [], array $sorts = [], $search = null, $pageSize = self::ITEMS_PER_PAGE)
    {
        return new self($filters, $sorts, $search, $pageSize);
    }

    /**
     * Query factory method
     *
     * @param array $sorts    Order by array
     * @param null  $search   Search query
     * @param int   $pageSize Page size
     *
     * @return QueryBuilder
     */
    public static function withSort(array $sorts = [], $search = null, $pageSize = self::ITEMS_PER_PAGE)
    {
        return new self([], $sorts, $search, $pageSize);
    }

    /**
     * Query factory method
     *
     * @param null $search   Search query
     * @param int  $pageSize Page size
     *
     * @return QueryBuilder
     */
    public static function withSearch($search = null, $pageSize = self::ITEMS_PER_PAGE)
    {
        return new self([], [], $search, $pageSize);
    }

    /**
     * Query factory method
     *
     * @param int $pageSize Page size
     *
     * @return QueryBuilder
     */
    public static function withPageSize($pageSize = self::ITEMS_PER_PAGE)
    {
        return new self([], [], null, $pageSize);
    }
}
