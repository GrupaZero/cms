<?php namespace Gzero\Core\Handler\File;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Basic
 *
 * @package    Gzero\FileTypeHandlers
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Video implements FileTypeHandler {

    /**
     * {@inheritdoc}
     */
    public function validateExtension($extension)
    {
        if (!in_array($extension, config("gzero.upload.allowed_file_extensions.video"))) {
            throw new FileHandlerException("The extension of this file (.$extension) is not allowed for video files");
        }
    }
}
