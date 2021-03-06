<?php namespace unit;

use Codeception\Test\Unit;
use Gzero\Cms\BlockFinder;
use Gzero\Cms\Models\Block;
use Gzero\Cms\Models\Content;
use Illuminate\Cache\CacheManager;
use Mockery as m;

class BlockFinderTest extends Unit {

    /**
     * @var \Gzero\Cms\BlockFinder
     */
    protected $finder;

    /**
     * @var \Mockery\Mock
     */
    protected $repo;

    protected function _before()
    {
        // Start the Laravel application
        $this->repo   = m::mock('Gzero\Cms\Repositories\BlockReadRepository');
        $this->finder = new BlockFinder($this->repo, new CacheManager(app()));
    }

    public function after()
    {
        // Stop the Laravel application
        m::close();
    }

    /** @test */
    public function itFindsCorrectBlock()
    {
        // Our content path
        $content = new Content(['path' => '1/2/3/4/5/6/']);
        // Content root path
        $rootPath = '1/';
        // Block visible on all pages (get by SQL query)
        $block1     = new Block();
        $block1->id = 1;
        // Block visible on all root children's pages
        $block2         = new Block();
        $block2->id     = 2;
        $block2->filter = ['+' => ['1/*']];
        // Block hidden on all root children's pages
        $block3         = new Block();
        $block3->id     = 3;
        $block3->filter = ['-' => ['1/*']];
        // Block visible only on that content
        $block4         = new Block();
        $block4->id     = 4;
        $block4->filter = ['+' => ['1/2/3/4/5/6/']];
        // Block hidden only on that content
        $block5         = new Block();
        $block5->id     = 5;
        $block5->filter = ['-' => ['1/2/3/4/5/6/']];
        // Block visible for all content parents children's
        $block6         = new Block();
        $block6->id     = 6;
        $block6->filter = ['+' => ['1/2/3/*']];
        // Block hidden for all content parents children's
        $block7         = new Block();
        $block7->id     = 7;
        $block7->filter = ['-' => ['1/2/3/*']];

        // Check for repository method call
        $this->repo->shouldReceive('getBlocksWithFilter')->andReturn(
            [
                $block1,
                $block2,
                $block3,
                $block4,
                $block5,
                $block6,
                $block7,
            ]
        );
        // Should not contain block visible on all pages those blocks are get by SQL query
        $this->assertNotContains(1, $this->finder->getBlocksIds($content));
        //  Block should be visible on all root children's pages
        $this->assertContains(2, $this->finder->getBlocksIds($content));
        //  Block should be hidden on all root children's pages
        $this->assertNotContains(3, $this->finder->getBlocksIds($content));
        //  Block should be visible only on that content
        $this->assertContains(4, $this->finder->getBlocksIds($content));
        //  Block should be hidden only on that content
        $this->assertNotContains(5, $this->finder->getBlocksIds($content));
        //  Block should be visible for all content parents children's
        $this->assertContains(6, $this->finder->getBlocksIds($content));
        //  Block should be hidden for all content parents children's
        $this->assertNotContains(7, $this->finder->getBlocksIds($content));
        // Blocks that should be hidden on root path
        $this->assertNotContains(1, $this->finder->getBlocksIds($rootPath));
        $this->assertNotContains(2, $this->finder->getBlocksIds($rootPath));
        $this->assertNotContains(4, $this->finder->getBlocksIds($rootPath));
        $this->assertNotContains(6, $this->finder->getBlocksIds($rootPath));
        // Blocks that should be visible on root path
        $this->assertContains(3, $this->finder->getBlocksIds($rootPath));
        $this->assertContains(5, $this->finder->getBlocksIds($rootPath));
        $this->assertContains(7, $this->finder->getBlocksIds($rootPath));
    }

    /** @test */
    public function itFindsCorrectBlockForStaticPages()
    {
        // Home page route name
        $findPath = 'home';
        // Content root path
        $rootPath = new Content(['path' => '1/']);
        // Block visible on home page
        $block1         = new Block();
        $block1->id     = 1;
        $block1->filter = ['+' => ['home']];
        // Block visible on all root children's pages
        $block2         = new Block();
        $block2->id     = 2;
        $block2->filter = ['+' => ['1/*']];
        // Block visible only on specific content
        $block3         = new Block();
        $block3->id     = 3;
        $block3->filter = ['+' => ['1/2/3/4/5/6/']];
        // Block hidden only on specific content
        $block4         = new Block();
        $block4->id     = 4;
        $block4->filter = ['-' => ['1/2/3/4/5/6/']];
        // Block hidden on homepage
        $block5         = new Block();
        $block5->id     = 5;
        $block5->filter = ['+' => ['1/2/3/4/5/6/'], '-' => ['home']];

        // Check for repository method call
        $this->repo->shouldReceive('getBlocksWithFilter')->andReturn(
            [
                $block1,
                $block2,
                $block3,
                $block4,
                $block5,
            ]
        );
        // Block should be visible on home page
        $this->assertContains(1, $this->finder->getBlocksIds($findPath));
        // Block should be visible on other page
        $this->assertContains(5, $this->finder->getBlocksIds(new Content(['path' => '1/2/3/4/5/6/'])));
        // All other blocks should be hidden
        $this->assertNotContains(2, $this->finder->getBlocksIds($findPath));
        $this->assertNotContains(3, $this->finder->getBlocksIds($findPath));
        $this->assertContains(4, $this->finder->getBlocksIds($findPath));
        // Blocks that should be hidden on root path
        $this->assertNotContains(1, $this->finder->getBlocksIds($rootPath));
        $this->assertNotContains(2, $this->finder->getBlocksIds($rootPath));
        $this->assertNotContains(3, $this->finder->getBlocksIds($rootPath));
        // Blocks that should be visible on root path
        $this->assertContains(4, $this->finder->getBlocksIds($rootPath));
        $this->assertContains(5, $this->finder->getBlocksIds($rootPath));
    }

    /** @test */
    public function itFindsBlockWithOnlyHiddenFilterOnOtherPages()
    {
        // Our content path
        $content = new Content(['path' => '1/2/3/4/5/6/']);
        // Our root path
        $root = new Content(['path' => '1/']);
        // Block hidden on home page - should be visible on our content
        $block1         = new Block();
        $block1->id     = 1;
        $block1->filter = ['+' => [], '-' => ['home']];
        // Block hidden on all root children's pages should - be hidden on our content
        $block2         = new Block();
        $block2->id     = 2;
        $block2->filter = ['+' => [], '-' => ['1/*']];
        // Block hidden for all content parents children's - should be hidden on our content
        $block3         = new Block();
        $block3->id     = 3;
        $block3->filter = ['+' => [], '-' => ['1/2/3/*']];
        // Block hidden only on specific content - should be hidden on our content
        $block4         = new Block();
        $block4->id     = 4;
        $block4->filter = ['+' => [], '-' => ['1/2/3/4/5/6/']];

        // Check for repository method call
        $this->repo->shouldReceive('getBlocksWithFilter')->andReturn(
            [
                $block1,
                $block2,
                $block3,
                $block4
            ]
        );
        // Blocks that should be visible on home page
        $this->assertNotContains(1, $this->finder->getBlocksIds('home'));
        $this->assertContains(2, $this->finder->getBlocksIds('home'));
        $this->assertContains(3, $this->finder->getBlocksIds('home'));
        $this->assertContains(4, $this->finder->getBlocksIds('home'));
        // Blocks that should be visible on our content
        $this->assertContains(1, $this->finder->getBlocksIds($content));
        $this->assertNotContains(2, $this->finder->getBlocksIds($content));
        $this->assertNotContains(3, $this->finder->getBlocksIds($content));
        $this->assertNotContains(4, $this->finder->getBlocksIds($content));
        // Blocks that should be visible on root path
        $this->assertContains(1, $this->finder->getBlocksIds($root));
        $this->assertContains(2, $this->finder->getBlocksIds($root));
        $this->assertContains(3, $this->finder->getBlocksIds($root));
        $this->assertContains(4, $this->finder->getBlocksIds($root));
    }

    /** @test */
    public function itFindsBlockWithOnlyHiddenFilterOnRootPages()
    {
        // Our content root path
        $findPath = '1/';
        // Block hidden on home page - should be visible on our content
        $block1         = new Block();
        $block1->id     = 1;
        $block1->filter = ['+' => [], '-' => ['home']];
        // Block hidden only on specific content - should be hidden on our content
        $block2         = new Block();
        $block2->id     = 2;
        $block2->filter = ['+' => [], '-' => ['1/']];

        // Check for repository method call
        $this->repo->shouldReceive('getBlocksWithFilter')->andReturn(
            [
                $block1,
                $block2
            ]
        );
        // Blocks that should be visible on home page
        $this->assertNotContains(1, $this->finder->getBlocksIds('home'));
        $this->assertContains(2, $this->finder->getBlocksIds('home'));
        // Blocks that should be visible on our content
        $this->assertContains(1, $this->finder->getBlocksIds($findPath));
        $this->assertNotContains(2, $this->finder->getBlocksIds($findPath));
    }

    /** @test */
    public function itUsesCorrectOrderOfOperations()
    {
        // Our content path
        $contentPath    = '1/2/';
        $block1         = new Block();
        $block1->id     = 1;
        $block1->filter = ['+' => ['1/*'], '-' => [$contentPath]];

        // Check for repository method call
        $this->repo->shouldReceive('getBlocksWithFilter')->andReturn([$block1]);

        // Block should be hidden because of order operation
        $this->assertNotContains(1, $this->finder->getBlocksIds($contentPath));
    }

    /** @test */
    public function itFindsCorrectBlockForNotFilteredPages()
    {
        // Our pages paths
        $contentPath    = '1/2/';
        $staticPagePath = 'contact';
        // Our root path
        $rootPath = '1/';
        // block hidden on other pages - should be visible
        $block1         = new Block();
        $block1->id     = 1;
        $block1->filter = ['+' => [], '-' => ['4/*']];
        // block shown only on other pages - should not be visible
        $block2         = new Block();
        $block2->id     = 2;
        $block2->filter = ['+' => ['4/*'], '-' => []];
        // block hidden on other static page - should be visible
        $block3         = new Block();
        $block3->id     = 3;
        $block3->filter = ['+' => [], '-' => ['home']];
        // block shown only on other static page - should not be visible
        $block4         = new Block();
        $block4->id     = 4;
        $block4->filter = ['+' => ['home'], '-' => []];

        // Check for repository method call
        $this->repo->shouldReceive('getBlocksWithFilter')->andReturn(
            [
                $block1,
                $block2,
                $block3,
                $block4,
            ]
        );
        // Check blocks for content
        $this->assertContains(1, $this->finder->getBlocksIds($contentPath));
        $this->assertContains(3, $this->finder->getBlocksIds($contentPath));
        $this->assertNotContains(2, $this->finder->getBlocksIds($contentPath));
        $this->assertNotContains(4, $this->finder->getBlocksIds($contentPath));
        // Check blocks for static page
        $this->assertContains(1, $this->finder->getBlocksIds($staticPagePath));
        $this->assertContains(3, $this->finder->getBlocksIds($staticPagePath));
        $this->assertNotContains(2, $this->finder->getBlocksIds($staticPagePath));
        $this->assertNotContains(4, $this->finder->getBlocksIds($staticPagePath));
        // Blocks that should be visible on root path
        $this->assertContains(1, $this->finder->getBlocksIds($rootPath));
        $this->assertContains(3, $this->finder->getBlocksIds($rootPath));
        // Blocks that should be hidden on root path
        $this->assertNotContains(2, $this->finder->getBlocksIds($rootPath));
        $this->assertNotContains(4, $this->finder->getBlocksIds($rootPath));
    }
}
