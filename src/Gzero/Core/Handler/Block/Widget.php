<?php namespace Gzero\Core\Handler\Block;

use Gzero\Entity\Block;
use Gzero\Entity\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Widget
 *
 * @package    Gzero\BlockTypeHandlers
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Widget implements BlockTypeHandler {

    /**
     * @var
     */
    private $block;

    // @codingStandardsIgnoreStart
    /**
     * {@inheritdoc}
     */
    public function load(Block $block, Lang $lang)
    {
        $this->block = $block;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        //return \View::make(
        //    'blocks.basic',
        //    ['block' => $this->block, 'translations' => $this->block->getTranslations()->first()]
        //)->render();
    }
    // @codingStandardsIgnoreEnd
}
