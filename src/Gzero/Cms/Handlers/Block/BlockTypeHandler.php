<?php namespace Gzero\Cms\Handlers\Block;

use Gzero\Cms\Models\Block;
use Gzero\Core\Models\Language;

interface BlockTypeHandler {

    /**
     * Load block
     *
     * @param Block    $block    Block
     * @param Language $language Language
     *
     * @return BlockTypeHandler
     */
    public function handle(Block $block, Language $language);

}
