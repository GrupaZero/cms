<?php namespace Gzero\Cms\Menu;

class Link {

    /** @var string */
    public $url;

    /** @var string */
    public $title;

    /** @var int */
    public $weight;

    /** @var \Illuminate\Support\Collection */
    public $children;

    /**
     * Link constructor.
     *
     * @param string $url      Link url
     * @param string $title    Link title
     * @param int    $weight   Link weight
     * @param array  $children Link children
     */
    public function __construct($url, $title, int $weight = 0, $children = [])
    {
        $this->url      = $url;
        $this->title    = $title;
        $this->weight   = $weight;
        $this->children = collect($children);
    }

}
