<?php

namespace Gzero\Core\Policies;

use Gzero\Entity\Option;
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
        return $user->hasPermission('options-read');
    }

    /**
     * Policy for updating options for specified category
     *
     * @param User   $user        User trying to do it
     * @param Option $option      Option class name
     * @param String $categoryKey option category
     *
     * @return bool
     */
    public function update(User $user, $option, $categoryKey)
    {
        if (!empty($option)) {
            return $user->hasPermission('options-update-' . $categoryKey);
        }
        return false;
    }

}
