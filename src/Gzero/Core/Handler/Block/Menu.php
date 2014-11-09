<?php namespace Gzero\Core\Handler\Block;

use Gzero\Entity\Block;
use Gzero\Entity\Lang;
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

    /**
     * Menu constructor
     *
     * @param MenuLinkRepository $menu Menu repository
     */
    public function __construct(MenuLinkRepository $menu)
    {
        $this->menuRepo = $menu;
    }

    // @codingStandardsIgnoreStart

    /**
     * {@inheritdoc}
     */
    public function load(Block $block, Lang $lang)
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
        $translation = $this->block->getTranslations()->first();
        $menu        = $this->block->getMenu();
        $menuTree    = $this->menuRepo->getDescendants($menu, true);
        return \View::make(
            'blocks.menu',
            [
                'block'        => $this->block,
                'translations' => $translation,
                'menu'         => $menuTree
            ]
        )->render();
    }

    // @codingStandardsIgnoreEnd
}
