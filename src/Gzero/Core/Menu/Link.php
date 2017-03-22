<?php namespace Gzero\Core\Menu;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Link
 *
 * @package    Gzero\Core\Menu
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2017, Adrian Skierniewski
 */
class Link {

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $weight;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $children;

    /**
     * Link constructor.
     *
     * @param string $url      Link url
     * @param string $title    Link title
     * @param int    $weight   Link weight
     * @param array  $children Link children
     */
    public function __construct($url, $title, int $weight = 0, $children = [])
    {
        $this->url      = $url;
        $this->title    = $title;
        $this->weight   = $weight;
        $this->children = collect($children);
    }

}
