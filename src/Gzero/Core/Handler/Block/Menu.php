<?php namespace Gzero\Handler\Block;

use Gzero\Models\Lang;
use Gzero\Repositories\MenuLink\MenuLinkRepository;

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
        if ($block->menu_id) {
            $menu        = $this->menuRepo->getById($block->menu_id);
            $block->menu = $this->menuRepo->buildTree($this->menuRepo->getDescendants($menu));
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
        return \View::make('blocks.menu', ['block' => $this->block])->render();
    }
}
