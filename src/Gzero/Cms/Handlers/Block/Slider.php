<?php namespace Gzero\Cms\Handlers\Block;

use Gzero\Cms\Models\Block;
use Gzero\Core\Models\Language;
use Gzero\Core\Repositories\FileReadRepository;

class Slider implements BlockTypeHandler {

    use CacheBlockTrait;

    /**
     * @var FileReadRepository
     */
    private $fileRepo;

    /**
     * Slider constructor.
     *
     * @param FileReadRepository $fileRepo File repository
     */
    public function __construct(FileReadRepository $fileRepo)
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

        $files = $block->files;
        $html   = view('gzero-cms::blocks.slider', [
            'block'  => $block,
            'images' => $files->filter(
                function ($file) {
                    return $file->type->name === 'image';
                }
            )
        ])->render();
        $this->putInCache($block, $language, $html);
        return $html;
    }
}
