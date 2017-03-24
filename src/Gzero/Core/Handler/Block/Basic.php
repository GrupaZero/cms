<?php namespace Gzero\Core\Handler\Block;

use Gzero\Entity\Block;
use Gzero\Entity\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Basic
 *
 * @package    Gzero\BlockTypeHandlers
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Basic implements BlockTypeHandler {

    use CacheBlockTrait;

    /**
     * Load block
     *
     * @param Block $block Block entity
     * @param Lang  $lang  Lang entity
     *
     * @return string
     */
    public function render(Block $block, Lang $lang)
    {
        $html = $this->getFromCache($block, $lang);
        if ($html !== null) {
            return $html;
        }
        $html = view('blocks.basic', ['block' => $block, 'lang' => $lang])->render();
        $this->putInCache($block, $lang, $html);
        return $html;
    }


}
