<?php namespace Cms;

use Cms\UnitTester;
use Codeception\Test\Unit;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Services\ContentService;
use Gzero\Cms\Services\FileService;
use Gzero\Core\Models\User;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Storage;

require_once(__DIR__ . '/../../stub/TestSeeder.php');
require_once(__DIR__ . '/../../stub/TestTreeSeeder.php');

class ContentServiceTest extends Unit {

    /** @var \Cms\UnitTester */
    protected $tester;

    /**
     * @var ContentService
     */
    protected $repository;

    /**
     * @var FileService
     */
    protected $fileRepository;

    /**
     * files directory
     */
    protected $filesDir;

    public function _before()
    {
        // Start the Laravel application
        $this->repository = new ContentService(new Content(), new Dispatcher());
        $this->filesDir   = __DIR__ . '/../../resources';
    }

    public function _after()
    {
        $dirName = config('gzero.upload.directory');
        if ($dirName) {
            Storage::deleteDirectory($dirName);
        }
        // Stop the Laravel application
    }

    /*
    |--------------------------------------------------------------------------
    | START Content tests
    |--------------------------------------------------------------------------
    */

    /**
     * @test
     */
    public function canCreateContentWithSameTitleAsOneOfSoftDeletedContents()
    {
        $this->markTestSkipped('FIX IT after refactor');

        (new \TestSeeder())->run();
        $content1 = $this->repository->create(
            [
                'type'         => 'content',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                    'body'          => 'Example body'
                ]
            ]
        );

        $contentId1 = $content1->id;

        $this->repository->delete($content1);

        $content2 = $this->repository->create(
            [
                'type'         => 'content',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                    'body'          => 'Example body'
                ]
            ]
        );

        $content1 = $this->repository->getDeletedById($contentId1);
        $content1->restore();

        $this->assertEquals($content1->title, $content2->title);
        $this->assertNotEquals($content1->getUrl('en'), $content2->getUrl('en'));
    }

    /*
    |--------------------------------------------------------------------------
    | END Content tests
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | START Tree tests
    |--------------------------------------------------------------------------
    */

    /**
     * @test
     */
    public function canGetRoots()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Tree seeds
        (new \TestSeeder())->run();
        //(new \TestTreeSeeder())->run();

        $roots = $this->repository->getRoots(
            [],
            [],
            null
        );
        foreach ($roots as $node) {
            $this->assertNull($node->parent_id);
            $this->assertEquals(0, $node->level);
        }
    }

    /**
     * @test
     */
    public function canGetTree()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Tree seeds
        (new \TestSeeder())->run();
        //(new \TestTreeSeeder())->run();

        $category = $this->repository->getById(1);
        $tree     = $this->repository->getTree(
            $category,
            [],
            [],
            null
        );

        // First level
        foreach ($tree['children'] as $node) {
            $this->assertEquals($category->id, $node->parent_id);
            // nested level
            if (array_key_exists('children', $node)) {
                foreach ($node['children'] as $subnode) {
                    $this->assertEquals($node->id, $subnode->parent_id);
                }
            }
        }
    }

    /**
     * @test
     */
    public function canCreateRoute()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Tree seeds
        //(new \TestTreeSeeder())->run();

        // Single content
        $singleContent = $this->repository->getById(2);

        // Crate single route
        $this->repository->createRoute($singleContent, 'en', 'Single content url');
        $updatedContent      = $this->repository->getById($singleContent->id);
        $updatedContentRoute = $updatedContent->routes->first();

        // Check single route
        $this->assertEquals('en', $updatedContentRoute['language_code']);
        $this->assertEquals('single-content-url', $updatedContentRoute['url']);

        // Nested content
        $category      = $this->repository->getById(1);
        $categoryRoute = $category->routes->first();
        $nestedContent = $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $category->id,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example content title'
                ]
            ]
        );

        // Crate nested route
        $newContent = $this->repository->getById($nestedContent->id);
        $this->repository->createRoute($newContent, 'en', 'Nested content url');
        $updatedContent      = $this->repository->getById($nestedContent->id);
        $updatedContentRoute = $updatedContent->routes->first();

        // Check nested route
        $this->assertEquals('en', $updatedContentRoute['language_code']);
        $this->assertEquals($categoryRoute->url . '/' . 'nested-content-url', $updatedContentRoute['url']);

        // Crate unique route
        $this->repository->createRoute($newContent, 'en', 'Nested content url');
        $updatedContent      = $this->repository->getById($nestedContent->id);
        $updatedContentRoute = $updatedContent->routes->first();

        // Check unique route
        $this->assertEquals('en', $updatedContentRoute['language_code']);
        $this->assertEquals($categoryRoute->url . '/' . 'nested-content-url-1', $updatedContentRoute['url']);
    }

    /** @test */
    public function canGetListOfDeletedContents()
    {
        $this->markTestSkipped('FIX IT after refactor');

        $category = $this->repository->create(
            [
                'type'         => 'category',
                'weight'       => 3,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'A title'
                ]
            ]
        );

        $content1 = $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $category->id,
                'weight'       => 2,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'A title'
                ]
            ]
        );

        $content2 = $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $category->id,
                'weight'       => 0,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'B title'
                ]
            ]
        );

        $contents = $this->repository->getContents([], [['weight', 'ASC']], null, null);

        $this->assertEquals(3, $contents->count());

        $this->assertEquals($contents[0]->weight, 0);
        $this->assertEquals($contents[0]->level, 1);
        $this->assertEquals($contents[1]->weight, 2);
        $this->assertEquals($contents[1]->level, 1);
        $this->assertEquals($contents[2]->weight, 3);
        $this->assertEquals($contents[2]->level, 0);

        $this->repository->delete($content1);
        $this->repository->delete($content2);
        $this->repository->delete($category);

        $contentsAfterDelete = $this->repository->getContents([], [['weight', 'ASC']], null, null);
        $deletedContents     = $this->repository->getDeletedContents([], [['weight', 'ASC']], null, null);

        $this->assertEquals(0, $contentsAfterDelete->count());
        $this->assertEquals(3, $deletedContents->count());

        $this->assertEquals($deletedContents[0]->weight, 0);
        $this->assertEquals($deletedContents[0]->level, 1);
        $this->assertEquals($deletedContents[1]->weight, 2);
        $this->assertEquals($deletedContents[1]->level, 1);
        $this->assertEquals($deletedContents[2]->weight, 3);
        $this->assertEquals($deletedContents[2]->level, 0);

    }

    /**
     * @test
     */
    public function canGetListOfDeletedContentsTree()
    {
        $this->markTestSkipped('FIX IT after refactor');

        $category = $this->repository->create(
            [
                'type'         => 'category',
                'weight'       => 3,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'A title'
                ]
            ]
        );

        $content1 = $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $category->id,
                'weight'       => 2,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'A title'
                ]
            ]
        );

        $content2 = $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $category->id,
                'weight'       => 0,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'B title'
                ]
            ]
        );

        $contents = $this->repository->getContentsByLevel([], [['weight', 'ASC']], null, null);

        $this->assertEquals(3, $contents->count());

        $this->assertEquals($contents[0]->weight, 3);
        $this->assertEquals($contents[0]->level, 0);
        $this->assertEquals($contents[1]->weight, 0);
        $this->assertEquals($contents[1]->level, 1);
        $this->assertEquals($contents[2]->weight, 2);
        $this->assertEquals($contents[2]->level, 1);

        $this->repository->delete($content1);
        $this->repository->delete($content2);
        $this->repository->delete($category);

        $contentsAfterDelete = $this->repository->getContentsByLevel([], [['weight', 'ASC']], null, null);
        $deletedContents     = $this->repository->getDeletedContentsByLevel([], [['weight', 'ASC']], null, null);

        $this->assertEquals(0, $contentsAfterDelete->count());
        $this->assertEquals(3, $deletedContents->count());

        $this->assertEquals($deletedContents[0]->weight, 3);
        $this->assertEquals($deletedContents[0]->level, 0);
        $this->assertEquals($deletedContents[1]->weight, 0);
        $this->assertEquals($deletedContents[1]->level, 1);
        $this->assertEquals($deletedContents[2]->weight, 2);
        $this->assertEquals($deletedContents[2]->level, 1);

    }

    /*
    |--------------------------------------------------------------------------
    | END Tree tests
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | START List tests
    |--------------------------------------------------------------------------
    */

    /**
     * @test
     */
    public function canGetContentChildrenList()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Tree seeds
        //(new \TestTreeSeeder())->run();

        $category = $this->repository->getById(1);

        $contents = $this->repository->getChildren(
            $category,
            [],
            [],
            null
        );

        // parent_id
        foreach ($contents as $content) {
            $this->assertEquals($category->id, $content->parent_id);
        }
    }

    /**
     * @test
     */
    public function canGetContentTranslationsList()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Tree seeds
        //(new \TestTreeSeeder())->run();

        $category = $this->repository->getById(1);
        // New translations
        for ($i = 0; $i < 3; $i++) {
            $this->repository->createTranslation(
                $category,
                [
                    'language_code' => 'pl',
                    'title'         => 'New example title',
                    'body'          => 'New example body'
                ]
            );
        }
        $contents = $this->repository->getTranslations(
            $category,
            [],
            [],
            null
        );
        // Number of new translations plus one for first translation
        $this->assertCount($i + 1, $contents);
        foreach ($contents as $content) {
            // parent_id
            $this->assertEquals($category->id, $content->content_id);
        }
    }

    /**
     * @test
     */
    public function canFilterContentsList()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Tree seeds
        //(new \TestTreeSeeder())->run();

        $contents = $this->repository->getContents(
            [
                ['type', '=', 'category'],
                ['is_active', '=', true]
            ],
            [],
            null
        );

        foreach ($contents as $content) {
            $this->assertEquals('category', $content->type);
            $this->assertEquals(true, $content->is_active);
        }

        $contents = $this->repository->getContentsByLevel(
            [
                ['type', '=', 'category'],
                ['is_active', '=', true]
            ],
            [],
            null
        );

        foreach ($contents as $content) {
            $this->assertEquals('category', $content->type);
            $this->assertEquals(true, $content->is_active);
        }
    }

    /**
     * @test
     */
    public function canSortContentsList()
    {
        $this->markTestSkipped('FIX IT after refactor');

        $category = $this->repository->create(
            [
                'type'         => 'category',
                'weight'       => 10,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'C title'
                ]
            ]
        );
        $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $category->id,
                'weight'       => 0,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'A title'
                ]
            ]
        );
        $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $category->id,
                'weight'       => 1,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'B title'
                ]
            ]
        );

        // Ascending
        $contents = $this->repository->getContents(
            [
                ['translations.lang', '=', 'en']
            ],
            [
                ['weight', 'ASC'],
                ['translations.title', 'ASC'],
            ],
            null
        );

        $this->assertEquals(3, $contents->count());
        $this->assertEquals(0, $contents[0]->weight);
        $this->assertEquals(1, $contents[1]->weight);
        $this->assertEquals(10, $contents[2]->weight);
        // Translations title
        $this->assertEquals('A title', $contents[0]->translations[0]->title);
        $this->assertEquals('B title', $contents[1]->translations[0]->title);
        $this->assertEquals('C title', $contents[2]->translations[0]->title);

        // Descending
        $contents = $this->repository->getContents(
            [
                ['translations.lang', '=', 'en']
            ],
            [
                ['weight', 'DESC'],
                ['translations.title', 'DESC'],
            ],
            null
        );

        $this->assertEquals(3, $contents->count());
        $this->assertEquals(10, $contents[0]->weight);
        $this->assertEquals(1, $contents[1]->weight);
        $this->assertEquals(0, $contents[2]->weight);

        $this->assertEquals('C title', $contents[0]->translations[0]->title);
        $this->assertEquals('B title', $contents[1]->translations[0]->title);
        $this->assertEquals('A title', $contents[2]->translations[0]->title);

        // Ascending
        $contents = $this->repository->getContentsByLevel(
            [
                ['translations.lang', '=', 'en']
            ],
            [
                ['weight', 'ASC'],
                ['translations.title', 'ASC'],
            ],
            null
        );

        $this->assertEquals(3, $contents->count());
        $this->assertEquals(10, $contents[0]->weight);
        $this->assertEquals(0, $contents[0]->level);
        $this->assertEquals(0, $contents[1]->weight);
        $this->assertEquals(1, $contents[1]->level);
        $this->assertEquals(1, $contents[2]->weight);
        $this->assertEquals(1, $contents[1]->level);

        $this->assertEquals('C title', $contents[0]->translations[0]->title);
        $this->assertEquals('A title', $contents[1]->translations[0]->title);
        $this->assertEquals('B title', $contents[2]->translations[0]->title);

        // Descending
        $contents = $this->repository->getContentsByLevel(
            [
                ['translations.lang', '=', 'en']
            ],
            [
                ['weight', 'DESC'],
                ['translations.title', 'DESC'],
            ],
            null
        );

        $this->assertEquals(3, $contents->count());
        $this->assertEquals(10, $contents[0]->weight);
        $this->assertEquals(0, $contents[0]->level);
        $this->assertEquals(1, $contents[1]->weight);
        $this->assertEquals(1, $contents[1]->level);
        $this->assertEquals(0, $contents[2]->weight);
        $this->assertEquals(1, $contents[2]->level);

        $this->assertEquals('C title', $contents[0]->translations[0]->title);
        $this->assertEquals('B title', $contents[1]->translations[0]->title);
        $this->assertEquals('A title', $contents[2]->translations[0]->title);

    }

    /*
    |--------------------------------------------------------------------------
    | END Lists tests
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | START Translations tests
    |--------------------------------------------------------------------------
    */

    /**
     * @test                     Change tree seeder to seeder
     * @expectedExceptionMessage Error: 'lang' criteria is required
     */
    public function itChecksExistenceOfLanguageCodeOnTranslationsJoin()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Tree seeds
        (new \TestSeeder())->run();

        $this->repository->getContents([], [['translations.title', 'DESC']], null);
    }

    /**
     * @test
     * @expectedExceptionMessage Error: 'lang' criteria is required
     */
    public function itChecksExistenceOfLanguageCodeOnTranslationsJoinTree()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Tree seeds
        //(new \TestTreeSeeder())->run();

        $this->repository->getContentsByLevel(
            [],
            [['translations.title', 'DESC']],
            null
        );
    }

    /**
     * @test
     */
    public function itDoesNotCheckExistenceOfLanguageCodeForCoreOrderByParams()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Tree seeds
        //(new \TestTreeSeeder())->run();

        $nodes = $this->repository->getContents(
            [],
            [['weight', 'DESC']],
            null
        );
        $this->assertNotEmpty($nodes);

        $nodes = $this->repository->getContentsByLevel(
            [],
            [['weight', 'DESC']],
            null
        );
        $this->assertNotEmpty($nodes);
    }

    /**
     * @test
     */
    public function canGetAncestor()
    {
        $this->markTestSkipped('FIX IT after refactor');

        $category1 = $this->repository->create(
            [
                'type'         => 'category',
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'Example content title'
                ]
            ]
        );

        $category2 = $this->repository->create(
            [
                'type'         => 'category',
                'parent_id'    => $category1->id,
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'Example content title'
                ]
            ]
        );

        $category3 = $this->repository->create(
            [
                'type'         => 'category',
                'parent_id'    => $category2->id,
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'Example content title'
                ]
            ]
        );

        $parents = $this->repository->getAncestors($category3, []);

        $this->assertEquals($parents[0]->id, $category1->id);
        $this->assertEquals($parents[1]->id, $category2->id);

    }

    /**
     * @test
     */
    public function itDoesNotDuplicateContentWhenTranslationAdded()
    {
        $this->markTestSkipped('FIX IT after refactor');

        $author  = User::find(1);
        $content = $this->repository->create(
            [
                'type'               => 'content',
                'is_on_home'         => false,
                'is_comment_allowed' => false,
                'is_promoted'        => false,
                'is_sticky'          => false,
                'is_active'          => true,
                'published_at'       => date('Y-m-d H:i:s'),
                'translations'       => [
                    'language_code' => 'en',
                    'title'         => 'English translation 1'
                ]
            ],
            $author
        );
        $this->assertInstanceOf('Gzero\Cms\Models\Content', $content);

        $translation = $this->repository->createTranslation(
            $content,
            [
                'language_code' => 'en',
                'title'         => 'English translation 2'
            ]
        );
        $this->assertInstanceOf('Gzero\Cms\Models\ContentTranslation', $translation);

        $translatedContent = $this->repository->getContents(
            [
                ['lang', '=', 'en'],
                ['type', '=', 'content']
            ],
            [],
            1,
            20
        );
        $this->assertEquals(1, $translatedContent->count());

        $translatedContent = $this->repository->getContentsByLevel(
            [
                ['lang', '=', 'en'],
                ['type', '=', 'content']
            ],
            [],
            1,
            20
        );
        $this->assertEquals(1, $translatedContent->count());

    }

    /**
     * @test
     */
    public function itCreatesNewRouteOnlyForNewContent()
    {
        $this->markTestSkipped('FIX IT after refactor');

        // Create new content with first translation
        $content = $this->repository->create(
            [
                'type'         => 'content',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                ]
            ]
        );

        // Add new content translation
        $this->repository->createTranslation(
            $content,
            [
                'language_code' => 'en',
                'title'         => 'Modified example title',
            ]
        );

        $newContent      = $this->repository->getById($content->id);
        $newContentRoute = $newContent->routes->first();
        // Route translation should not be changed
        $this->assertEquals('en', $newContentRoute['language_code']);
        $this->assertEquals('example-title', $newContentRoute['url']);
    }

    /**
     * @test
     */
    public function itReturnsTitlesTranslationBasedOnUrlAndLang()
    {
        $this->markTestSkipped('FIX IT after refactor');

        $category1 = $this->repository->create(
            [
                'type'         => 'category',
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'Przykładowy tytuł kategorii 1.'
                ]
            ]
        );

        // It should not be in $titles array.
        $this->repository->createTranslation(
            $category1,
            [
                'language_code' => 'en',
                'title'         => 'Example title category 1.'
            ]
        );

        // It should not be in $titles array.
        $category2 = $this->repository->create(
            [
                'type'         => 'category',
                'parent_id'    => $category1->id,
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'Przykładowy, nieaktywny, zagnieżdżony tytuł kategorii 2.'
                ]
            ]
        );

        // It should not be in $titles array.
        $this->repository->createTranslation(
            $category2,
            [
                'language_code' => 'en',
                'title'         => 'Example, active, nested title category 2.'
            ]
        );

        $this->repository->createTranslation(
            $category2,
            [
                'language_code' => 'pl',
                'title'         => 'Przykładowy, aktywny, zagnieżdżony tytuł kategorii 2.'
            ]
        );

        $content1 = $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $category2->id,
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'Przykładowy tytuł zawartości 1.'
                ]
            ]
        );

        // It should not be in $titles array.
        $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $category1->id,
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'Przykładowy tytuł zawartości 2.'
                ]
            ]
        );

        $url = $content1->getUrl('pl');
        // We get title from not active translation in second segment because routes are created only once for given lang.
        $this->assertEquals('przykladowy-tytul-kategorii-1/przykladowy-nieaktywny-zagniezdzony-tytul-kategorii-2/przykladowy-tytul-zawartosci-1', $url);

        $titles = $this->repository->getTitlesTranslationFromUrl($url, 'pl');

        $this->assertCount(3, $titles);

        $this->assertEquals('Przykładowy tytuł kategorii 1.', $titles[0]['title']);
        $this->assertEquals('Przykładowy, aktywny, zagnieżdżony tytuł kategorii 2.', $titles[1]['title']);
        $this->assertEquals('Przykładowy tytuł zawartości 1.', $titles[2]['title']);

        foreach ($titles as $key => $value) {
            $this->assertNotEquals('Example title category 1.', $value['title']);
            $this->assertNotEquals('Przykładowy, nieaktywny, zagnieżdżony tytuł kategorii 2.', $value['title']);
            $this->assertNotEquals('Example, active, nested title category 2.', $value['title']);
            $this->assertNotEquals('Przykładowy tytuł zawartości 2.', $value['title']);
        }

        // We should check what happens for url like 'blog/blog/blog'.
        $blogCategory1 = $this->repository->create(
            [
                'type'         => 'category',
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'blog'
                ]
            ]
        );

        $blogCategory2 = $this->repository->create(
            [
                'type'         => 'category',
                'parent_id'    => $blogCategory1->id,
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'blog'
                ]
            ]
        );

        $blogContent1 = $this->repository->create(
            [
                'type'         => 'content',
                'parent_id'    => $blogCategory2->id,
                'translations' => [
                    'language_code' => 'pl',
                    'title'         => 'blog'
                ]
            ]
        );

        $blogUrl = $blogContent1->getUrl('pl');
        $this->assertEquals('blog/blog/blog', $blogUrl);

        $blogTitles = $this->repository->getTitlesTranslationFromUrl($blogUrl, 'pl');

        $this->assertCount(3, $titles);

        foreach ($blogTitles as $blogValue) {
            $this->assertEquals('blog', $blogValue['title']);
        }
    }

    /**
     * @test
     */
    public function itReturnsTitlesWithMatchedUrlsArray()
    {
        $url = 'przykladowy-tytul-kategorii-1/przykladowy-tytul-kategorii-2/przykladowy-tytul-zawartosci-1';

        $titles = [
            ['title' => 'Przykładowy tytuł kategorii 1.'],
            ['title' => 'Przykładowy tytuł kategorii 2.'],
            ['title' => 'Przykładowy tytuł zawartości 1.']
        ];

        $titlesAndUrls = $this->repository->matchTitlesWithUrls($titles, $url, 'pl');

        $this->assertInternalType('array', $titlesAndUrls);
        $this->assertCount(3, $titlesAndUrls);

        $this->assertEquals('Przykładowy tytuł kategorii 1.', $titlesAndUrls[0]['title']);
        $this->assertEquals('/pl/przykladowy-tytul-kategorii-1', $titlesAndUrls[0]['url']);

        $this->assertEquals('Przykładowy tytuł kategorii 2.', $titlesAndUrls[1]['title']);
        $this->assertEquals('/pl/przykladowy-tytul-kategorii-1/przykladowy-tytul-kategorii-2', $titlesAndUrls[1]['url']);

        $this->assertEquals('Przykładowy tytuł zawartości 1.', $titlesAndUrls[2]['title']);
        $this->assertEquals('/pl/przykladowy-tytul-kategorii-1/przykladowy-tytul-kategorii-2/przykladowy-tytul-zawartosci-1', $titlesAndUrls[2]['url']);
    }

    /*
    |--------------------------------------------------------------------------
    | END Translations tests
    |--------------------------------------------------------------------------
    */
}
