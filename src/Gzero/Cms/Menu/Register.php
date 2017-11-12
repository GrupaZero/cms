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

    /**
     * It searches parent link specified by url and adds child link to the parent
     *
     * @param array  $child    ['url' => $url, 'title' => $title, 'alt' => NULL]
     * @param string $url      Link url
     * @param array  $haystack Links array
     *
     * @return bool Return true if link added successfully otherwise false
     * @throws Exception
     */
    protected function addNextChild(array $child, $url, array &$haystack)
    {
        if (!isset($child['url'])) {
            throw new Exception("Class UserPanelMenu: 'url' key i required");
        }
        foreach ($haystack as &$value) {
            if ($value['url'] == $url) {
                $child['children']   = [];
                $value['children'][] = $child;
                return true;
            }
            if (isset($value['children']) && is_array($value['children'])) {
                $this->addNextChild($child, $url, $value['children']);
            }
        }
        return false;
    }
}
