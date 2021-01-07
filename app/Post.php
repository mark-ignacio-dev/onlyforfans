<?php

namespace App;

use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\Ownable;
use App\Interfaces\PaymentReceivable;

//use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model implements Ownable, PaymentReceivable
{
    const PRICE_TYPE = 'price'; // associated with a price
    const FREE_TYPE = 'free';
    const PAID_TYPE = 'paid'; // %PSG: ie, for subscribers (?)
    
    //use SoftDeletes;
    //protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','updated_at'];

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function sharees() { // can be shared with many users (via [shareables])
        return $this->morphToMany('App\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id');
    }

    public function mediafiles() {
        return $this->morphMany('App\Mediafile', 'resource');
    }

    public function ledgersales() {
        return $this->morphMany('App\Fanledger', 'purchaseable');
    }

    public function user() { // owner of the post
        return $this->belongsTo('App\User');
    }

    public function getOwner() : ?User {
        return $this->user;
    }

    public function timeline() {
        return $this->belongsTo('App\Timeline');
    }

    public function users_liked() {
        return $this->belongsToMany('App\User', 'post_likes', 'post_id', 'user_id')->withTimestamps();
    }

    //public function tip() {
        //return $this->belongsToMany('App\User', 'post_tips', 'post_id', 'user_id')->withPivot('amount')->withTimestamps();
    //}

    public function shares() {
        return $this->belongsToMany('App\User', 'post_shares', 'post_id', 'user_id');
    }

    public function usersSaved() {
        return $this->belongsToMany('App\User', 'saved_posts', 'post_id', 'user_id');
    }

    public function usersPinned() {
        return $this->belongsToMany('App\User', 'pinned_posts', 'post_id', 'user_id');
    }

    public function notifications_user() {
        return $this->belongsToMany('App\User', 'post_follows', 'post_id', 'user_id')->withTimestamps();
    }

    public function reports() {
        return $this->belongsToMany('App\User', 'post_reports', 'post_id', 'reporter_id')->withPivot('status');
    }

    public function comments() {
        return $this->hasMany('App\Comment')->where('parent_id', null);
    }

    public function users_shared() {
        return $this->belongsToMany('App\User', 'post_shares', 'post_id', 'user_id');
    }

    public function images() {
        return $this->belongsToMany('App\Media', 'post_media', 'post_id', 'media_id');
    }

    public function videos() {
    }

    public function users_posts() {
        return $this->belongsToMany('App\User', 'posts', 'id', 'user_id');
    }

    public function managePostReport($post_id, $user_id) {
        $post_report = DB::table('post_reports')->insert(['post_id' => $post_id, 'reporter_id' => $user_id, 'status' => 'pending', 'created_at' => Carbon::now()]);

        $result = $post_report ? true : false;

        return $result;
    }

    public function check_reports($post_id)
    {
        $post_report = DB::table('post_reports')->where('post_id', $post_id)->first();

        $result = $post_report ? true : false;

        return $result;
    }

    public function deleteManageReport($id)
    {
        $post_report = DB::table('post_reports')->where('id', $id)->delete();

        $result = $post_report ? true : false;

        return $result;
    }

    public function getUserName($id)
    {
        $user = User::find($id);
        $timeline = Timeline::where('id', $user->timeline_id)->first();
        $result = $timeline ? $timeline->username : false;

        return $result;
    }

    public function getAvatar($id)
    {
        $user = User::find($id);
        $timeline = Timeline::where('id', $user->timeline_id)->first();
        $media = Media::where('id', $timeline->avatar_id)->first();

        $result = $media ? $media->source : false;

        return $result;
    }

    public function getGender($id)
    {
        $user = User::find($id);

        $result = $user ? $user->gender : false;

        return $result;
    }

    public function postsLiked()
    {
        $result = DB::table('post_likes')->get();

        return $result;
    }

    public function postsReported()
    {
        $result = DB::table('post_reports')->get();

        return $result;
    }

    public function postShared()
    {
        $result = DB::table('post_shares')->get();

        return $result;
    }

    public function chkUserFollower($login_id, $post_user_id)
    {
        $followers = DB::table('followers')->where('follower_id', $post_user_id)->where('leader_id', $login_id)->where('status', '=', 'approved')->first();

        if ($followers) {
            $userSettings = DB::table('user_settings')->where('user_id', $login_id)->first();
            $result = $userSettings ? $userSettings->comment_privacy : false;

            return $result;
        }
    }

    public function chkUserSettings($login_id)
    {
        $userSettings = DB::table('user_settings')->where('user_id', $login_id)->first();
        $result = $userSettings ? $userSettings->comment_privacy : false;

        return $result;
    }

    public function users_tagged()
    {
        return $this->belongsToMany('App\User', 'post_tags', 'post_id', 'user_id');
    }

    public function getPageName($id)
    {
        $timeline = Timeline::where('id', $id)->first();
        $result = $timeline ? $timeline->username : false;

        return $result;
    }

    public function deletePageReport($id)
    {
        $timeline_report = DB::table('timeline_reports')->where('id', $id)->delete();
        $result = $timeline_report ? true : false;

        return $result;
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification', 'post_id', 'id');
    }

    public function sharedPost()
    {
        return $this->belongsTo('App\Post', 'id', 'shared_post_id');
    }

    public function allComments()
    {
        return $this->hasMany('App\Comment');
    }

    public function deleteMe()
    {
        $this->users_liked()->detach();
        $this->shares()->detach();
        $this->notifications_user()->detach();
        $this->reports()->detach();
        $this->users_tagged()->detach();
        $this->images()->detach();
        $comments = $this->allComments()->get();
        
        foreach ($comments as $comment) {
            $comment->comments_liked()->detach();
            $dependencies = Comment::where('parent_id', $comment->id)->update(['parent_id' => null]);
            // $comment->replies()->detach();
            $comment->update(['parent_id' => null]);
            $comment->delete();
        }

        // $this->comments()->delete();
        $this->notifications()->delete();
        if ($this->shared_post_id != null) {
            $this->update(['shared_post_id' => null])->save();
        }
        print_r($this->sharedPost()->first());
        if ($this->sharedPost()->first() != NULL && count($this->sharedPost()->first()) != 0) {
            $this->sharedPost->delete();
        }

        $this->delete();
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    // %%% --- Implement PaymentReceivable Interface ---

    public function receivePayment(
        string $ptype, // PaymentTypeEnum
        User $sender,
        //PaymentSendable $sender,
        //PaymentReceivable $receiver, -> $this
        int $amountInCents,
        array $cattrs = []
    ) : ?Fanledger
    {
        $result = DB::transaction( function() use($ptype, $amountInCents, $cattrs, &$sender) {

            switch ($ptype) {
                case PaymentTypeEnum::TIP:
                    $result = FanLedger::create([
                        'fltype' => $ptype,
                        'seller_id' => $this->user->id,
                        'purchaser_id' => $sender->id,
                        'purchaseable_type' => 'posts',
                        'purchaseable_id' => $this->id,
                        'qty' => 1,
                        'base_unit_cost_in_cents' => $amountInCents,
                        'cattrs' => $cattrs ?? [],
                    ]);
                    break;
                case PaymentTypeEnum::PURCHASE:
                    $result = FanLedger::create([
                        'fltype' => $ptype,
                        'seller_id' => $this->user->id,
                        'purchaser_id' => $sender->id,
                        'purchaseable_type' => 'posts',
                        'purchaseable_id' => $this->id,
                        'qty' => 1,
                        'base_unit_cost_in_cents' => $amountInCents,
                        'cattrs' => $cattrs ?? [],
                    ]);
                    $sender->sharedposts()->attach($this->id, [
                        'cattrs' => json_encode($cattrs ?? []),
                    ]);
                    break;
                default:
                    throw new Exception('Unrecognized payment type : '.$ptype);
            }

            return $result;
        });

        return $result ?? null;
    }

    // Can a user view this post (?)
    public function isViewableByUser(User $user) : bool
    {
        /*
        if ($user->sharedposts) {
            dd($user->sharedposts, $this->id, $user->id);
        } else {
            dd('found null');
        }
         */
        $postOwner = $this->user;
        if ( $postOwner->id === $user->id ) {
            return true;
        } else if ( $user->sharedposts->contains('id', $this->id) ) {
            return true;
        }
        return false;
    }

}
