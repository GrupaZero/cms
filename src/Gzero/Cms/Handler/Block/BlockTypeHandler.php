<?php namespace Gzero\Cms\Handler\Block;

use Gzero\Cms\Models\Block;
use Gzero\Core\Models\Language;

interface BlockTypeHandler {

    /**
     * Load block
     *
     * @param Block    $block Block
     * @param Language $lang  Language
     *
     * @return BlockTypeHandler
     */
    public function render(Block $block, Language $lang);

}
