<?php namespace Gzero\Repository;

use Gzero\Entity\User;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Hash;

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
class UserRepository extends BaseRepository implements UserProviderInterface {

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
     * Content repository constructor
     *
     * @param User       $user   Content model
     * @param Dispatcher $events Events dispatcher
     */
    public function __construct(User $user, Dispatcher $events)
    {
        $this->model = $user;
        $this->events = $events;
    }

    // @codingStandardsIgnoreStart

    /**
     * Create specific content entity
     *
     * @param array $data Content entity to persist
     *
     * @return User
     */
    public function create(Array $data)
    {
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
     * Eager load relations for eloquent collection
     *
     * @param Collection $results Eloquent collection
     *
     * @return void
     */
    protected function listEagerLoad($results)
    {
        //$results->load('relation');
        $results->count(); // Place holder
    }

    /*
    |--------------------------------------------------------------------------
    | START UserProviderInterface
    |--------------------------------------------------------------------------
    */

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveById($identifier)
    {
        return $this->getById($identifier);
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string $token
     *
     * @return \Illuminate\Auth\UserInterface|null
     * @SuppressWarnings("unused")
     */
    public function retrieveByToken($identifier, $token)
    {
        $qb = $this->newQB()
            ->select('u')
            ->from($this->getClassName(), 'u')
            ->where('u.rememberToken = :token')
            ->setParameter('token', $token);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $qb = $this->newQB()
            ->select('u')
            ->from($this->getClassName(), 'u');
        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $qb->where("u.$key=:value")->setParameter('value', $value);
            };
        }
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Auth\UserInterface $user
     * @param  array                          $credentials
     *
     * @return bool
     */
    public function validateCredentials(UserInterface $user, array $credentials)
    {
        $plain = $credentials['password'];
        return Hash::check($plain, $user->getAuthPassword());
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Auth\UserInterface $user
     * @param  string                         $token
     *
     * @return void
     */
    public function updateRememberToken(UserInterface $user, $token)
    {
        $user->setRememberToken($token);
        $this->_em->persist($user);
        $this->commit();
    }

    /*
    |--------------------------------------------------------------------------
    | END UserProviderInterface
    |--------------------------------------------------------------------------
    */
}
