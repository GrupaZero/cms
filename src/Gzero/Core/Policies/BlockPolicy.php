<?php

namespace Gzero\Core\Policies;

use Gzero\Entity\Block;
use Gzero\Entity\User;

class BlockPolicy {

    /**
     * Policy for displaying list of entities
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function readList(User $user)
    {
        return $user->hasPermission('block-read');
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
        return $user->hasPermission('block-read');
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
        return $user->hasPermission('block-create');
    }

    /**
     * Policy for displaying single element
     *
     * @param User  $user  User trying to do it
     * @param Block $block Block that we're trying to update
     *
     * @return boolean
     */
    public function update(User $user, Block $block)
    {
        if ($block->author->id === $user->id) {
            return true;
        }
        return $user->hasPermission('block-update');
    }

    /**
     * Policy for deleting single element
     *
     * @param User  $user  User trying to do it
     * @param Block $block Block that we're trying to update
     *
     * @return boolean
     */
    public function delete(User $user, Block $block)
    {
        if ($block->author->id === $user->id) {
            return true;
        }
        return $user->hasPermission('block-delete');
    }
}
