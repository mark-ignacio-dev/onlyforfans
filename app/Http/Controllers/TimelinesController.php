<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;

use App\Http\Resources\PostCollection;
use App\Models\User;
use App\Libs\FeedMgr;
use App\Libs\UserMgr;

use App\Models\Setting;
use App\Models\Timeline;
use App\Models\Fanledger;
use App\Models\Post;

use Illuminate\Http\Request;
use App\Enums\PaymentTypeEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TimelinesController extends AppBaseController
{
    // ---

    public function index(Request $request)
    {
        $query = User::query();

        // Apply filters
        //  ~ %TODO

        return response()->json([
            'users' => $query->get(),
        ]);
    }

    // %TODO: is this still used (?)
    public function show(Request $request, Timeline $timeline)
    {
        $this->authorize('view', $timeline);
        $sales = Fanledger::where('seller_id', $timeline->user->id)->sum('total_amount');

        $timeline->userstats = [ // %FIXME DRY
            'post_count' => $timeline->posts->count(),
            'like_count' => 0, // %TODO $timeline->user->postlikes->count(),
            'follower_count' => $timeline->followers->count(),
            'following_count' => $timeline->user->followedtimelines->count(),
            'subscribed_count' => 0, // %TODO $sessionUser->timeline->subscribed->count()
            'earnings' => $sales,
        ];

        return [
            'sessionUser' => $request->user(),
            'timeline' => $timeline,
        ];
    }

    // Display my home timeline
    public function homefeed(Request $request)
    {
        $query = Post::with('mediafiles', 'user')->withCount('comments')->where('active', 1);
        $query->whereHas('timeline', function($q1) use(&$request) {
            $q1->whereHas('followers', function($q2) use(&$request) {
                $q2->where('id', $request->user()->id);
            });
        });
        $data = $query->latest()->paginate( $request->input('take', env('MAX_POSTS_PER_REQUEST', 10)) );
        return new PostCollection($data);
    }

    // Get a list of items that make up a timeline feed, typically posts but
    //  keep generic as we may want to throw in other things
    //  %TODO: 
    //  ~ [ ] DEPRECATE, use FeedsController (?)
    //  ~ [ ] trending tags
    //  ~ [ ] announcements
    //  ~ [ ] hashtag search
    public function feed(Request $request, Timeline $timeline)
    {
        //$this->authorize('view', $timeline); // must be follower or subscriber
        //$filters = [];
        $query = Post::with('mediafiles', 'user')->withCount('comments')->where('active', 1);
        $query->where('postable_type', 'timelines')->where('postable_id', $timeline->id);
        $data = $query->latest()->paginate( $request->input('take', env('MAX_POSTS_PER_REQUEST', 10)) );
        return new PostCollection($data);
    }

    // Get suggested users (list/index)
    public function suggested(Request $request)
    {
        $TAKE = $request->input('take', 5);

        $followedIDs = $request->user()->followedtimelines->pluck('id');

        $query = Timeline::with('user')->inRandomOrder();
        $query->whereHas('user', function($q1) use(&$request, &$followedIDs) {
            $q1->where('id', '<>', $request->user()->id); // skip myself
            // skip timelines I'm already following
            $q1->whereHas('followedtimelines', function($q2) use(&$followedIDs) {
                $q2->whereNotIn('shareable_id', $followedIDs);
            });
        });

        // Apply filters
        //  ~ %TODO

        return response()->json([
            'timelines' => $query->take($TAKE)->get(),
        ]);
    }


    // toggles, returns set state
    public function follow(Request $request, Timeline $timeline)
    {
        $this->authorize('follow', $timeline);

        $request->validate([
            'sharee_id' => 'required|uuid|exists:users,id',
        ]);
        if ( $request->sharee_id != $request->user()->id ) {
            abort(403);
        }

        $cattrs = [];
        if ( $request->has('notes') ) {
            $cattrs['notes'] = $request->notes;
        }

        $existing = $timeline->followers->contains($request->sharee_id); // currently following?

        if ($existing) {
            $timeline->followers()->detach($request->sharee_id);
            $isFollowing = false;
        } else {
            $timeline->followers()->attach($request->sharee_id, [ // will sync work here?
                'shareable_type' => 'timelines',
                'shareable_id' => $timeline->id,
                'is_approved' => 1, // %FIXME
                'access_level' => 'default',
                'cattrs' => json_encode($cattrs),
            ]); //
            $isFollowing = true;
        }

        $timeline->refresh();
        return response()->json([
            'is_following' => $isFollowing,
            'timeline' => $timeline,
            'follower_count' => $timeline->followers->count(),
        ]);
    }

    public function subscribe(Request $request, Timeline $timeline)
    {
        $this->authorize('follow', $timeline);

        $request->validate([
            'sharee_id' => 'required|uuid|exists:users,id',
        ]);
        if ( $request->sharee_id != $request->user()->id ) {
            abort(403);
        }

        list($timeline, $isSubscribed) = DB::transaction( function() use(&$timeline, &$request) {
            $cattrs = [];
            if ( $request->has('notes') ) {
                $cattrs['notes'] = $request->notes;
            }

            $existingFollowing = $timeline->followers->contains($request->sharee_id); // currently following?
            $existingSubscribed = $timeline->subscribers->contains($request->sharee_id); // currently subscribed?

            if ( $existingSubscribed ) {
                // unsubscribe & unfollow
                $timeline->followers()->detach($request->sharee_id);
                $isSubscribed = false;
            } else {
                if ( $existingFollowing ) {
                    // upgrade from follow to subscribe => remove existing (covers 'upgrade') case
                    $timeline->followers()->detach($request->sharee_id); 
                } // otherwise, just a direct subscription...
                $timeline->followers()->attach($request->sharee_id, [
                    'shareable_type' => 'timelines',
                    'shareable_id' => $timeline->id,
                    'is_approved' => 1, // %FIXME
                    'access_level' => 'premium',
                    'cattrs' => json_encode($cattrs), // %FIXME: add a observer function?
                ]); //
                $timeline->receivePayment(
                    PaymentTypeEnum::SUBSCRIPTION,
                    $request->user(),
                    $timeline->price,
                    $cattrs,
                );
                $isSubscribed = true;
            }
            return [$timeline, $isSubscribed];
        });

        $timeline->refresh();
        return response()->json([
            'is_subscribed' => $isSubscribed,
            'timeline' => $timeline,
            'subscriber_count' => $timeline->subscribers->count(),
        ]);
    }

    public function tip(Request $request, Timeline $timeline)
    {
        $request->validate([
            'base_unit_cost_in_cents' => 'required|numeric',
        ]);

        $cattrs = [];
        if ( $request->has('notes') ) {
            $cattrs['notes'] = $request->notes;
        }

        try {
            $timeline->receivePayment(
                PaymentTypeEnum::TIP,
                $request->user(),
                $request->base_unit_cost_in_cents,
                $cattrs,
            );
        } catch(Exception | Throwable $e) {
            return response()->json([ 'message'=>$e->getMessage() ], 400);
        }

        $timeline->refresh();
        return response()->json([
            'timeline' => $timeline,
        ]);
    }

}
