<?php namespace functional;

use Gzero\Entity\Content;
use Gzero\Entity\User;
use Gzero\Repository\ContentRepository;
use Illuminate\Events\Dispatcher;

require_once(__DIR__ . '/../TestCase.php');
require_once(__DIR__ . '/../stub/TestSeeder.php');

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ContentRepositoryTest
 *
 * @package    functional
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class ContentRepositoryTest extends \TestCase {

    /**
     * @var ContentRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = new ContentRepository(new Content(), new Dispatcher());
        $this->app['artisan']->call('db:seed', ['--class' => 'TestSeeder']); // Relative to tests/app/
    }

    /**
     * @test
     */
    public function can_create_content()
    {
        $author  = User::find(1);
        $content = $this->repository->create(
            [
                'type'         => 'content',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example title'
                ]
            ],
            $author
        );

        $newContent       = $this->repository->getById($content->id);
        $newContentRoute  = $newContent->route->translations;
        $newContentAuthor = $newContent->author;
        // content
        $this->assertNotSame($content, $newContent);
        $this->assertEquals($content->id, $newContent->id);
        $this->assertEquals($content->type, $newContent->type);
        // author
        $this->assertEquals($author->id, $newContent->authorId);
        $this->assertEquals($author->email, $newContentAuthor['email']);
        // route
        $this->assertEquals('en', $newContentRoute[0]['langCode']);
        $this->assertEquals('example-title', $newContentRoute[0]['url']);
    }

    /**
     * @test
     */
    public function can_create_content_without_author()
    {
        $content    = $this->repository->create(
            [
                'type'         => 'content',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example title'
                ]
            ]
        );
        $newContent = $this->repository->getById($content->id);
        $this->assertNotSame($content, $newContent);
        $this->assertNull($newContent->author);
    }
}
