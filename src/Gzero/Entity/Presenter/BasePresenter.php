<?php namespace Gzero\Entity\Presenter;

use Robbo\Presenter\Presenter;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BasePresenter
 *
 * @package    Gzero\Entity\Presenter
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2016, Adrian Skierniewski
 */
class BasePresenter extends Presenter {

    /**
     * Pass any unknown variable calls to present{$variable} or fall through to the injected object.
     *
     * @param string $var Variable name
     *
     * @return mixed
     */
    public function __get($var)
    {
        return parent::__get(snake_case($var));
    }

    /**
     * Allow ability to run isset() on a variable
     *
     * @param string $name Variable name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return parent::__isset(snake_case($name));
    }

    /**
     * Allow to unset a variable through the presenter
     *
     * @param string $name Variable name
     *
     * @return void
     */
    public function __unset($name)
    {
        parent::__unset(snake_case($name));
    }

}
