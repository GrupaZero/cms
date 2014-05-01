<?php namespace Gzero\Core\Filter;

use Gzero\Core\BlockHandler;
use Gzero\Entity\Lang;

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

    protected $regions;
    protected $langRepo;
    protected $handler;

    public function __construct(BlockHandler $block)
    {
        $this->handler  = $block;
//        $this->langRepo = $lang;
    }

    public function filter()
    {
        $this->handler->loadAllActive('/', new Lang('pl', 'pl_PL'));
    }

} 
