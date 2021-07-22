<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;

use Carbon\Carbon;
use Money\Money;
use Tests\TestCase;

use App\Notifications\CampaignGoalReached;
use App\Notifications\CommentReceived;
use App\Notifications\EmailVerified;
use App\Notifications\IdentityVerificationRejected;
use App\Notifications\IdentityVerificationRequestSent;
use App\Notifications\IdentityVerificationVerified;
use App\Notifications\MessageReceived;
use App\Notifications\NewCampaignContributionReceived;
use App\Notifications\NewReferralReceived;
use App\Notifications\NewSubPaymentReceived;
use App\Notifications\NotifyTraits;
use App\Notifications\PasswordChanged;
use App\Notifications\PasswordReset;
use App\Notifications\PostTipped;
use App\Notifications\ResourceLiked;
use App\Notifications\ResourcePurchased;
use App\Notifications\SubRenewalPaymentReceived;
use App\Notifications\SubRenewalPaymentReceivedReturningSubscriber;
use App\Notifications\TimelineFollowed;
use App\Notifications\TimelineSubscribed;
use App\Notifications\TipReceived;
use App\Notifications\VaultfileShareSent;

use App\Apis\Sendgrid\Api as SendgridApi;

use App\Models\Campaign;
use App\Models\Chatmessage;
use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use App\Models\Timeline;
use App\Models\Subscription;
use App\Models\Tip;
use App\Models\Verifyrequest;

use App\Enums\VerifyStatusTypeEnum;


// %NOTE: these tests are meant for manual inspection, not automation

// Send mail via SendGrid (default) in Sandbox Mode
// $ DEBUG_BYPASS_SENDGRID_MAIL_NOTIFY=false DEBUG_ENABLE_SENDGRID_SANDBOX_MODE=true DEBUG_OVERRIDE_TO_EMAIL_FOR_SENDGRID="peter@peltronic.com" php artisan test --group="notify-via-sendgrid-unit"
// $ DEBUG_BYPASS_SENDGRID_MAIL_NOTIFY=false DEBUG_ENABLE_SENDGRID_SANDBOX_MODE=true DEBUG_OVERRIDE_TO_EMAIL_FOR_SENDGRID="peter@peltronic.com" php artisan test --group="here0721"

class SendgridApiTest extends TestCase
{

    use WithFaker;

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     * @group OFF-here0719
     */
    public function test_should_send_email_direct_via_sendgrid_wrapper_api_tip_received()
    {
        $response = SendgridApi::send('new-tip-received', [
            //'subject' => 'Subject Override Ex',
            'to' => [
                'email' => 'peter+test1@peltronic.com', 
                'name' => 'Peter Test1',
            ],
            'dtdata' => [
                'sender_name' => 'Joe Tipsender',
                'amount' => '$33.10',
                'home_url' => url('/'),
                'referral_url' => url('/referrals'),
                'privacy_url' => url('/privacy'),
                'manage_preferences_url' => url( route('users.showSettings', $notifiable->username) ),
                'unsubscribe_url' => url( route('users.showSettings', $notifiable->username) ),
            ],
        ]);
        $isSandbox = env('DEBUG_ENABLE_SENDGRID_SANDBOX_MODE', false);
        $this->assertEquals( $isSandbox?200:202, $response->statusCode() );
    }

    // -----

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_new_message_received()
    {
        $receiver = User::has('chatthreads')->firstOrFail();

        $chatthread = $receiver->chatthreads->first();
        $this->assertNotNull($chatthread);
        $this->assertNotNull($chatthread->id);

        $chatmessage = $chatthread->chatmessages->first();
        $this->assertNotNull($chatmessage);
        $this->assertNotNull($chatmessage->id);

        $sender = $chatmessage->sender;

        $result = Notification::send( $receiver, new MessageReceived($chatmessage, $sender) );

        $this->assertNull($result);

        dump('info', 
            ['receiver' => $receiver->name??'-'],
            ['sender' => $sender->name??'-'],
            'chatmessage', [
                'id' => $chatmessage->id,
                'mcontent' => $chatmessage->mcontent,
            ],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_new_referral_received()
    {
        $this->assertTrue(false, 'to-be-implemented');
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_new_campaign_goal_reached()
    {
        $this->assertTrue(false, 'to-be-implemented');
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_new_campaign_contribution_received()
    {
        $this->assertTrue(false, 'to-be-implemented');
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_sub_renewal_payment_received()
    {
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $sender = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = Notification::send( $receiver, new SubRenewalPaymentReceived($timeline, $sender, ['amount'=>$amount]) );
        $this->assertNull($result);
        dump('info', 
            ['receiver' => $receiver->name??'-'],
            ['sender' => $sender->name??'-'],
            ['amount' => $amount->getAmount()],
            'timeline', [
                'id' => $timeline->id,
                'slug' => $timeline->slug,
            ],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_sub_renewal_payment_received_returning_subscriber()
    {
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $sender = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = Notification::send( $receiver, new SubRenewalPaymentReceivedReturningSubscriber($timeline, $sender, ['amount'=>$amount]) );
        $this->assertNull($result);
        dump('info', 
            ['receiver' => $receiver->name??'-'],
            ['sender' => $sender->name??'-'],
            ['amount' => $amount->getAmount()],
            'timeline', [
                'id' => $timeline->id,
                'slug' => $timeline->slug,
            ],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_new_sub_payment_received()
    {
        $timeline = Timeline::has('followers', '>=', 1)->first(); // subscribable
        $sender = $timeline->followers->first(); // fan
        $receiver = $timeline->user; // creator
        $amount = Money::USD( $this->faker->numberBetween(1, 20) * 500 );
        $result = Notification::send( $receiver, new NewSubPaymentReceived($timeline, $sender, ['amount'=>$amount]) );
        $this->assertNull($result);
        dump('info', 
            ['receiver' => $receiver->name??'-'],
            ['sender' => $sender->name??'-'],
            ['amount' => $amount->getAmount()],
            'timeline', [
                'id' => $timeline->id,
                'slug' => $timeline->slug,
            ],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_tip_received()
    {
        $tip = Tip::where('tippable_type', 'posts')->first(); // the 'tip'
        $post = $tip->tippable; // the 'tippable'
        $sender = $tip->sender;
        $receiver = $tip->receiver;
        $result = Notification::send( $receiver, new TipReceived($post, $sender, ['amount'=>$tip->amount]) );
        $this->assertNull($result);

        dump('info', 
            'tip:', [
                'sender' => $sender->name??'-',
                'receiver' => $receiver->name??'-',
                'amount' => $tip->amount->getAmount()??'-',
            ],
            ['sender' => $sender->name??'-'],
            ['post-tippable', $post->slug??'-'],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_comment_received()
    {
        $comment = Comment::first();
        $sender = $comment->user;
        $receiver = $comment->post->timeline->user;
        dump('info', 
            'receiver' => $receiver->name, 
            'sender' => $sender->name, 
            'comment' => [
                'id' => $comment->id,
                'description' => $comment->description,
                'post' => [
                    'id' => $comment->post->id,
                ],
            ],
        );
        $result = Notification::send( $receiver, new CommentReceived($comment, $sender));
        $this->assertNull($result);
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_email_verified()
    {
        $user = User::first();
        $result = Notification::send( $user, new EmailVerified($user) );
        $this->assertNull($result);
        dump('info', 
            ['user' => $user->name??'-'],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_password_changed()
    {
        $user = User::first();
        $result = Notification::send( $user, new PasswordChanged($user) );
        $this->assertNull($result);
        dump('info', 
            ['user' => $user->name??'-'],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_password_reset()
    {
        $user = User::first();
        $result = Notification::send( $user, new PasswordReset($user) );
        $this->assertNull($result);
        dump('info', 
            ['user' => $user->name??'-'],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_id_verification_pending()
    {
        $user = User::first();

        $vr = Verifyrequest::create([
            'service_guid' => $this->faker->uuid,
            'vservice' => 'fake-service',
            'vstatus' => VerifyStatusTypeEnum::PENDING,
            'requester_id' => $user->id,
            'last_checked_at' => '2021-07-17 01:48:49',
        ]);

        $result = Notification::send( $user, new IdentityVerificationPending($vr, $user));
        $this->assertNull($result);

        dump('info', 
            ['requester' => $user->name??'-'],
            'verifyrequest', [
                'id' => $vr->id,
                'status' => $vr->vstatus,
            ],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_id_verification_approved()
    {
        $user = User::first();

        $vr = Verifyrequest::create([
            'service_guid' => $this->faker->uuid,
            'vservice' => 'fake-service',
            'vstatus' => VerifyStatusTypeEnum::VERIFIED,
            'requester_id' => $user->id,
            'last_checked_at' => '2021-07-17 01:48:49',
        ]);

        $result = Notification::send( $user, new IdentityVerificationVerified($vr, $user));
        $this->assertNull($result);

        dump('info', 
            ['requester' => $user->name??'-'],
            'verifyrequest', [
                'id' => $vr->id,
                'status' => $vr->vstatus,
            ],
        );
    }

    /**
     * @group sendgrid-api-unit
     * @group NO-regression
     */
    public function test_should_notify_id_verification_rejected()
    {
        $user = User::first();
        $vr = Verifyrequest::create([
            'service_guid' => $this->faker->uuid,
            'vservice' => 'fake-service',
            'vstatus' => VerifyStatusTypeEnum::REJECTED,
            'requester_id' => $user->id,
            'last_checked_at' => '2021-07-17 01:48:49',
        ]);
        $result = Notification::send( $user, new IdentityVerificationRejected($vr, $user));
        $this->assertNull($result);
        dump('info', 
            ['requester' => $user->name??'-'],
            'verifyrequest', [
                'id' => $vr->id,
                'status' => $vr->vstatus,
            ],
        );
    }

}

        /*
        $api = IdMeritApi::create();
        $response = $api->issueToken();
        $this->assertEquals( 200, $response->status() );
        $json = $response->json();
        //dd( $json );
        $this->assertArrayHasKey('access_token', $json);
        $this->assertArrayHasKey('token_type', $json);
        $this->assertArrayHasKey('expires_in', $json);
        //dd( $response );
         */
