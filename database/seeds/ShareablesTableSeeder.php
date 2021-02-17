<?php
namespace Database\Seeders;

use DB;
use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Libs\UserMgr;
use App\Models\Timeline;
use App\Models\Fanledger;
use App\Enums\PostTypeEnum;
use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;
use App\Enums\PaymentTypeEnum;
use App\Enums\MediafileTypeEnum;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;

class ShareablesTableSeeder extends Seeder
{
    use SeederTraits;

    // %FIXME: instead of starting from followers and finding timelines, we should start from timelines and associate
    // followers, and guarantee a timeline has at least one follower of each type
    public function run()
    {
        $this->initSeederTraits('ShareablesTableSeeder'); // $this->{output, faker, appEnv}

        // +++ Create ... +++

        $followers = User::get();
        $this->output->writeln("  - Shareables seeder: loaded ".$followers->count()." followers...");

        $followers->each( function($f) {

            static $iter = 1;

            // --- purchase some posts ---

            $purchaseablePosts = Post::where('type', PostTypeEnum::PRICED)
                ->where('postable_id', '<>', $f->timeline->id) // exclude my own
                ->get();
            $max = $this->faker->numberBetween( 0, min($purchaseablePosts->count()-1, $this->getMax('purchased')) );
            $this->command->info("  - Creating $max purchased-posts for user ".$f->name." (iter: $iter)");

            if ( $max > 0 ) {
                $purchaseablePosts->random($max)->each( function($p) use(&$f) {
                    $customAttributes = [ 'notes' => 'ShareablesTableSeeder.purchase_some_posts' ];
                    // %FIXME: this should also update shareables, and be encapsualted in a method in model or class-lib
                    $p->receivePayment(
                        PaymentTypeEnum::PURCHASE, // PaymentTypeEnum
                        $f, // User $sender | follower | purchaser
                        $p->price * 100, // int $amountInCents
                        $customAttributes,
                    );
                });
            }

            // --- follow some free timelines ---

            $timelines = Timeline::where('id', '<>', $f->timeline->id) // exclude my own
                ->where('is_follow_for_free', true)
                ->get();
            if ( $timelines->count() == 0 ) {
                throw new Exception('No free timelines found, please adjust user/timeline seeder and/or factory');
            }
            $max = $this->faker->numberBetween( 0, min($timelines->count()-1, $this->getMax('follower')) );
            $this->command->info("  - Following (default) $max timelines for user ".$f->name);

            if ( $max > 0 ) {
                $timelines->random($max)->each( function($t) use(&$f) {
                    $customAttributes = [ 'notes' => 'ShareablesTableSeeder.follow_some_free_timelines' ];
                    // %FIXME: should be encapsualted in a method in model or class-lib
                    DB::table('shareables')->insert([
                        'sharee_id' => $f->id,
                        'shareable_type' => 'timelines',
                        'shareable_id' => $t->id,
                        'is_approved' => 1,
                        'access_level' => 'default',
                        'cattrs' => json_encode($customAttributes),
                    ]);
                });
            }

            // --- subscribe to some timelines ---

            unset($timelines);
            $timelines = Timeline::where('id', '<>', $f->timeline->id) // exclude my own
                ->where('is_follow_for_free', 0)->get();
            if ( $timelines->count() == 0 ) {
                throw new Exception('No paid timelines found, please adjust user/timeline seeder and/or factory');
            }
            $groups = $timelines->split(2);

            unset($timelines);
            $timelines = $groups[0];
            $max = $this->faker->numberBetween( 1, min($timelines->count()-1, $this->getMax('subscriber')) );
            //$this->command->info("  - Following $max premium timelines for user ".$f->name);
            $timelines->random($max)->each( function($t) use(&$f) {
                // %FIXME: should be encapsualted in a method in model or class-lib
                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.follow_some_premium_timelines' ];
                DB::table('shareables')->insert([
                    'sharee_id' => $f->id,
                    'shareable_type' => 'timelines',
                    'shareable_id' => $t->id,
                    'is_approved' => 1,
                    'access_level' => 'default',
                    'cattrs' => json_encode($customAttributes),
                ]);
            });

            unset($timelines);
            $timelines = $groups[1];
            $max = $this->faker->numberBetween( 1, min($timelines->count()-1, $this->getMax('subscriber')) );
            //$this->command->info("  - Subscribing to $max premium timelines for user ".$f->name);
            $timelines->random($max)->each( function($t) use(&$f) {
                // %FIXME: should be encapsualted in a method in model or class-lib
                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.subscribe_to_some_premium_timelines' ];
                DB::table('shareables')->insert([
                    'sharee_id' => $f->id, // fan
                    'shareable_type' => 'timelines',
                    'shareable_id' => $t->id,
                    'is_approved' => 1,
                    'access_level' => 'premium',
                    'cattrs' => json_encode($customAttributes),
                ]);
                Fanledger::create([
                    //'from_account' => , // %TODO: see https://trello.com/c/LzTUmPCp
                    //'to_account' => ,
                    'fltype' => PaymentTypeEnum::SUBSCRIPTION,
                    'purchaser_id' => $f->id, // fan
                    'seller_id' => $t->user->id,
                    'purchaseable_type' => 'timelines',
                    'purchaseable_id' => $t->id,
                    'qty' => 1,
                    'base_unit_cost_in_cents' => $t->user->price*100, // %FIXME: price should be on timeline not user
                    'cattrs' => json_encode($customAttributes),
                ]);
            });

            // ---


            $iter++;

        }); // $followers->each( ... )

        // Create an additional users/timelines, which won't have any followers (useful for testing)
        $isFollowForFree = true;
        User::factory()->count(2)->create()->each( function($u) use(&$isFollowForFree) {

            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("ShareablesTableSeeder - Adding avatar & cover for new user " . $u->name);
                $avatar = FactoryHelpers::createImage(MediafileTypeEnum::AVATAR);
                $cover = FactoryHelpers::createImage(MediafileTypeEnum::COVER);
            } else {
                $avatar = null;
                $cover = null;
            }

            $u->save();

            $timeline = $u->timeline;
            $timeline->is_follow_for_free = $isFollowForFree;
            $timeline->price = $isFollowForFree ? 0 : $this->faker->randomFloat(2, 1, 300);
            $timeline->avatar_id = $avatar->id ?? null;
            $timeline->cover_id = $cover->id ?? null;
            $timeline->save();
            $isFollowForFree = !$isFollowForFree; // toggle so we get at least one of each

            //Update default user settings
            // DB::table('user_settings')->insert([
            //     'user_id'               => $u->id,
            //     'confirm_follow'        => 'no',
            //     'follow_privacy'        => 'everyone',
            //     'comment_privacy'       => 'everyone',
            //     'timeline_post_privacy' => 'everyone',
            //     'post_privacy'          => 'everyone',
            //     'message_privacy'       => 'everyone',
            // ]);
        });

    }

    private function getMax($param) : int
    {
        static $max = [
            'testing' => [
                'purchased' => 3,
                'follower' => 3,
                'subscriber' => 3,
            ],
            'local' => [
                'purchased' => 3,
                'follower' => 3,
                'subscriber' => 3,
            ],
        ];
        return $max[$this->appEnv][$param];
    }


}
