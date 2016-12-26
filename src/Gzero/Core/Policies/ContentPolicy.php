<?php

namespace Gzero\Core\Policies;

use Gzero\Entity\Content;
use Gzero\Entity\User;

class ContentPolicy {

    /**
     * Policy for displaying list of entities
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function readList(User $user)
    {
        return $user->hasPermission('content-read');
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
        return $user->hasPermission('content-read');
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
        return $user->hasPermission('content-create');
    }

    /**
     * Policy for displaying single element
     *
     * @param User    $user    User trying to do it
     * @param Content $content Content that we're trying to update
     *
     * @return boolean
     */
    public function update(User $user, Content $content)
    {
        if ($content->author->id === $user->id) {
            return true;
        }
        return $user->hasPermission('content-update');
    }

    /**
     * Policy for deleting single element
     *
     * @param User    $user    User trying to do it
     * @param Content $content Content that we're trying to update
     *
     * @return boolean
     */
    public function delete(User $user, Content $content)
    {
        if ($content->author->id === $user->id) {
            return true;
        }
        return $user->hasPermission('content-delete');
    }

    /**
     * Policy for viewing single unpublished element
     *
     * @param User    $user    User trying to do it
     * @param Content $content Content that we're trying to update
     *
     * @return boolean
     */
    public function viewUnpublished(User $user, Content $content)
    {
        return ($content->author->id === $user->id);
    }
}
