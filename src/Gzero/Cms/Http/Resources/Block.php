<?php namespace Gzero\Cms\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="Block",
 *   type="object",
 *   required={},
 *   @SWG\Property(
 *     property="author_id",
 *     type="number",
 *     example="10"
 *   ),
 *   @SWG\Property(
 *     property="type",
 *     type="string",
 *     example="basic"
 *   ),
 *   @SWG\Property(
 *     property="region",
 *     type="string",
 *     example="sidebarLeft"
 *   ),
 *   @SWG\Property(
 *     property="theme",
 *     type="string",
 *     example="is-block"
 *   ),
 *   @SWG\Property(
 *     property="weight",
 *     type="number",
 *     example="100"
 *   ),
 *   @SWG\Property(
 *     property="filter",
 *     type="json",
 *     description="Filters block visibility with route names and content's ids",
 *     example="{'+':['3\/*'],'-':['2\/19\/*']}"
 *   ),
 *   @SWG\Property(
 *     property="options",
 *     description="Contains customizable options",
 *     type="json",
 *     example="{'key':'value'}"
 *   ),
 *   @SWG\Property(
 *     property="is_active",
 *     type="boolean",
 *     example="true"
 *   ),
 *   @SWG\Property(
 *     property="is_cacheable",
 *     type="boolean",
 *     example="true"
 *   ),
 *   @SWG\Property(
 *     property="created_at",
 *     type="string",
 *     format="date-time"
 *   ),
 *   @SWG\Property(
 *     property="updated_at",
 *     type="string",
 *     format="date-time"
 *   ),
 *   @SWG\Property(
 *     property="translations",
 *     type="array",
 *     @SWG\Items(ref="#/definitions/BlockTranslation")
 *   )
 * )
 */
class Block extends Resource {

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
            'id'           => (int) $this->id,
            'author_id'    => $this->author_id,
            'type'         => $this->whenLoaded('type', function () {
                return $this->type->name;
            }),
            'region'       => $this->region,
            'theme'        => $this->theme,
            'weight'       => $this->weight,
            'filter'       => $this->filter,
            'options'      => $this->options,
            'is_active'    => $this->is_active,
            'is_cacheable' => $this->is_cacheable,
            'created_at'   => $this->created_at->toIso8601String(),
            'updated_at'   => $this->updated_at->toIso8601String(),
            'translations' => BlockTranslation::collection($this->whenLoaded('translations')),
        ];
    }
}
