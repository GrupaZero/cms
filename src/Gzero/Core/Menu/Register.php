<?php namespace Gzero\Core\Menu;

use Exception;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Register
 *
 * @package    Gzero\Core
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Register {


    protected $modules = [];

    /**
     * Function adds link to user panel menu
     *
     * @param string $url
     * @param string $title
     */
    public function addLink($url, $title)
    {
        $this->modules[] = ['url' => $url, 'title' => $title, 'children' => []];
    }

    /**
     * Function returns whole menu as tree
     *
     * @return array
     */
    public function getMenu()
    {
        return $this->modules;
    }

    /**
     * Function adds child link to parent specified by url parameter
     *
     * @param array  $child ['url' => $url, 'title' => $title, 'alt' => NULL]
     * @param string $parentUrl
     *
     * @return bool Return true if link added successfully otherwise false
     */
    public function addChild(array $child, $parentUrl)
    {
        return $this->addNextChild($child, $parentUrl, $this->modules);
    }

    /**
     * Functions searches parent link specified by url and adds child link to the parent
     *
     * @param array  $child
     * @param string $url
     * @param array  $haystack
     *
     * @return bool Return true if link added successfully otherwise false
     * @throws Exception
     */
    protected function addNextChild(array $child, $url, array &$haystack)
    {
        if (!isset($child['url'])) {
            throw new Exception("Class UserPanelMenu: 'url' key i required");
        }
        foreach ($haystack as &$value) {
            if ($value['url'] == $url) {
                $child['children']   = [];
                $value['children'][] = $child;
                return TRUE;
            }
            if (isset($value['children']) and is_array($value['children'])) {
                $this->addNextChild($child, $url, $value['children']);
            }
        }
        return FALSE;
    }
}
