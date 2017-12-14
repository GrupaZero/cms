<?php namespace Gzero\Cms\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="ContentTranslation",
 *   type="object",
 *   required={"title", "language_code"},
 *   @SWG\Property(
 *     property="author_id",
 *     type="number",
 *     example="10"
 *   ),
 *   @SWG\Property(
 *     property="language_code",
 *     type="string",
 *     example="en"
 *   ),
 *   @SWG\Property(
 *     property="title",
 *     type="string",
 *     example="example title"
 *   ),
 *   @SWG\Property(
 *     property="teaser",
 *     type="string",
 *     example="Example teaser"
 *   ),
 *   @SWG\Property(
 *     property="body",
 *     type="string",
 *     example="Example body"
 *   ),
 *   @SWG\Property(
 *     property="seo_title",
 *     type="string",
 *     example="SEO title"
 *   ),
 *   @SWG\Property(
 *     property="seo_description",
 *     type="string",
 *     example="SEO description"
 *   ),
 *   @SWG\Property(
 *     property="is_active",
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
            'id'              => (int) $this->id,
            'author_id'       => $this->author_id,
            'language_code'   => $this->language_code,
            'title'           => $this->title,
            'teaser'          => $this->teaser,
            'body'            => $this->body,
            'seo_title'       => $this->seo_title,
            'seo_description' => $this->seo_description,
            'is_active'       => $this->is_active,
            'created_at'      => $this->created_at->toIso8601String(),
            'updated_at'      => $this->updated_at->toIso8601String(),
        ];
    }
}
