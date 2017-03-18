<?php namespace Gzero\Core\Handler\File;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class FileTypeHandler
 *
 * @package    Gzero\FileTypeHandler
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
interface FileTypeHandler {

    /**
     * Validate file extension
     *
     * @param string $extension File extension
     *
     * @return FileTypeHandler
     */
    public function validateExtension($extension);
}
