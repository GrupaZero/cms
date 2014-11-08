<?php namespace Gzero\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Mapping;
use Gzero\Entity\BaseUser;
use Gzero\Entity\Block;
use Gzero\Entity\User;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
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
     * Get single block with active translations
     *
     * @param int $id
     *
     * @return Block
     */
    public function getById($id)
    {
        return $this->find($id);
    }

    public function create(array $data)
    {
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setPassword($data['password']);
        $this->_em->persist($user);
    }

    public function update(BaseUser $user, array $data)
    {
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setPassword($data['password']);
        $this->_em->persist($user);
    }

    public function delete(BaseUser $user)
    {
        $this->_em->remove($user);
    }

    public function commit()
    {
        $this->_em->flush();
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
