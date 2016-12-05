<?php namespace Gzero\Repository;

use Gzero\Entity\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class UserRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class UserRepository extends BaseRepository implements AuthenticatableContract {

    /**
     * @var User
     */
    protected $model;

    /**
     * The events dispatcher
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * User repository constructor
     *
     * @param User       $user   Content model
     * @param Dispatcher $events Events dispatcher
     */
    public function __construct(User $user, Dispatcher $events)
    {
        $this->model  = $user;
        $this->events = $events;
    }

    // @codingStandardsIgnoreStart

    /**
     * Retrieve a user by given email
     *
     * @param  string $email
     *
     * @return User
     */
    public function getByEmail($email)
    {
        $qb = $this->newQuery()
            ->table($this->getTableName())
            ->where('email', '=', $email);
        return $qb->first();
    }

    /**
     * Create specific user entity
     *
     * @param array $data User entity to persist
     *
     * @return User
     */
    public function create(Array $data)
    {
        // handle empty nickname users
        if (empty($data['nick'])) {
            $data['nick'] = $this->buildUniqueNickname();
        }
        $user = $this->newQuery()->transaction(
            function () use ($data) {
                $user = new User();
                $user->fill($data);
                $user->save();
                return $user;
            }
        );
        $this->events->fire('user.created', [$user]);
        return $user;
    }

    /**
     * Update specific user entity
     *
     * @param User  $user user entity
     * @param array $data data to save
     *
     * @return User
     * @throws \Exception
     */
    public function update(User $user, Array $data)
    {
        $user = $this->newQuery()->transaction(
            function () use ($user, $data) {
                if (array_key_exists('password', $data)) {
                    $data['password'] = Hash::make($data['password']);
                }
                $user->fill($data);
                $user->save();
                return $user;
            }
        );
        $this->events->fire('user.updated', [$user]);
        return $user;
    }

    /**
     * Eager load relations for eloquent collection
     *
     * @param Collection $results Eloquent collection
     *
     * @return void
     */
    protected function listEagerLoad($results)
    {
        $results->load('roles');
    }

    /**
     * Delete specific user entity
     *
     * @param User $user User entity to delete
     *
     * @return boolean
     */
    public function delete(User $user)
    {
        return $user->delete();
    }

    /**
     * Get all users with specific criteria
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return Collection
     */
    public function getUsers(array $criteria = [], array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query  = $this->newORMQuery();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->userDefaultOrderBy()
        );
        return $this->handlePagination($this->getTableName(), $query, $page, $pageSize);
    }

    /**
     * Default order for user query
     *
     * @return callable
     */
    protected function userDefaultOrderBy()
    {
        return function ($query) {
            $query->orderBy('id', 'DESC');
        };
    }

    /*
    |--------------------------------------------------------------------------
    | START AuthenticatableContract
    |--------------------------------------------------------------------------
    */

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->model->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->model->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->model->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->model->{$this->getRememberTokenName()};
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->model->{$this->getRememberTokenName()} = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /*
    |--------------------------------------------------------------------------
    | END AuthenticatableContract AND CanResetPasswordContract
    |--------------------------------------------------------------------------
    */

    /**
     * Function returns an unique user nickname from given url in specific language
     *
     * @param string $replacement string nick replacement to use, "Anonymous" is default
     *
     * @return string $nickname an unique user nickname
     */
    protected function buildUniqueNickname($replacement = 'anonymous')
    {
        // search for duplicated url
        $count = $this->newQuery()
            ->table('users')
            ->whereRaw("nick REGEXP '^$replacement($|-[0-9]+$)'")
            ->count();
        return ($count) ? $replacement . '-' . $count : $replacement;
    }
}
