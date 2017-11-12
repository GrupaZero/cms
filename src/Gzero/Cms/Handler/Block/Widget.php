<?php namespace Gzero\Cms\Handler\Block;

use Gzero\Cms\Models\Block;
use Gzero\Core\Models\Language;

class Widget implements BlockTypeHandler {

    use CacheBlockTrait;

    /**
     * Load block
     *
     * @param Block    $block Block
     * @param Language $lang  Language
     *
     * @return string
     */
    public function render(Block $block, Language $lang)
    {
        $html = $this->getFromCache($block, $lang);
        if ($html !== null) {
            return $html;
        }
        $html = view('blocks.widget', ['block' => $block, 'lang' => $lang])->render();
        $this->putInCache($block, $lang, $html);
        return $html;
    }
}
