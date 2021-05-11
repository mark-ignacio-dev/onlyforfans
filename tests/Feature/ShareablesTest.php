<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

//use App\Enums\PostTypeEnum;
//use App\Enums\PaymentTypeEnum;
use App\Models\Fanledger;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\Mediafile;
use App\Models\User;

class ShareablesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group shareables
     *  @group regression
     */
    public function test_owner_can_list_shareables()
    {
        $post = Post::has('shareables','>=',2)->firstOrFail(); // base on a post for now
        $creator = $post->user;
        $timeline = $creator->timeline;

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('shareables.index'), [
            //'user_id' => $creator->id,
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
        //dd($content);
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        $this->assertObjectHasAttribute('shareable_type', $content->data[0]);
        $this->assertObjectHasAttribute('shareable_id', $content->data[0]);
        $this->assertObjectHasAttribute('sharee_id', $content->data[0]);
        $this->assertObjectHasAttribute('is_approved', $content->data[0]);
        $this->assertObjectHasAttribute('access_level', $content->data[0]);

        // All resources returned are owned by creator
        $ownedByCreator = collect($content->data)->reduce( function($acc, $item) use(&$creator) {
            switch ( $item->shareable_type ) {
            case 'posts':
                $shareable = Post::find($item->shareable_id);
                break;
            case 'timelines':
                $shareable = Timeline::find($item->shareable_id);
                break;
            case 'mediafiles':
                $shareable = Mediafile::find($item->shareable_id);
                break;
            default:
                throw new Exception('Unknown shareable_type: '.$item->shareable_type);
            }
            if ( $shareable->getPrimaryOwner()->id === $creator->id ) {
                $acc += 1;
            }
            return $acc;
        }, 0);
        $this->assertEquals(count($content->data), $ownedByCreator); 
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

