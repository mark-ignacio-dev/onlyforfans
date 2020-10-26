<?php

namespace App;

use Auth;
use Cmgmyr\Messenger\Traits\Messagable;
use DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{
    use Notifiable;
    use EntrustUserTrait;
    // use SoftDeletes, EntrustUserTrait {

    //     SoftDeletes::restore insteadof EntrustUserTrait;
    //     EntrustUserTrait::restore insteadof SoftDeletes;

    // }
    use Messagable;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    //protected $dates = ['deleted_at'];


      /**
       * The attributes that are mass assignable.
       *
       * @var array
       */
    protected $appends = [
        'name',
        'avatar',
        'cover',
        'about',
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'timeline_id', 'email', 'verification_code', 'email_verified', 'remember_token', 'password', 'birthday', 'city', 'gender', 'last_logged', 'timezone', 'affiliate_id', 'language', 'country', 'active', 'verified', 'facebook_link', 'twitter_link', 'dribbble_link', 'instagram_link', 'youtube_link', 'linkedin_link', 'wishlist', 'website', 'instagram','custom_option1', 'custom_option2', 'custom_option3', 'custom_option4'
        , 'bank_account', 'price', 'is_payment_set', 'is_bank_set', 'is_follow_for_free', 'is_online'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'verification_code', 'email', 'timeline',
    ];

    /**
     * Get the user's  name.
     *
     * @param string $value
     *
     * @return string
     */
    public function getNameAttribute($value)
    {
        return $this->timeline->name;
    }

    /**
     * Get the user's  username.
     *
     * @param string $value
     *
     * @return string
     */
    public function getUsernameAttribute($value)
    {
        return $this->timeline->username;
    }

    /**
     * Get the user's  avatar.user().
     *
     * @param string $value
     *
     * @return string
     */
    public function getAvatarAttribute($value)
    {
        return $this->timeline->avatar ? url('user/avatar/'.$this->timeline->avatar->source) : url('user/avatar/default-'.$this->gender.'-avatar.png');
    }

    /**
     * Get the user's  cover.
     *
     * @param string $value
     *
     * @return string
     */
    public function getCoverAttribute($value)
    {
        return $this->timeline->cover ? $this->timeline->cover->source : null;
    }

    /**
     * Get the user's  about.
     *
     * @param string $value
     *
     * @return string
     */
    public function getAboutAttribute($value)
    {
        return $this->timeline->about ? $this->timeline->about : null;
    }

    //this method is for displaying user avatar and default avatar from group in events feature
    /**
     * Get the user's  avatar.user().
     *
     * @param string $value
     *
     * @return string
     */
    public function getPictureAttribute($value)
    {
        return $this->timeline->avatar ? url('user/avatar/'.$this->timeline->avatar->source) : url('group/avatar/default-group-avatar.png');
    }

    public function toArray()
    {
        $array = parent::toArray();

        $timeline = $this->timeline->toArray();

        foreach ($timeline as $key => $value) {
            if ($key != 'id') {
                $array[$key] = $value;
            }
        }

        $array['avatar'] = $this->avatar;

        return $array;
    }

    public function timeline()
    {
        return $this->belongsTo('App\Timeline');
    }

    public function followers()
    {
        return $this->belongsToMany('App\User', 'followers', 'leader_id', 'follower_id')->withPivot('status', 'referral', 'subscription_id');
    }

    public function following()
    {
        return $this->belongsToMany('App\User', 'followers', 'follower_id', 'leader_id')->withPivot('referral');
    }

    public function pages()
    {
        return $this->belongsToMany('App\Page', 'page_user', 'user_id', 'page_id')->withPivot('role_id', 'active');
    }

    public function timelinesSaved()
    {
        return $this->belongsToMany('App\Timeline', 'saved_timelines', 'user_id', 'timeline_id')->withPivot('type');
    }

    public function postsSaved()
    {
        return $this->belongsToMany('App\Post', 'saved_posts', 'user_id', 'post_id');
    }

    public function postsPinned()
    {
        return $this->belongsToMany('App\Post', 'pinned_posts', 'user_id', 'post_id');
    }

    public function userList()
    {
        return $this->belongsToMany('App\UserListType', 'user_lists', 'user_id', 'list_type_id');
    }

    public function own_pages()
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $own_pages = $this->pages()->where('role_id', $admin_role_id->id)->where('page_user.active', 1)->get();

        $result = $own_pages ? $own_pages : false;

        return $result;
    }

    public function own_groups()
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $own_groups = $this->groups()->where('role_id', $admin_role_id->id)->where('status', 'approved')->get();

        $result = $own_groups ? $own_groups : false;

        return $result;
    }

    public function payment() {
        return $this->hasOne('App\Payment');
    }

    public function groups()
    {
        return $this->belongsToMany('App\Group', 'group_user', 'user_id', 'group_id')->withPivot('role_id', 'status');
    }

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function pageLikes()
    {
        return $this->belongsToMany('App\Page', 'page_likes', 'user_id', 'page_id');
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification')->with('notified_from');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
    }

    public function get_group($id)
    {
        $group_page = $this->groups()->where('groups.id', $id)->first();
        
        $result = $group_page ? $group_page->pivot->status : false;
         
        return $result;
    }

    public function get_page($id)
    {
        return $this->pages()->where('pages.id', $id)->first();
        // $result = $user_page ? $user_page : false;
        // return $result;
    }

    public function getUserSettings($user_id)
    {
        $result = DB::table('user_settings')->where('user_id', $user_id)->first();

        return $result;
    }

    public function getUserListTypes($user_id)
    {
        $result = DB::table('user_list_types')->where('user_id', $user_id)->get();

        return $result;
    }

    public function deleteUserSettings($user_id)
    {
        $result = DB::table('user_settings')->where('user_id', $user_id)->delete();

        return $result;
    }

    public function getOthersSettings($username)
    {
        $timeline = Timeline::where('username', $username)->first();
        $user = self::where('timeline_id', $timeline->id)->first();
        $result = DB::table('user_settings')->where('user_id', $user->id)->first();

        return $result;
    }

    public function getReportsCount()
    {
        $post_reports = DB::table('post_reports')->get();
        $timeline_reports = DB::table('timeline_reports')->get();
        $result1 = count($post_reports);
        $result2 = count($timeline_reports);

        return $result1 + $result2;
    }

    public function updateFollowStatus($user_id)
    {
        $chk_user = DB::table('followers')->where('follower_id', $user_id)->where('leader_id', Auth::user()->id)->first();
        if ($chk_user->status == 'pending') {
            $result = DB::table('followers')->where('follower_id', $user_id)->where('leader_id', Auth::user()->id)->update(['status' => 'approved']);
        }

        $result = $result ? true : false;

        return $result;
    }

    public function decilneRequest($user_id)
    {
        $chk_user = DB::table('followers')->where('follower_id', $user_id)->where('leader_id', Auth::user()->id)->first();
        if ($chk_user->status == 'pending') {
            $result = DB::table('followers')->where('follower_id', $user_id)->where('leader_id', Auth::user()->id)->delete();
        }

        $result = $result ? true : false;

        return $result;
    }

    public function announcements()
    {
        return $this->belongsToMany('App\Announcement', 'announcement_user', 'user_id', 'announcement_id');
    }

    public function chkMyFollower($diff_timeline_id, $login_id)
    {
        $followers = DB::table('followers')->where('follower_id', $login_id)->where('leader_id', $diff_timeline_id)->where('status', '=', 'approved')->first();
        $result = $followers ? true : false;

        return $result;
    }

    public function conversations()
    {
        return $this->belongsToMany('App\Conversation', 'conversation_user', 'user_id', 'conversation_id');
    }

    public function messages()
    {
        return $this->conversations()->with('messages');
    }

    public function getUserPrivacySettings($loginId, $others_id)
    {
        $timeline_post_privacy = '';
        $timeline_post = '';
        $user_post = '';
        $result = '';

        $live_user_settings = $this->getUserSettings($others_id);

        if ($live_user_settings) {
            $timeline_post_privacy = $live_user_settings->timeline_post_privacy;
            $user_post_privacy = $live_user_settings->post_privacy;
        }

        //start $this if block is for timeline post privacy settings
        if ($loginId != $others_id) {
            if ($timeline_post_privacy != null && $timeline_post_privacy == 'only_follow') {
                $isFollower = $this->chkMyFollower($others_id, $loginId);
                if ($isFollower) {
                    $timeline_post = true;
                }
            } elseif ($timeline_post_privacy != null && $timeline_post_privacy == 'everyone') {
                $timeline_post = true;
            } elseif ($timeline_post_privacy != null && $timeline_post_privacy == 'nobody') {
                $timeline_post = false;
            }

          //start $this if block is for user post privacy settings
            if ($user_post_privacy != null && $user_post_privacy == 'only_follow') {
                $isFollower = $this->chkMyFollower($others_id, $loginId);
                if ($isFollower) {
                    $user_post = 'user';
                }
            } elseif ($user_post_privacy != null && $user_post_privacy == 'everyone') {
                $user_post = 'user';
            }
        } else {
            $timeline_post = true;
            $user_post = 'user';
        }
           //End
        $result = $timeline_post.'-'.$user_post;

        return $result;
    }

    public function events()
    {
        return $this->belongsToMany('App\Event', 'event_user', 'user_id', 'event_id');
    }

    public function get_eventuser($id)
    {
        return $this->events()->where('events.id', $id)->first();
    }

    public function is_eventadmin($user_id, $event_id)
    {
        $chk_isadmin = Event::where('id', $event_id)->where('user_id', $user_id)->first();

        $result = $chk_isadmin ? true : false;
        
        return $result;
    }

    public function getEvents()
    {
        $result = [];
        $guestevents =  $this->events()->get();
        if ($guestevents) {
            foreach ($guestevents as $guestevent) {
                if (!$this->is_eventadmin(Auth::user()->id, $guestevent->id)) {
                    array_push($result, $guestevent);
                }
            }
        }
        return $result;
    }

    public function is_groupAdmin($user_id, $group_id)
    {
        $admin_role_id = Role::where('name', 'admin')->first();

        $groupUser = $this->groups()->where('group_id', $group_id)->where('user_id', $user_id)->where('role_id', $admin_role_id->id)->where('status', 'approved')->first();

        $result = $groupUser ? true : false;

        return $result;
    }

    public function is_groupMember($user_id, $group_id)
    {
        $admin_role_id = Role::where('name', 'admin')->first();

        $groupMember = $this->groups()->where('group_id', $group_id)->where('user_id', $user_id)->where('role_id', '!=', $admin_role_id->id)->where('status', 'approved')->first();

        $result = $groupMember ? true : false;

        return $result;
    }

    public function settings()
    {
        $settings = DB::table('user_settings')->where('user_id', $this->id)->first();
        return $settings;
    }

    public function commentLikes()
    {
        return $this->belongsToMany('App\User', 'comment_likes', 'user_id', 'comment_id');
    }

    public function postLikes()
    {
        return $this->belongsToMany('App\User', 'post_likes', 'user_id', 'post_id');
    }

    public function postFollows()
    {
        return $this->belongsToMany('App\User', 'post_follows', 'user_id', 'post_id');
    }

    public function postShares()
    {
        return $this->belongsToMany('App\User', 'post_shares', 'user_id', 'post_id');
    }

    public function postReports()
    {
        return $this->belongsToMany('App\User', 'post_reports', 'reporter_id', 'post_id')->withPivot('status');
    }

    public function postTags()
    {
        return $this->belongsToMany('App\User', 'post_tags', 'user_id', 'post_id')->withPivot('status');
    }

    public function notifiedBy()
    {
        return $this->hasMany('App\Notification', 'notified_by', 'id');
    }

    public function timelineReports()
    {
        return $this->belongsToMany('App\User', 'timeline_reports', 'reporter_id', 'timeline_id')->withPivot('status');
    }

    public function userEvents()
    {
        return $this->hasMany('App\Event');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function deleteOthers()
    {
        $authUserId = Auth::user()->id;
        $otherposts = $this->timeline->posts()->where('user_id', '!=', Auth::user()->id)->get();
        foreach ($otherposts as $otpost) {
            $otpost->users_liked()->detach();
            $otpost->notifications_user()->detach();
            $otpost->shares()->detach();
            $otpost->reports()->detach();
            $otpost->users_tagged()->detach();
            $otpost->images()->detach();
            $comments = $otpost->allComments()->get();

            foreach ($comments as $comment) {
                $comment->comments_liked()->detach();
                $dependencies = Comment::where('parent_id', $comment->id)->update(['parent_id' => null]);
                $comment->update(['parent_id' => null]);
                $comment->delete();
            }
            $otpost->notifications()->delete();
            // if($otpost->shared_post_id != NULL) {
            //     $otpost->update(['shared_post_id' => null])->save();
            // }
            if (!empty($otpost->sharedPost()->first()) && count($otpost->sharedPost()->first()) != 0) {
                $otpost->sharedPost->delete();
            }
            $otpost->delete();
        }
    }

    public function joinedPages()
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $joined_pages = $this->pages()
                        ->where('role_id', '!=', $admin_role_id->id)
                        ->where('page_user.active', 1)
                        ->get();
        return $joined_pages ? $joined_pages : 0;
    }

    public function joinedGroups()
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $joined_groups = $this->groups()
                        ->where('role_id', '!=', $admin_role_id->id)
                        ->where('group_user.status', 'approved')
                        ->get();
        return $joined_groups ? $joined_groups : 0;
    }

    /**
     * @return BelongsToMany
     */
    public function paidSubscribers()
    {
        $followers = $this->followers()->pluck('follower_id')->toArray();

        $activeFollowers = Subscription::whereIn('follower_id', $followers)
            ->where('leader_id', $this->id)
            ->pluck('follower_id')->toArray();

        return $this->followers()->whereIn('follower_id', $activeFollowers);
    }
    
    public function activeSubscribers()
    {
        $followers = $this->followers()->pluck('follower_id')->toArray();

        $activeFollowers = Subscription::whereIn('follower_id', $followers)
            ->where('leader_id', $this->id)
            ->where('cancel_at', '=', null)
            ->pluck('follower_id')->toArray();

        return $this->followers()->whereIn('follower_id', $activeFollowers);
    }

    /**
     * @return HasMany
     */
    public function blockedProfiles()
    {
        return $this->hasMany(BlockedProfile::class, 'blocked_by');
    }

    /**
     * @return HasMany
     */
    public function purchasedPosts()
    {
        return $this->hasMany(PurchasedPost::class, 'purchased_by');
    }

    /**
     * @return Collection
     */
    public function getPurchasedPostsArrAttribute()
    {
        return $this->purchasedPosts()->pluck('post_id');
    }
    
    /**
     * @return BelongsToMany
     */
    public function favouriteUsers()
    {
        return $this->belongsToMany('App\User', 'favourite_users', 'user_id', 'favourite_user_id')->withPivot('favourite_user_id');
    }

    /**
     * @return HasMany
     */
    public function tips()
    {
        return $this->hasMany('App\PostTip', 'user_id');
    }
    
    public function usersSentTips()
    {
        return $this->belongsToMany('App\User', 'users_tips', 'tip_from', 'tip_to')->withPivot('amount');
    }
    
    public function usersReceivedTips()
    {
        return $this->belongsToMany('App\User', 'users_tips', 'tip_to', 'tip_from')->withPivot('amount');
    }

    /**
     * @return HasOne
     */
    public function bankAccountDetails()
    {
        return $this->hasOne('App\BankAccountDetails', 'user_id');
    }
}
