<?php namespace Gzero\Handler\Block;

use Gzero\Models\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockTypeHandlerInterface
 *
 * @package    Gzero\BlockTypeHandlers
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
interface BlockTypeHandler {

    /**
     * @param      $block
     * @param Lang $lang
     *
     * @return BlockTypeHandler
     */
    public function load($block, Lang $lang);

    /**
     * @return string
     */
    public function render();
} 
