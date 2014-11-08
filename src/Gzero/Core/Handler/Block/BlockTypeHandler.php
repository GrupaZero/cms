<?php  namespace Gzero\Core\Handler\Block;

use Gzero\Entity\Block;
use Gzero\Entity\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockTypeHandler
 *
 * @package    Gzero\BlockTypeHandlers
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
interface BlockTypeHandler {

    /**
     * Load block
     *
     * @param Block $block Block entity
     * @param Lang  $lang  Lang entity
     *
     * @return BlockTypeHandler
     */
    public function load(Block $block, Lang $lang);

    /**
     * Renders block
     *
     * @return string
     */
    public function render();
}
