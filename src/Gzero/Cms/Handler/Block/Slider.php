<?php namespace Gzero\Cms\Handler\Block;

use Gzero\Cms\Models\Block;
use Gzero\Cms\Service\FileService;
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
     * @param Block    $block Block
     * @param Language $lang  Language
     *
     * @return string
     */
    public function render(Block $block, Language $lang)
    {
        $html = $this->getFromCache($block, $lang);
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
        $html   = view('blocks.slider', ['block' => $block, 'images' => $images, 'lang' => $lang])->render();
        $this->putInCache($block, $lang, $html);
        return $html;
    }
}
