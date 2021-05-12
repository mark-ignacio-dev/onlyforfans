<?php
namespace Tests\Feature;

use DB;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Database\Seeders\TestDatabaseSeeder;

use Tests\TestCase;

use App\Libs\FactoryHelpers;
use App\Models\User;
use App\Models\Story;
use App\Models\Timeline;
use App\Models\Post;
use App\Models\Mediafile;
use App\Enums\MediafileTypeEnum;

// see: https://laravel.com/docs/5.4/http-tests#testing-file-uploads
// https://stackoverflow.com/questions/47366825/storing-files-to-aws-s3-using-laravel
// => https://stackoverflow.com/questions/29527611/laravel-5-how-do-you-copy-a-local-file-to-amazon-s3
// https://stackoverflow.com/questions/34455410/error-executing-putobject-on-aws-upload-fails
class MediafileTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group mediafiles
     *  @group regression
     */
    public function test_admin_can_list_mediafiles()
    {
        $expectedCount = Mediafile::where('mftype', MediafileTypeEnum::AVATAR)->count();

        $admin = User::first();
        $admin->assignRole('super-admin'); // upgrade to admin!
        $admin->refresh();

        $response = $this->actingAs($admin)->ajaxJSON('GET', route('mediafiles.index'), [
            'filters' => [
                'mftype' => MediafileTypeEnum::AVATAR,
            ],
        ]);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);

        $this->assertNotNull($content->data);
        $this->assertEquals($expectedCount, count($content->data));
        $this->assertObjectHasAttribute('mfname', $content->data[0]);
        $this->assertObjectHasAttribute('mftype', $content->data[0]);
        $this->assertEquals(MediafileTypeEnum::AVATAR, $content->data[0]->mftype);
    }

    /**
     *  @group mediafiles
     *  @group regression
     *  @group here0512
     */
    public function test_owner_can_list_mediafiles()
    {
        /*
        //dd ( Mediafile::where('resource_type','stories')->count() );
        $query = User::query();
        //$query->has('mediafiles', '>=', 1);
        $query->has('posts.mediafiles', '>=', 1);
        $query->has('timeline.stories.mediafiles', '>=', 1);
        $userPool = $query->get();
        dd($userPool->count());
         */

        $owner = User::has('posts.mediafiles', '>=', 1)
            ->has('timeline.stories.mediafiles', '>=', 1)
            ->firstOrFail();

        $response = $this->actingAs($owner)->ajaxJSON('GET', route('mediafiles.index'), [
            //'mftype' => MediafileTypeEnum::AVATAR,
        ]);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, $content->meta->current_page);

        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        //$this->assertEquals($expectedCount, count($content->data));
        $this->assertObjectHasAttribute('mfname', $content->data[0]);
        $this->assertObjectHasAttribute('mftype', $content->data[0]);
        //$this->assertEquals(MediafileTypeEnum::AVATAR, $content->data[0]->mftype);
    }

    /**
     *  @group mediafiles
     *  @group regression
     */
    public function test_can_store_mediafile()
    {
        Storage::fake('s3');
        $filename = 'file-foo.png';

        $user = User::first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $payload = [
            'mediafile' => $file,
            'mftype' => MediafileTypeEnum::AVATAR,
        ];
        $response = $this->actingAs($user)->ajaxJSON('POST', route('mediafiles.store'), $payload);

        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafile = $content->mediafile;

        Storage::disk('s3')->assertExists($mediafile->filename);
        $this->assertSame($filename, $mediafile->mfname);
        $this->assertSame(MediafileTypeEnum::AVATAR, $mediafile->mftype);
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);

        // Update or add avatars to some users for this test...
        $users = User::take(5)->get();
        $users->each( function($u) {
            $avatar = FactoryHelpers::createImage(MediafileTypeEnum::AVATAR, null, false); //skip S3 upload
            $u->save();
        });
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

