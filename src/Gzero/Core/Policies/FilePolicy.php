<?php

namespace Gzero\Core\Policies;

use Gzero\Entity\File;
use Gzero\Entity\User;

class FilePolicy {

    /**
     * Policy for displaying list of entities
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function readList(User $user)
    {
        return $user->hasPermission('file-read');
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
        return $user->hasPermission('file-read');
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
        return $user->hasPermission('file-create');
    }

    /**
     * Policy for displaying single element
     *
     * @param User $user User trying to do it
     * @param File $file File that we're trying to update
     *
     * @return boolean
     */
    public function update(User $user, File $file)
    {
        if ($file->author->id === $user->id) {
            return true;
        }
        return $user->hasPermission('file-update');
    }

    /**
     * Policy for deleting single element
     *
     * @param User $user User trying to do it
     * @param File $file File that we're trying to update
     *
     * @return boolean
     */
    public function delete(User $user, File $file)
    {
        if ($file->author->id === $user->id) {
            return true;
        }
        return $user->hasPermission('file-delete');
    }
}
