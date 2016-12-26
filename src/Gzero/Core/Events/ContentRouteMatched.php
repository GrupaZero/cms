<?php namespace Gzero\Core\Events;

use Gzero\Entity\Content;

class ContentRouteMatched {
    /**
     * The route instance.
     *
     * @var Content
     */
    public $content;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param  Content                  $content Matched content
     * @param  \Illuminate\Http\Request $request Request
     *
     */
    public function __construct(Content $content, $request)
    {
        $this->content = $content;
        $this->request = $request;
    }
}
