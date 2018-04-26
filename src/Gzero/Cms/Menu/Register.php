<?php namespace Gzero\Cms\Menu;

use Exception;
use Illuminate\Support\Collection;

class Register {

    /**
     * @var Collection
     */
    protected $links;

    /**
     * Register constructor.
     *
     * @param array $links Initial links
     */
    public function __construct(array $links = [])
    {
        $this->links = collect($links);
    }

    /**
     * It adds link to user panel menu
     *
     * @param Link $link Menu link
     *
     * @return void
     */
    public function add(Link $link)
    {
        $this->links->push($link);
    }

    /**
     * It adds child link to parent specified by url parameter
     *
     * @param string $parentUrl Parent url
     * @param Link   $link      Menu link
     *
     * @return void
     */
    public function addChild($parentUrl, Link $link)
    {
        $this->links->each(
            function ($value) use ($parentUrl, $link) {
                if ($value->url === $parentUrl) {
                    $value->children->push($link);
                    return false;
                }
            }
        );
    }

    /**
     * It returns whole menu as tree
     *
     * @return Collection
     */
    public function getMenu()
    {
        $this->links->each(
            function ($link) {
                if (!$link->children->isEmpty()) {
                    $link->children = $link->children->sortBy('weight')->values();
                }
            }
        );
        return $this->links->sortBy('weight')->values();
    }
}
