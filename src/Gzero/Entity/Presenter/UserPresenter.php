<?php namespace Gzero\Entity\Presenter;

use Robbo\Presenter\Presenter;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class UserPresenter
 *
 * @package    Gzero\Entity\Presenter
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class UserPresenter extends Presenter {

    /**
     * Get display name nick or first and last name
     *
     * @return string
     */
    public function displayName()
    {
        if ($this->nickName && config('gzero.useUsersNickNames')) {
            return $this->nickName;
        }

        if ($this->firstName || $this->lastName) {
            return $this->firstName . ' ' . $this->lastName;
        } else {
            return trans('common.anonymous');
        }
    }

}
