<?php

namespace Gzero\Core\Policies;

use Gzero\Entity\User;

class OptionPolicy {

    /**
     * Policy for displaying single element
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function read(User $user)
    {
        return $user->hasPermission('options-read-read');
    }

    /**
     * Policy for updating general options
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function updateGeneral(User $user)
    {
        return $user->hasPermission('options-update-general');
    }

    /**
     * Policy for updating seo options
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function updateSEO(User $user)
    {
        return $user->hasPermission('options-update-seo');
    }

}
