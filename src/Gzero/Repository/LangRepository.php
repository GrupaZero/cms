<?php namespace Gzero\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
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
class LangRepository extends BaseRepository {

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
     * Init languages from database or cache
     *
     * @param Repository $cache Cache
     *
     * @return void
     */
    public function init(Repository $cache)
    {
        $this->cache = $cache;
        if ($this->cache->get('langs')) {
            $this->langs = $this->cache->get('langs');
        } else {
            /* @var QueryBuilder $qb */
            $qb          = $this->_em->createQueryBuilder();
            $this->langs = $this->prepareLangsArray(
                $qb->select('l')
                    ->from($this->getClassName(), 'l')
                    ->getQuery()->getResult()
            );
            $cache->forever('langs', $this->langs);
        }
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
        $this->checkIfInitialized();
        return $this->langs->get($code);

    }

    /**
     * Get current language
     *
     * @return \Gzero\Entity\Lang
     */
    public function getCurrent()
    {
        $this->checkIfInitialized();
        return $this->getByCode(App::getLocale());
    }

    /**
     * Get all languages
     *
     * @return ArrayCollection
     */
    public function getAll()
    {
        $this->checkIfInitialized();
        return $this->langs;
    }

    /**
     * Get all enabled langs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAllEnabled()
    {
        $this->checkIfInitialized();
        return $this->langs->filter(
            function ($lang) {
                return ($lang->isEnabled()) ? $lang : false;
            }
        );
    }

    /**
     * Build langs collection where key is a lang code
     *
     * @param array $langs Array of langs
     *
     * @return ArrayCollection
     */
    private function prepareLangsArray(Array $langs)
    {
        $returnArray = [];
        foreach ($langs as $lang) {
            $returnArray[$lang->getCode()] = $lang;
        }
        return new ArrayCollection($returnArray);
    }

    /**
     * This function checking if repository was initialized
     *
     * @throws RepositoryException
     * @return void
     */
    private function checkIfInitialized()
    {
        if (!isset($this->langs)) {
            throw new RepositoryException('You must init repository first');
        }
    }

}
