<?php namespace Gzero\Repository;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var ArrayCollection
     */
    private $langs;

    /**
     * Init languages from database or cache
     */
    public function init()
    {
        /** @var $cache \Illuminate\Cache\Repository */
        $cache = App::make('cache');
        if ($cache->get('langs')) {
            $this->langs = $cache->get('langs');
        } else {
            /* @var QueryBuilder $qb */
            $qb          = $this->_em->createQueryBuilder();
            $langs       = $qb->select('l')
                ->from($this->getClassName(), 'l')
                ->getQuery()->getResult();
            $this->langs = $this->prepareLangsArray($langs);
            $cache->forever('langs', $this->langs);
        }
    }

    /**
     * Refresh langs cache
     *
     * @return bool
     */
    public function refresh()
    {
        /** @var $cache \Illuminate\Cache\Repository */
        $cache = App::make('cache');
        if ($cache->has('langs')) {
            $cache->forget('langs');
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param $code
     *
     * @return \Gzero\Entity\Lang
     */
    public function getByCode($code)
    {
        return $this->langs->get($code);
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAllActive()
    {
        return $this->langs->filter(
            function ($lang) {
                if ($lang->isActive()) {
                    return $lang;
                }
            }
        );
    }

    /**
     * @return ArrayCollection
     */
    public function getAll()
    {
        return $this->langs;
    }

    /**
     * @param array $langs
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

}
