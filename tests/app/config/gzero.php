<?php
return [
    'upload'                  => [
        'directory' => env('UPLOAD_DIR', 'public') // directory inside filesystem root directory (storage/app/ as default)
    ],
    'file_type'               => [
        'image'    => 'Gzero\Core\Handler\File\Image',
        'document' => 'Gzero\Core\Handler\File\Document',
        'video'    => 'Gzero\Core\Handler\File\Video',
        'music'    => 'Gzero\Core\Handler\File\Music'
    ],
    'allowed_file_extensions' => [
        'image'    => ['png', 'jpg', 'jpeg', 'tif'],
        'document' => ['pdf', 'odt', 'ods', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
        'video'    => ['mp4'],
        'music'    => ['mp3']
    ],
];
