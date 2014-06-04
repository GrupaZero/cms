<?php namespace Gzero\Core\Handler\Block;

use Gzero\Entity\Lang;
use Gzero\Handler\Block\BlockHandlerException;
use Gzero\Repository\MenuLinkRepository;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Menu
 *
 * @package    Gzero\BlockTypeHandlers
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Menu implements BlockTypeHandler {

    private $block;
    private $menuRepo;

    public function __construct(MenuLinkRepository $menu)
    {
        $this->menuRepo = $menu;
    }

    /**
     * {@inheritdoc}
     */
    public function load($block, Lang $lang)
    {
        if ($block->getMenu()) {
            $this->block = $block;
        } else {
            throw new BlockHandlerException('Block Menu Handler: Menu not found!');
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $translations = $this->block->getTranslations()->first();
        $menu         = $this->block->getMenu();
        return \View::make('blocks.menu', ['block' => $this->block, 'translations' => $translations, 'menu' => $menu])->render();
    }
}
