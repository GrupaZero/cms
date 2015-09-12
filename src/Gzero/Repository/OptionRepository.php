<?php namespace Gzero\Repository;

use Gzero\Entity\OptionCategories;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class OptionRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class OptionRepository {

    /**
     * @var OptionCategory
     */
    protected $model;

    /**
     * @var Collection
     */
    private $options;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * OptionRepository constructor
     *
     * @param OptionCategories $model Model
     * @param CacheManager   $cache Cache
     */
    public function __construct(OptionCategories $model, CacheManager $cache)
    {
        $this->model = $model;
        $this->cache = $cache;
        $this->init();
    }

    /**
     * Init options from database or cache
     *
     * @return void
     */
    protected function init()
    {
        if ($this->cache->get('options')) {
            $this->options = $this->cache->get('options');
        } else {
            $this->options = $this->model->newQuery()->with('options')->get();
            $this->cache->forever('options', $this->options);
        }
    }
}
