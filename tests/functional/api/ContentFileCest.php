<?php namespace Cms\api;

use Cms\FunctionalTester;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Core\Jobs\AddFileTranslation;
use Gzero\Core\Jobs\CreateFile;
use Gzero\Core\Jobs\SyncFiles;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ContentFileCest
{
    public function _before(FunctionalTester $I)
    {
        $I->apiLoginAsAdmin();

        Storage::fake('uploads');
    }

    public function shouldGetFilesSyncedWithContent(FunctionalTester $I)
    {
        $author   = factory(User::class)->create();
        $language   = new Language(['code' => 'en']);
        $content = dispatch_now(CreateContent::content('Content title', $language, $author, [
            'body'      => 'Content body',
            'is_active' => true
        ]));

        $image  = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Image', $language, $author, [
            'info'        => 'info text',
            'description' => 'My image',
            'is_active'   => true,
        ]));
        $translation = dispatch_now(new AddFileTranslation($file, 'New translation', $language, $author,
            ['description' => 'Description']
        ));

        dispatch_now(new SyncFiles($content, [$file->id => ['weight' => 3]]));

        $I->sendGet(apiUrl("contents/$content->id/files"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseJsonMatchesJsonPath('links[*]');
        $I->seeResponseJsonMatchesJsonPath('meta[*]');
        $I->seeResponseContainsJson(
            [
                'data' => [
                    [
                        'id' => $file->id,
                        'author_id' => $author->id,
                        'name' => 'file',
                        'extension' => 'jpg',
                        'size' => 10240,
                        'mime_type' => 'image/jpeg',
                        'info' => 'info text',
                        'is_active' => true,
                        'weight' => 3,
                        'created_at' => $file->created_at->toAtomString(),
                        'updated_at' => $file->updated_at->toAtomString(),
                        'translations'=> [
                            [
                                'author_id' => $author->id,
                                'language_code' => 'en',
                                'title' => 'New translation',
                                'description' => 'Description',
                                'created_at' => $translation->created_at->toAtomString(),
                                'updated_at' => $translation->updated_at->toAtomString()
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function shouldSetPerPageToFilesSyncedWithContent(FunctionalTester $I)
    {
        $author   = factory(User::class)->create();
        $language   = new Language(['code' => 'en']);
        $content = dispatch_now(CreateContent::content('Content title', $language, $author, [
            'body'      => 'Content body',
            'is_active' => true
        ]));

        $image  = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Image', $language, $author, [
            'info'        => 'info text',
            'description' => 'My image',
            'is_active'   => true,
        ]));
        dispatch_now(new AddFileTranslation($file, 'New translation', $language, $author,
            ['description' => 'Description']
        ));

        dispatch_now(new SyncFiles($content, [$file->id => ['weight' => 3]]));

        $I->sendGet(apiUrl("contents/$content->id/files?perPage=100"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseJsonMatchesJsonPath('links[*]');
        $I->seeResponseJsonMatchesJsonPath('meta[*]');
        $I->seeResponseContainsJson(
            [
                'meta'  => [
                    'current_page' => 1,
                    'from'         => 1,
                    'last_page'    => 1,
                    'path'         => apiUrl("contents/$content->id/files"),
                    'per_page'     => 100,
                    'to'           => 1,
                    'total'        => 1,
                ],
            ]
        );
    }

    public function shouldSyncFilesWithContent(FunctionalTester $I)
    {
        $author   = factory(User::class)->create();
        $language   = new Language(['code' => 'en']);
        $content = dispatch_now(CreateContent::content('Content title', $language, $author, [
            'body'      => 'Content body',
            'is_active' => true
        ]));

        $image  = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Image', $language, $author, [
            'info'        => 'info text',
            'description' => 'My image',
            'is_active'   => true,
        ]));
        $translation = dispatch_now(new AddFileTranslation($file, 'New translation', $language, $author,
            ['description' => 'Description']
        ));

        $I->sendPUT(apiUrl("contents/$content->id/files"), [
            'data' => [
                [
                    'id' => $file->id,
                    'weight' => 10
                ]
            ]
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseJsonMatchesJsonPath('links[*]');
        $I->seeResponseJsonMatchesJsonPath('meta[*]');
        $I->seeResponseContainsJson(
            [
                'data' => [
                    [
                        'id' => $file->id,
                        'author_id' => $author->id,
                        'name' => 'file',
                        'extension' => 'jpg',
                        'size' => 10240,
                        'mime_type' => 'image/jpeg',
                        'info' => 'info text',
                        'is_active' => true,
                        'weight' => 10,
                        'created_at' => $file->created_at->toAtomString(),
                        'updated_at' => $file->updated_at->toAtomString(),
                        'translations'=> [
                            [
                                'author_id' => $author->id,
                                'language_code' => 'en',
                                'title' => 'New translation',
                                'description' => 'Description',
                                'created_at' => $translation->created_at->toAtomString(),
                                'updated_at' => $translation->updated_at->toAtomString()
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function shouldSyncFilesWithoutWeightFieldWithContent(FunctionalTester $I)
    {
        $author   = factory(User::class)->create();
        $language   = new Language(['code' => 'en']);
        $content = dispatch_now(CreateContent::content('Content title', $language, $author, [
            'body'      => 'Content body',
            'is_active' => true
        ]));

        $image  = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Image', $language, $author, [
            'info'        => 'info text',
            'description' => 'My image',
            'is_active'   => true,
        ]));
        $translation = dispatch_now(new AddFileTranslation($file, 'New translation', $language, $author,
            ['description' => 'Description']
        ));

        $I->sendPUT(apiUrl("contents/$content->id/files"), [
            'data' => [
                [
                    'id' => $file->id
                ]
            ]
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseJsonMatchesJsonPath('links[*]');
        $I->seeResponseJsonMatchesJsonPath('meta[*]');
        $I->seeResponseContainsJson(
            [
                'data' => [
                    [
                        'id' => $file->id,
                        'author_id' => $author->id,
                        'name' => 'file',
                        'extension' => 'jpg',
                        'size' => 10240,
                        'mime_type' => 'image/jpeg',
                        'info' => 'info text',
                        'is_active' => true,
                        'weight' => 0,
                        'created_at' => $file->created_at->toAtomString(),
                        'updated_at' => $file->updated_at->toAtomString(),
                        'translations'=> [
                            [
                                'author_id' => $author->id,
                                'language_code' => 'en',
                                'title' => 'New translation',
                                'description' => 'Description',
                                'created_at' => $translation->created_at->toAtomString(),
                                'updated_at' => $translation->updated_at->toAtomString()
                            ]
                        ]
                    ]
                ]
            ]
        );
    }
}