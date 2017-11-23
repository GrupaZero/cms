<?php namespace Gzero\Cms\Handlers\File;

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
