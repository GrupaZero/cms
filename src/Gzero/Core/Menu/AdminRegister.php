<?php namespace Gzero\Core\Menu;


/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class AdminRegister
 *
 * @package    Gzero\Core
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class AdminRegister extends MenuRegister {

    protected $modules = [];

    /**
     * Function adds register AngularJS module
     *
     * @param string $name Module name
     * @param string $path Module path to js file
     *
     * @return void
     */
    public function addModule($name, $path)
    {
        $this->modules[] = ['name' => $name, 'path' => $path];
    }

    /**
     * Function returns all AngularJS modules names and paths
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }
}
