<?php namespace Gzero\Cms\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="Content",
 *   type="object",
 *   required={},
 *   @SWG\Property(
 *     property="parent_id",
 *     type="int",
 *     example="2"
 *   ),
 *   @SWG\Property(
 *     property="theme",
 *     type="string",
 *     example="content"
 *   ),
 *   @SWG\Property(
 *     property="translations",
 *     type="array",
 *     @SWG\Items(ref="#/definitions/ContentTranslation")
 *   )
 * )
 */
class Content extends Resource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                 => (int) $this->id,
            'parent_id'          => $this->parent_id,
            'type'               => $this->whenLoaded('type', function () {
                return $this->type->name;
            }),
            'theme'              => $this->theme,
            'weight'             => $this->weight,
            'rating'             => $this->rating,
            'visits'             => $this->visits,
            'is_on_home'         => $this->is_on_home,
            'is_comment_allowed' => $this->is_comment_allowed,
            'is_promoted'        => $this->is_promoted,
            'is_sticky'          => $this->is_sticky,
            'path'               => $this->buildPath($this->path),
            'published_at'       => $this->published_at->toIso8601String(),
            'created_at'         => $this->created_at->toIso8601String(),
            'updated_at'         => $this->updated_at->toIso8601String(),
            'route'              => $this->route->toArray(), // @TODO Need Resource in gzero-core
            'translations'       => ContentTranslation::collection($this->whenLoaded('translations')),
        ];
    }

    /**
     * Returns array of path ids as integers
     *
     * @param Content $path path to explode
     *
     * @return array extracted path
     */
    private function buildPath($path)
    {
        $result = [];
        foreach (explode('/', $path) as $value) {
            if (!empty($value)) {
                $result[] = (int) $value;
            }
        }
        return $result;
    }
}
