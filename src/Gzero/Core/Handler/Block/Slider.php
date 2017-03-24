<?php namespace Gzero\Core\Handler\Block;

use Gzero\Entity\Block;
use Gzero\Entity\Lang;
use Gzero\Repository\FileRepository;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Slider
 *
 * @namespace    Gzero\BlockTypeHandlers
 * @author       Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright    Copyright (c) 2014, Adrian Skierniewski
 */
class Slider implements BlockTypeHandler {

    use CacheBlockTrait;

    /**
     * @var FileRepository
     */
    private $fileRepo;

    /**
     * Slider constructor.
     *
     * @param FileRepository $fileRepo File repository
     */
    public function __construct(FileRepository $fileRepo)
    {
        $this->fileRepo = $fileRepo;
    }

    /**
     * Load block
     *
     * @param Block $block Block entity
     * @param Lang  $lang  Lang entity
     *
     * @return string
     */
    public function render(Block $block, Lang $lang)
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
        $html   = view('blocks.slider', ['block' => $block, 'images' => $images])->render();
        $this->putInCache($block, $lang, $html);
        return $html;
    }
}
