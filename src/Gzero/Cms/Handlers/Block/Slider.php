<?php namespace Gzero\Cms\Handlers\Block;

use Gzero\Cms\Models\Block;
use Gzero\Cms\Services\FileService;
use Gzero\Core\Models\Language;

class Slider implements BlockTypeHandler {

    use CacheBlockTrait;

    /**
     * @var FileService
     */
    private $fileRepo;

    /**
     * Slider constructor.
     *
     * @param FileService $fileRepo File repository
     */
    public function __construct(FileService $fileRepo)
    {
        $this->fileRepo = $fileRepo;
    }

    /**
     * Load block
     *
     * @param Block    $block    Block
     * @param Language $language Language
     *
     * @return string
     */
    public function handle(Block $block, Language $language)
    {
        $html = $this->getFromCache($block, $language);
        if ($html !== null) {
            return $html;
        }
        $images = $this->fileRepo->getEntityFiles(
            $block,
            [
                ['type', '=', 'image'],
                ['is_active', '=', true]
            ]
        );
        $html   = view('gzero-cms::blocks.slider', [
            'block'       => $block,
            'translation' => $block->getActiveTranslation($language->code),
            'images'      => $images
        ])->render();
        $this->putInCache($block, $language, $html);
        return $html;
    }
}
