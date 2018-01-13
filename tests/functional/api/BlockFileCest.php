<?php namespace Cms\api;

use Cms\FunctionalTester;
use Gzero\Cms\Jobs\CreateBlock;
use Gzero\Core\Jobs\AddFileTranslation;
use Gzero\Core\Jobs\CreateFile;
use Gzero\Core\Jobs\SyncFiles;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BlockFileCest
{
    public function _before(FunctionalTester $I)
    {
        $I->apiLoginAsAdmin();

        Storage::fake('uploads');
    }

    public function shouldGetFilesSyncedWithBlock(FunctionalTester $I)
    {
        $author   = factory(User::class)->create();
        $language   = new Language(['code' => 'en']);
        $block = dispatch_now(CreateBlock::basic('Block title', $language, $author, [
            'body'      => 'Block body',
            'region'    => 'homepage',
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

        dispatch_now(new SyncFiles($block, [$file->id => ['weight' => 3]]));

        $I->sendGet(apiUrl("blocks/$block->id/files"));

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
                        'created_at' => $file->created_at->toAtomString(),
                        'updated_at' => $file->updated_at->toAtomString(),
                        'translations'=> [
                            [
                                'author_id' => $author->id,
                                'language_code' => 'en',
                                'title' => 'New translation',
                                'description' => 'Description',
                                'created_at' => $translation->created_at->toAtomString(),
                                'updated_at' => $file->updated_at->toAtomString()
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function shouldSyncFilesWithBlock(FunctionalTester $I)
    {
        $author   = factory(User::class)->create();
        $language   = new Language(['code' => 'en']);
        $block = dispatch_now(CreateBlock::basic('Block title', $language, $author, [
            'body'      => 'Block body',
            'region'    => 'homepage',
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

        $I->sendPUT(apiUrl("blocks/$block->id/files"), [
            'data' => [
                'id' => $file->id
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
                    'id' => $file->id,
                    'author_id' => $author->id,
                    'name' => 'file',
                    'extension' => 'jpg',
                    'size' => 10240,
                    'mime_type' => 'image/jpeg',
                    'info' => 'info text',
                    'is_active' => true,
                    'created_at' => $file->created_at->toAtomString(),
                    'updated_at' => $file->updated_at->toAtomString(),
                    'translations'=> [
                        [
                            'author_id' => $author->id,
                            'language_code' => 'en',
                            'title' => 'New translation',
                            'description' => 'Description',
                            'created_at' => $translation->created_at->toAtomString(),
                            'updated_at' => $file->updated_at->toAtomString()
                        ]
                    ]
                ]
            ]
        );
    }
}