<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

//use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Models\Fanledger;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\User;

class TimelinesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_owner_can_view_own_timeline_feed()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;

        $payload = [];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('timelines.feeditems', $timeline->id), $payload);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $this->assertObjectHasAttribute('current_page', $content->data);
        $this->assertObjectHasAttribute('data', $content->data);
        $this->assertGreaterThan(0, count($content->data->data));
        $this->assertEquals(1, $content->data->current_page);
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_send_tip_to_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // assume non-admin (%FIXME)
        $creator = $timeline->user;
        $fan = $timeline->followers[0];

        $payload = [
            'base_unit_cost_in_cents' => $this->faker->randomNumber(3),
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.tip', $timeline->id), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $timelineR = $content->timeline;

        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::TIP)
            ->where('purchaseable_type', 'timelines')
            ->where('purchaseable_id', $timeline->id)
            ->where('seller_id', $creator->id)
            ->where('purchaser_id', $fan->id)
            ->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals($payload['base_unit_cost_in_cents'], $fanledger->base_unit_cost_in_cents);
        $this->assertTrue( $timeline->ledgersales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerpurchases->contains( $fanledger->id ) );
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_follow_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
        $creator = $timeline->user;

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();
        $this->assertFalse( $timeline->followers->contains( $fan->id ) );
        $this->assertFalse( $fan->followedtimelines->contains( $timeline->id ) );
        $origFollowerCount = $timeline->followers->count();

        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_follow_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.follow', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertEquals($timeline->id, $content->timeline->id);
        $this->assertTrue( $content->is_following );
        $this->assertEquals( $origFollowerCount+1, $content->follower_count );

        $timeline->refresh();
        $fan->refresh();
        $this->assertEquals('default', $timeline->followers->find($fan->id)->pivot->access_level);
        $this->assertEquals('timelines', $timeline->followers->find($fan->id)->pivot->shareable_type);
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $timeline->id ) );
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_unfollow_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $origFollowerCount = $timeline->followers->count();

        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_unfollow_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.follow', $timeline->id), $payload); // toggles
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertEquals($timeline->id, $content->timeline->id);
        $this->assertFalse( $content->is_following );
        $this->assertEquals( $origFollowerCount-1, $content->follower_count );

        $timeline->refresh();
        $this->assertFalse( $timeline->followers->contains( $fan->id ) );
        $this->assertFalse( $fan->followedtimelines->contains( $content->timeline->id ) );
    }

    /**
     *  @group timelines
     *  @group regression
     *  @group broken
     */
    public function test_blocked_can_not_follow_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
        $creator = $timeline->user;

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', $creator->id)->first();

        // Block the fan (note we do programatically, not via API as this is not integral to this test)
        DB::table('blockables')->insert([
            'blockable_type' => 'timelines',
            'blockable_id' => $timeline->id,
            'user_id' => $fan->id,
        ]);
        $timeline->refresh();
        $fan->refresh();

        // Try to follow
        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_subscribe_to_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.follow', $timeline->id), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_subscribe_to_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // includes subscribers
        $creator = $timeline->user;

        // Make sure creator's timeline is paid-only
        $timeline->is_follow_for_free = false;
        $timeline->price = $this->faker->randomNumber(3);
        $timeline->save();
        $timeline->refresh();
        $origSubscriberCount = $timeline->subscribers->count();

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        // Check access (before: should be denied)
        // [ ] %TODO: actually this is more complex: they can access the timeline if follower (default), but can only see a subset
        //     of posts on it before subscription (premium)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->user->username));
        $response->assertStatus(403);

        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_subscribe_to_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.subscribe', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertEquals($timeline->id, $content->timeline->id);
        $this->assertTrue( $content->is_subscribed );
        $this->assertEquals( $origSubscriberCount+1, $content->subscriber_count );

        $timeline->refresh();
        $this->assertEquals('premium', $timeline->followers->find($fan->id)->pivot->access_level);
        $this->assertEquals('timelines', $timeline->followers->find($fan->id)->pivot->shareable_type);
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $content->timeline->id ) );
        $this->assertTrue( $timeline->subscribers->contains( $fan->id ) );
        $this->assertTrue( $fan->subscribedtimelines->contains( $content->timeline->id ) );
        $this->assertTrue( $content->is_subscribed );

        // Check ledger
        $fanledger = Fanledger::where('fltype', PaymentTypeEnum::SUBSCRIPTION)
            ->where('purchaseable_type', 'timelines')
            ->where('purchaseable_id', $timeline->id)
            ->where('seller_id', $creator->id)
            ->where('purchaser_id', $fan->id)
            ->first();
        $this->assertNotNull($fanledger);
        $this->assertEquals(1, $fanledger->qty);
        $this->assertEquals(intval($timeline->price), $fanledger->base_unit_cost_in_cents);
        $this->assertTrue( $timeline->ledgersales->contains( $fanledger->id ) );
        $this->assertTrue( $fan->ledgerpurchases->contains( $fanledger->id ) );

        // Check access (after: should be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->user->username));
        $response->assertStatus(200);
    }

    /**
     *  @group timelines
     *  @group regression
     */
    public function test_can_unsubscribe_from_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // includes subscribers
        $creator = $timeline->user;

        // Make sure creator's timeline is paid-only
        $timeline->is_follow_for_free = false;
        $timeline->price = $this->faker->randomNumber(3);
        $timeline->save();
        $timeline->refresh();

        // find a user who is not yet a follower (includes subscribers) of timeline
        $fan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        // Check access (before: should not be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->user->username));
        $response->assertStatus(403);

        // Subscribe
        $payload = [
            'sharee_id' => $fan->id,
            'notes'=>'test_can_unsubscribe_from_timeline',
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.subscribe', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertTrue( $content->is_subscribed );
        $timeline->refresh();
        $origSubscriberCount = $timeline->subscribers->count();

        // Check access (after subscribe: should be allowed)
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.show', $timeline->user->username));
        $response->assertStatus(200);

        // Unsubscribe
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('timelines.subscribe', $timeline->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->timeline);
        $this->assertEquals($timeline->id, $content->timeline->id);
        $this->assertFalse( $content->is_subscribed );
        $this->assertEquals( $origSubscriberCount-1, $content->subscriber_count );

        $timeline->refresh();
        $this->assertFalse( $timeline->followers->contains( $fan->id ) );
        $this->assertFalse( $fan->followedtimelines->contains( $content->timeline->id ) );
        $this->assertFalse( $timeline->subscribers->contains( $fan->id ) );
        $this->assertFalse( $fan->subscribedtimelines->contains( $content->timeline->id ) );
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

