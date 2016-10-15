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
class UserRepositoryTest extends \TestCase {

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
    }

    public function _after()
    {
        // Stop the Laravel application
        $this->stopApplication();
    }

    /**
     * @test
     */
    public function can_create_user_and_get_it_by_id()
    {
        $userData = [
            'email'     => 'john.doe@example.com',
            'password'  => 'secret',
            'nickName'  => 'Nickname',
            'firstName' => 'John',
            'lastName'  => 'Doe',
        ];

        $user       = $this->repository->create($userData);
        $userFromDb = $this->repository->getById($user->id);

        $this->assertEquals(
            [
                $user->email,
                $user->id,
                $user->nickName,
                $user->firstName,
                $user->lastName
            ],
            [
                $userFromDb->email,
                $userFromDb->id,
                $userFromDb->nickName,
                $userFromDb->firstName,
                $userFromDb->lastName
            ]
        );
    }

    /**
     * @test
     */
    public function can_create_user_with_empty_nickname_as_anonymous()
    {

        $firstUserData = [
            'email'     => 'john.doe@example.com',
            'password'  => 'secret',
            'nickName'  => '',
            'firstName' => 'John',
            'lastName'  => 'Doe',
        ];

        $secondUserData = [
            'email'     => 'jane.doe@example.com',
            'password'  => 'secret',
            'nickName'  => '',
            'firstName' => 'Jane',
            'lastName'  => 'Doe',
        ];

        $firstUser  = $this->repository->create($firstUserData);
        $secondUser = $this->repository->create($secondUserData);

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
    public function it_hashes_user_password_when_updating_user()
    {
        $user = $this->repository->create(
            [
                'email'     => 'john.doe@example.com',
                'password'  => 'password',
                'firstName' => 'John',
                'lastName'  => 'Doe',
            ]
        );

        $this->repository->update($user, ['password' => 'secret']);

        $this->assertTrue(Hash::check('secret', $user->password));
    }

    /**
     * @test
     */
    public function can_delete_user()
    {
        $userData = [
            'email'     => 'john.doe@example.com',
            'password'  => 'secret',
            'nickName'  => 'Nickname',
            'firstName' => 'John',
            'lastName'  => 'Doe',
        ];

        $user       = $this->repository->create($userData);
        $userFromDb = $this->repository->getById($user->id);

        $this->assertNotNull($userFromDb);
        $this->tester->seeInDatabase('Users', $userData);

        $this->repository->delete($user);

        $userFromDb = $this->repository->getById($user->id);

        $this->assertNull($userFromDb);
    }

    /**
     * @test
     */
    public function can_sort_users_list()
    {

        $firstUser = $this->repository->create(
            [
                'email'     => 'john.doe@example.com',
                'password'  => 'secret',
                'firstName' => 'John',
                'lastName'  => 'Doe'
            ]
        );

        $secondUser = $this->repository->create(
            [
                'email'     => 'zoe.doe@example.com',
                'password'  => 'secret',
                'firstName' => 'Zoe',
                'lastName'  => 'Doe'
            ]
        );

        // ASC
        $result = $this->repository->getUsers([], [['email', 'ASC']], null);

        $this->assertEquals($result[0]->email, 'admin@gzero.pl');
        $this->assertEquals($result[1]->email, $firstUser->email);
        $this->assertEquals($result[2]->email, $secondUser->email);

        // DESC
        $result = $this->repository->getUsers([], [['email', 'DESC']], null);

        $this->assertEquals($result[0]->email, $secondUser->email);
        $this->assertEquals($result[1]->email, $firstUser->email);
        $this->assertEquals($result[2]->email, 'admin@gzero.pl');
    }
}

