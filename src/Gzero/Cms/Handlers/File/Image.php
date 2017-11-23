<?php namespace Gzero\Cms\Handlers\File;

class Image implements FileTypeHandler {

    /**
     * Validated extension
     *
     * @param string $extension extension
     *
     * @throws FileHandlerException
     * @return void
     */
    public function validateExtension($extension)
    {
        if (!in_array($extension, config("gzero.upload.allowed_file_extensions.image"))) {
            throw new FileHandlerException("The extension of this file (.$extension) is not allowed for image files");
        }
    }
}
