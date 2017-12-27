<?php namespace Gzero\Cms\Handlers\Block;

use Gzero\Cms\Models\Block;
use Gzero\Core\Models\Language;

class Menu implements BlockTypeHandler {

    use CacheBlockTrait;

    /**
     * Load block
     *
     * @param Block    $block    Block
     * @param Language $language Language
     *
     * @return string
     */
    public function handle(Block $block, Language $language)
    {
        $html = $this->getFromCache($block, $language);
        if ($html !== null) {
            return $html;
        }
        $html = view('gzero-cms::blocks.menu', [
            'block' => $block
        ])->render();
        $this->putInCache($block, $language, $html);
        return $html;
    }
}
