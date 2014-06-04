<?php namespace Gzero\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Gzero\Doctrine2Extensions\Common\BaseRepository;
use Gzero\Entity\Block;
use Gzero\Entity\Lang;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;

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
     * Gets all active blocks with translation in specified lang
     *
     * @param Lang $lang Lang model
     *
     * @return mixed
     */
    public function getAllActive(Lang $lang)
    {
        /* @var QueryBuilder $qb */
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
            ->from($this->getClassName(), 'b')
            ->leftJoin('b.translations', 't', 'WITH', 't.lang = :lang')
            ->where('b.isActive = 1')
            ->where('b.regions IS NOT NULL')
            ->orderBy('b.weight')
            ->setParameter('lang', $lang->getCode());
        return $qb->getQuery()->getResult();
    }

    public function create(Block $block)
    {
        $this->_em->persist($block);
    }

    public function update(Block $block)
    {

    }

    public function delete(Block $block)
    {

    }

    public function save()
    {
        $this->_em->flush();
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveById($identifier)
    {
        return $this->find($identifier);
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string $token
     *
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveByToken($identifier, $token)
    {
        // TODO: Implement retrieveByToken() method.
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
        // TODO: Implement updateRememberToken() method.
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
        $list = $this->findBy($credentials);
        return (!empty($list[0])) ? $list[0] : NULL;
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
        return TRUE;
    }
}
