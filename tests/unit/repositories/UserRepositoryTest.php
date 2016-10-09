<?php namespace functional;

use Gzero\Entity\User;
use Gzero\Repository\UserRepository;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Hash;

require_once(__DIR__ . '/../../stub/TestSeeder.php');
require_once(__DIR__ . '/../../stub/TestTreeSeeder.php');

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class UserRepositoryTest
 *
 * @author     Mateusz Urbanowicz <urbanowiczmateusz89@gmail.com>
 * @copyright  Copyright (c) 2015, Mateusz Urbanowicz
 */
class UserRepositoryTest extends \TestCase  {

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var UserRepository
     */
    protected $repository;

    protected function _before()
    {
        // Start the Laravel application
        $this->startApplication();
        $this->repository = new UserRepository(new User(), new Dispatcher());
        $this->seed('TestSeeder'); // Relative to tests/app/
    }

    public function _after()
    {
        // Stop the Laravel application
        $this->stopApplication();
    }

    /**
     * @test
     */
    public function can_create_user_and_get_user_by_id()
    {
        $user = $this->repository->create(
            [
                'email'     => 'test_user@user.com',
                'password'  => 'test',
                'nickName'  => 'Nickname',
                'firstName' => 'John',
                'lastName'  => 'Doe',
            ]
        );

        $created = $this->repository->getById($user->id);

        $this->assertEquals(
            [
                $user->email,
                $user->id,
                $user->nickName,
                $user->firstName,
                $user->lastName
            ],
            [
                $created->email,
                $created->id,
                $created->nickName,
                $created->firstName,
                $created->lastName
            ]
        );
    }

    /**
     * @test
     */
    public function can_create_user_with_empty_nickname_as_anonymous()
    {
        $firstUser = $this->repository->create(
            [
                'email'     => 'first_user@user.com',
                'password'  => 'test',
                'nickName'  => '',
                'firstName' => 'John',
                'lastName'  => 'Doe',
            ]
        );

        $secondUser = $this->repository->create(
            [
                'email'     => 'second_user@user.com',
                'password'  => 'test',
                'nickName'  => '',
                'firstName' => 'John',
                'lastName'  => 'Doe',
            ]
        );

        $firstUserFromDb  = $this->repository->getById($firstUser->id);
        $secondUserFromDb = $this->repository->getById($secondUser->id);

        $this->assertEquals(
            [
                $firstUser->email,
                $firstUser->id,
                'anonymous',
                $firstUser->firstName,
                $firstUser->lastName
            ],
            [
                $firstUserFromDb->email,
                $firstUserFromDb->id,
                $firstUserFromDb->nickName,
                $firstUserFromDb->firstName,
                $firstUserFromDb->lastName
            ]
        );

        $this->assertEquals(
            [
                $secondUser->email,
                $secondUser->id,
                'anonymous-1',
                $secondUser->firstName,
                $secondUser->lastName
            ],
            [
                $secondUserFromDb->email,
                $secondUserFromDb->id,
                $secondUserFromDb->nickName,
                $secondUserFromDb->firstName,
                $secondUserFromDb->lastName
            ]
        );
    }

    /**
     * @test
     */
    public function password_is_hashed_after_user_update()
    {
        $user = $this->repository->create(
            [
                'email'     => 'test_user@user.com',
                'password'  => 'password',
                'firstName' => 'John',
                'lastName'  => 'Doe',
            ]
        );

        $this->repository->update($user, ['password' => 'test']);

        $this->assertTrue(Hash::check('test', $user->password));
    }

    /**
     * @test
     */
    public function can_delete_user()
    {
        $user = $this->repository->create(
            [
                'email'     => 'delete@user.com',
                'password'  => 'password',
                'firstName' => 'John',
                'lastName'  => 'Doe',
            ]
        );

        $user_id = $user->id;

        $found = $this->repository->getById($user_id);

        $this->assertNotNull($found);

        $this->repository->delete($user);

        $found = $this->repository->getById($user_id);

        $this->assertNull($found);
    }

    /**
     * @test
     */
    public function can_sort_users_list()
    {

        $user = $this->repository->create(
            [
                'email'     => 'alpha@user.com',
                'password'  => 'password',
                'firstName' => 'John',
                'lastName'  => 'Doe'
            ]
        );

        $user1 = $this->repository->create(
            [
                'email'     => 'beta@user.com',
                'password'  => 'password',
                'firstName' => 'Steve',
                'lastName'  => 'Doe'
            ]
        );

        // ASC
        $result = $this->repository->getUsers([], [['email', 'ASC']], null);

        $this->assertEquals($result[0]->email, 'admin@gzero.pl');
        $this->assertEquals($result[1]->email, $user->email);
        $this->assertEquals($result[2]->email, $user1->email);

        // DESC
        $result = $this->repository->getUsers([], [['email', 'DESC']], null);

        $this->assertEquals($result[0]->email, $user1->email);
        $this->assertEquals($result[1]->email, $user->email);
        $this->assertEquals($result[2]->email, 'admin@gzero.pl');
    }
}

