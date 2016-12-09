<?php namespace Gzero\Entity\Presenter;

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
class UserPresenter extends BasePresenter {

    /**
     * Get display name nick or first and last name
     *
     * @return string
     */
    public function displayName()
    {
        if ($this->nick && config('gzero.use_users_nicks')) {
            return $this->nick;
        }

        if ($this->firstName || $this->lastName) {
            return $this->firstName . ' ' . $this->lastName;
        }
        return trans('common.anonymous');
    }

}
