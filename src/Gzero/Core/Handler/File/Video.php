<?php namespace Gzero\Core\Handler\File;

use Gzero\Entity\File;

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

    // @codingStandardsIgnoreStart
    /**
     * {@inheritdoc}
     */
    public function validateExtension(File $file)
    {
        if (in_array($file->extension, config("gzero.allowed_file_extensions.$file->type"))) {
            return true;
        } else {
            throw new FileHandlerException("The extension of this file (.$file->extension) is not allowed for $file->type files");
        }
    }
    // @codingStandardsIgnoreEnd
}
