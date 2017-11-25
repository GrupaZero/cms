<?php namespace Gzero\Cms\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="ContentTranslation",
 *   type="object",
 *   required={"title", "language_code"},
 *   @SWG\Property(
 *     property="title",
 *     type="string",
 *     example="example title"
 *   ),
 *   @SWG\Property(
 *     property="language_code",
 *     type="string",
 *     example="en"
 *   ),
 *   @SWG\Property(
 *     property="body",
 *     type="string",
 *     example="example body"
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
 *   )
 * )
 */
class ContentTranslation extends Resource {

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
            'id'            => (int) $this->id,
            'language_code' => $this->language_code,
            'title'         => $this->title,
            'body'          => $this->body,
            'is_active'     => $this->is_active,
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),
        ];
    }
}
