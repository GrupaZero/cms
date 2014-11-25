<?php namespace Gzero\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Gzero\Entity\Lang;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\App;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class LangRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class LangRepository {

    /**
     * All languages
     *
     * @var ArrayCollection
     */
    private $langs;

    /**
     * @var Repository
     */
    private $cache;


    /**
     * LangRepository constructor
     *
     * @param CacheManager $cache Cache
     */
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
        $this->init();
    }

    /**
     * Refresh langs cache
     *
     * @return boolean
     */
    public function refresh()
    {
        if ($this->cache->has('langs')) {
            $this->cache->forget('langs');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get lang by lang code
     *
     * @param string $code Lang code eg. "en"
     *
     * @throws RepositoryException
     * @return \Gzero\Entity\Lang
     */
    public function getByCode($code)
    {
        return $this->langs->filter(
            function ($lang) use ($code) {
                return $lang->code == $code;
            }
        )->first();
    }

    /**
     * Get current language
     *
     * @return \Gzero\Entity\Lang
     */
    public function getCurrent()
    {
        return $this->getByCode(App::getLocale());
    }

    /**
     * Get all languages
     *
     * @return ArrayCollection
     */
    public function getAll()
    {
        return $this->langs;
    }

    /**
     * Get all enabled langs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAllEnabled()
    {
        return $this->langs->filter(
            function ($lang) {
                return ($lang->isEnabled) ? $lang : false;
            }
        );
    }

    /**
     * Init languages from database or cache
     *
     * @return void
     */
    protected function init()
    {
        if ($this->cache->get('langs')) {
            $this->langs = $this->cache->get('langs');
        } else {
            /* @var QueryBuilder $qb */
            $this->langs = Lang::all();
            $this->cache->forever('langs', $this->langs);
        }
    }
}
