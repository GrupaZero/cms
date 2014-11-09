<?php namespace Gzero\Core\Filter;

use Gzero\Core\BlockHandler;
use Gzero\Repository\LangRepository;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Block
 *
 * @package    Gzero\Core\Filters
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Block {

    protected $langRepo;

    protected $handler;

    /**
     * Block filter constructor
     *
     * @param BlockHandler   $block Block repository
     * @param LangRepository $lang  Lang repository
     */
    public function __construct(BlockHandler $block, LangRepository $lang)
    {
        $this->handler  = $block;
        $this->langRepo = $lang;
    }

    /**
     * Run filter
     *
     * @return void
     */
    public function filter()
    {
        $lang = $this->langRepo->getCurrent();
        if (!empty($lang)) {
            $regions = $this->handler->loadAllActive('/', $lang)->getRegions();
        } else {
            $regions = [];
        }
        \View::share('regions', $regions);
    }
}
