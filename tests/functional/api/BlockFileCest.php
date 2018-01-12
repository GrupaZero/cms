<?php namespace Cms\api;

use Cms\FunctionalTester;
use Gzero\Cms\Jobs\CreateBlock;
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

        $file = dispatch_now(CreateFile::image($image, 'Image', new Language(['code' => 'en']), $author, [
            'info'        => 'info text',
            'description' => 'My image',
            'is_active'   => true,
        ]));

        dispatch_now(new SyncFiles($block, [$file->id => ['weight' => 3]]));

        $I->sendGet(apiUrl("blocks/$block->id/files"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson(
            [
                [
                    'id' => $file->id,
                    'author_id' => $author->id,
                    'name' => $file->name,
                    'extension' => $file->extension,
                    'size' => $file->size,
                    'mime_type' => $file->mime_type,
                    'info' => $file->info,
                    'is_active' => $file->is_active,
                    'created_at' => $file->created_at->toAtomString(),
                    'updated_at' => $file->updated_at->toAtomString()
                ]
            ]
        );
    }
}