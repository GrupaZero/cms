<?php namespace functional;

use Faker\Factory;
use Gzero\Entity\Content;
use Gzero\Entity\User;
use Gzero\Repository\ContentRepository;
use Illuminate\Events\Dispatcher;

require_once(__DIR__ . '/../stub/TestSeeder.php');
require_once(__DIR__ . '/../stub/TestTreeSeeder.php');

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
class ContentRepositoryTest extends \EloquentTestCase {

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
                'type'             => 'content',
                'isOnHome'         => true,
                'isCommentAllowed' => true,
                'isPromoted'       => true,
                'isSticky'         => true,
                'isActive'         => true,
                'publishedAt'      => date('Y-m-d H:i:s'),
                'translations'     => [
                    'langCode' => 'en',
                    'title'    => 'Example title'
                ]
            ],
            $author
        );

        $newContent       = $this->repository->getById($content->id);
        $newContentRoute  = $newContent->route->translations()->first();
        $newContentAuthor = $newContent->author;
        // content
        $this->assertNotSame($content, $newContent);
        $this->assertEquals($content->id, $newContent->id);
        $this->assertEquals($content->type, $newContent->type);
        $this->assertEquals($content->isOnHome, $newContent->isOnHome);
        $this->assertEquals($content->isCommentAllowed, $newContent->isCommentAllowed);
        $this->assertEquals($content->isPromoted, $newContent->isPromoted);
        $this->assertEquals($content->isSticky, $newContent->isSticky);
        $this->assertEquals($content->isActive, $newContent->isActive);
        $this->assertEquals($content->publishedAt, $newContent->publishedAt);
        // author
        $this->assertEquals($author->id, $newContent->authorId);
        $this->assertEquals($author->email, $newContentAuthor['email']);
        // route
        $this->assertEquals('en', $newContentRoute['langCode']);
        $this->assertEquals('example-title', $newContentRoute['url']);
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

    /**
     * @test
     */
    public function can_create_and_get_content_translation()
    {
        $content          = $this->repository->create(
            [
                'type'         => 'content',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example title'
                ]
            ]
        );
        $newContent       = $this->repository->getById($content->id);
        $translation      = $this->repository->createTranslation(
            $newContent,
            [
                'langCode' => 'en',
                'title'    => 'New example title',
                'body'     => 'New example body'
            ]
        );
        $firstTranslation = $this->repository->getContentTranslationById($newContent, 1);
        $newTranslation   = $this->repository->getContentTranslationById($newContent, 2);
        $this->assertNotSame($content, $newContent);
        $this->assertNotSame($translation, $firstTranslation);
        // previous translation is inactive
        $this->assertFalse((bool) $firstTranslation->isActive);
        // new translation
        $this->assertEquals('en', $newTranslation->langCode);
        $this->assertEquals('New example title', $newTranslation->title);
        $this->assertEquals('New example body', $newTranslation->body);
        $this->assertEquals($newContent->id, $newTranslation->contentId);
    }

    /**
     * @test
     */
    public function can_delete_content()
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
        $this->repository->delete($newContent);
        // content deleted
        $this->assertNull($this->repository->getById($newContent->id));
        // route deleted
        $this->assertNull($newContent->route()->first());
        // content translations deleted
        $this->assertNull($newContent->translations()->first());
    }

    /**
     * @test
     */
    public function can_delete_content_translation()
    {
        $content    = $this->repository->create(
            [
                'type'         => 'content',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example title',
                    'body'     => 'Example body'
                ]
            ]
        );
        $newContent = $this->repository->getById($content->id);
        $this->assertNotSame($content, $newContent);
        $this->repository->deleteTranslation($newContent->translations()->first());
        // content translations deleted
        $this->assertNull($newContent->translations()->first());

    }


    /**
     * @test
     * @expectedException Gzero\Core\Exception
     */
    public function it_checks_existence_of_parent_route_translation()
    {
        $category    = $this->repository->create(
            [
                'type'         => 'category',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example category title'
                ]
            ]
        );
        $newCategory = $this->repository->getById($category->id);
        $this->repository->create(
            [
                'type'         => 'content',
                'parentId'     => $newCategory->id,
                'translations' => [
                    'langCode' => 'pl',
                    'title'    => 'Example content title'
                ]
            ]
        );
    }

    /*
  |--------------------------------------------------------------------------
  | START Tree tests
  |--------------------------------------------------------------------------
  */

    /**
     * @test
     */
    public function can_create_content_as_child()
    {
        // Tree seeds
        $this->app['artisan']->call('db:seed', ['--class' => 'TestTreeSeeder']);

        $category        = $this->repository->getById(1);
        $categoryRoute   = $category->route->translations()->first();
        $content         = $this->repository->create(
            [
                'type'         => 'content',
                'parentId'     => $category->id,
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example content title'
                ]
            ]
        );
        $newContent      = $this->repository->getById($content->id);
        $newContentRoute = $newContent->route->translations()->first();
        // parentId
        $this->assertEquals($category->id, $newContent->parentId);
        // level
        $this->assertEquals($category->level + 1, $newContent->level);
        // path
        $this->assertEquals($category->path . $newContent->id . '/', $newContent->path);
        // route
        $this->assertEquals('en', $newContentRoute['langCode']);
        $this->assertEquals($categoryRoute->url . '/' . 'example-content-title', $newContentRoute['url']);
    }

    /**
     * @test
     */
    public function can_delete_content_with_children()
    {
        // Tree seeds
        $this->app['artisan']->call('db:seed', ['--class' => 'TestTreeSeeder']);

        $content = $this->repository->getById(1);
        $this->repository->delete($content);
        // content children deleted
        $this->assertEmpty($content->children()->get());
    }

    /*
    |--------------------------------------------------------------------------
    | END Tree tests
    |--------------------------------------------------------------------------
    */
}
