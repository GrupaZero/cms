<?php namespace Gzero\Cms\Handlers\Block;

use Gzero\Cms\Models\Block;
use Gzero\Core\Models\Language;

trait CacheBlockTrait {

    /**
     * Get rendered block from cache
     *
     * @param Block    $block Block
     * @param Language $lang  Language
     *
     * @return string|null
     */
    protected function getFromCache(Block $block, Language $lang)
    {
        if ($block->is_cacheable) {
            return cache('blocks:cache:' . $block->id . ':' . $lang->code, null);
        }
        return null;
    }

    /**
     * Put rendered html in to block cache
     *
     * @param Block    $block Block
     * @param Language $lang  Language
     * @param string   $html  Rendered html
     *
     * @return void
     */
    protected function putInCache(Block $block, Language $lang, $html)
    {
        if ($block->is_cacheable) {
            cache()->forever('blocks:cache:' . $block->id . ':' . $lang->code, $html);
        }
    }

}
