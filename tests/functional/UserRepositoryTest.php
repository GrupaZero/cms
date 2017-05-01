<?php namespace functional;

use Gzero\Entity\User;
use Gzero\Repository\UserRepository;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Hash;

require_once(__DIR__ . '/../stub/TestSeeder.php');
require_once(__DIR__ . '/../stub/TestTreeSeeder.php');

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
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            'nick'       => 'Nickname',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ];

        $user       = $this->repository->create($userData);
        $userFromDb = $this->repository->getById($user->id);

        $this->assertEquals(
            [
                $user->email,
                $user->id,
                $user->nick,
                $user->first_name,
                $user->last_name
            ],
            [
                $userFromDb->email,
                $userFromDb->id,
                $userFromDb->nick,
                $userFromDb->first_name,
                $userFromDb->last_name
            ]
        );
    }

    /**
     * @test
     */
    public function can_create_user_with_empty_nickname_as_anonymous()
    {
        $data1 = [
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            'nick'       => '',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ];
        $data2 = [
            'email'      => 'jane.doe@example.com',
            'password'   => 'secret',
            'nick'       => '',
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ];
        $data3 = [
            'email'      => 'jane.doe2@example.com',
            'password'   => 'secret',
            'nick'       => '',
            'first_name' => 'Jane',
            'last_name'  => 'Doe2',
        ];

        $user1 = $this->repository->create($data1);
        $user2 = $this->repository->create($data2);

        $user1Db = $this->repository->getById($user1->id);
        $user2Db = $this->repository->getById($user2->id);

        $this->assertEquals(
            [
                $user1->email,
                $user1->id,
                $user1->first_name,
                $user1->last_name
            ],
            [
                $user1Db->email,
                $user1Db->id,
                $user1Db->first_name,
                $user1Db->last_name
            ]
        );

        $this->assertEquals(
            [
                $user2->email,
                $user2->id,
                $user2->first_name,
                $user2->last_name
            ],
            [
                $user2Db->email,
                $user2Db->id,
                $user2Db->first_name,
                $user2Db->last_name
            ]
        );

        $this->assertRegExp('/^anonymous\-[a-z 0-9]{13}/', $user1Db->nick);
        $this->assertRegExp('/^anonymous\-[a-z 0-9]{13}/', $user2Db->nick);

        // Deleting user1 to make sure that we still return unique nick
        $this->repository->delete($user1);

        $user3   = $this->repository->create($data3);
        $user3Db = $this->repository->getById($user3->id);

        $this->assertEquals(
            [
                $user3->email,
                $user3->id,
                $user3->first_name,
                $user3->last_name
            ],
            [
                $user3Db->email,
                $user3Db->id,
                $user3Db->first_name,
                $user3Db->last_name
            ]
        );

        $this->assertRegExp('/^anonymous\-[a-z 0-9]{13}/', $user3Db->nick);
        $this->assertCount(3, array_unique([$user1Db->nick, $user2Db->nick, $user3Db->nick]));
    }

    /**
     * @test
     */
    public function it_hashes_user_password_when_updating_user()
    {
        $user = $this->repository->create(
            [
                'email'      => 'john.doe@example.com',
                'password'   => 'password',
                'first_name' => 'John',
                'last_name'  => 'Doe',
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
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            'nick'       => 'Nickname',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ];

        $user       = $this->repository->create($userData);
        $userFromDb = $this->repository->getById($user->id);

        $this->assertNotNull($userFromDb);
        $this->tester->seeInDatabase('users', $userData);

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
                'email'      => 'john.doe@example.com',
                'password'   => 'secret',
                'first_name' => 'John',
                'last_name'  => 'Doe'
            ]
        );

        $secondUser = $this->repository->create(
            [
                'email'      => 'zoe.doe@example.com',
                'password'   => 'secret',
                'first_name' => 'Zoe',
                'last_name'  => 'Doe'
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

