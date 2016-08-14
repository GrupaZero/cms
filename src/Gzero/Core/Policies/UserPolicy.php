<?php

namespace Gzero\Core\Policies;

use Gzero\Entity\User;

class UserPolicy {

    /**
     * Policy for displaying list of entities
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function readList(User $user)
    {
        return $user->hasPermission('user-read');
    }

    /**
     * Policy for displaying single element
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function read(User $user)
    {
        return $user->hasPermission('user-read');
    }

    /**
     * Policy for creating single element
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function create(User $user)
    {
        return $user->hasPermission('user-create');
    }

    /**
     * Policy for displaying single element
     *
     * @param User $user   User trying to do it
     * @param User $entity User that we're trying to update
     *
     * @return boolean
     */
    public function update(User $user, User $entity)
    {
        if ($entity->author->id === $user->id) {
            return true;
        }
        return $user->hasPermission('user-update');
    }

    /**
     * Policy for deleting single element
     *
     * @param User $user   User trying to do it
     * @param User $entity User that we're trying to update
     *
     * @return boolean
     */
    public function delete(User $user, User $entity)
    {
        if ($entity->author->id === $user->id) {
            return true;
        }
        return $user->hasPermission('user-delete');
    }
}
