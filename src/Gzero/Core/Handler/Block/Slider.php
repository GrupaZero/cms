<?php namespace Gzero\Core\Handler\Block;

use Gzero\Entity\Block;
use Gzero\Entity\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Slider
 *
 * @namespace    Gzero\BlockTypeHandlers
 * @author       Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright    Copyright (c) 2014, Adrian Skierniewski
 */
class Slider implements BlockTypeHandler {

    /**
     * @var
     */
    private $block;

    /**
     * @var
     */
    private $translations;
    // @codingStandardsIgnoreStart

    /**
     * {@inheritdoc}
     */
    public function load(Block $block, Lang $lang)
    {
        $this->block        = $block;
        $this->translations = $block->getPresenter()->translation($lang->code);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return \View::make(
            'blocks.slider',
            ['block' => $this->block, 'translations' => $this->translations]
        )->render();
    }

    // @codingStandardsIgnoreEnd
}
