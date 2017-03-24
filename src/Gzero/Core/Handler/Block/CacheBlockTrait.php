<?php namespace Gzero\Core\Handler\Block;

use Gzero\Entity\Block;
use Gzero\Entity\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class AbstractHandler
 *
 * @package    Gzero\Core\Handler\Block
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2017, Adrian Skierniewski
 */
trait CacheBlockTrait {

    /**
     * Get rendered block from cache
     *
     * @param Block $block Block
     * @param Lang  $lang  Lang
     *
     * @return string|null
     */
    protected function getFromCache(Block $block, Lang $lang)
    {
        if ($block->is_cacheable) {
            return cache('blocks:cache:' . $block->id . ':' . $lang->code, null);
        }
        return null;
    }

    /**
     * Put rendered html in to block cache
     *
     * @param Block  $block Block
     * @param Lang   $lang  Lang
     * @param string $html  Rendered html
     *
     * @return void
     */
    protected function putInCache(Block $block, Lang $lang, $html)
    {
        if ($block->is_cacheable) {
            cache()->forever('blocks:cache:' . $block->id . ':' . $lang->code, $html);
        }
    }

}
