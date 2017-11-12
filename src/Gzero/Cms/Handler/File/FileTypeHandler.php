<?php namespace Gzero\Cms\Handler\File;

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
