<?php namespace Gzero\Cms\Http\Resources;

use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContentCollection extends ResourceCollection {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request request
     *
     * @return Collection
     */
    public function toArray($request)
    {
        return $this->collection;
    }
}
