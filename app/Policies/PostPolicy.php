<?php
namespace App\Policies;

use App\Policies\Traits\OwnablePolicies;
use App\Models\Post;
use App\Models\User;
use App\Enums\PostTypeEnum;

class PostPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail', // if owner pass, if blocked fail, else check function
        'like'        => 'isOwner:pass isBlockedByOwner:fail', // if owner pass, if blocked fail, else check function
        'comment'     => 'isOwner:pass isBlockedByOwner:fail', // if owner pass, if blocked fail, else check function
        'purchase'    => 'isOwner:fail isBlockedByOwner:fail', // if owner fail, if blocked fail, else check function
        'tip'         => 'isOwner:fail isBlockedByOwner:fail',
        'update'      => 'isOwner:next:fail', // if non-owner fail, else check function
        'delete'      => 'isOwner:next:fail', // if non-owner fail, else check function
        'forceDelete' => 'isOwner:next:fail', // if non-owner fail, else check function
        'restore'     => 'isOwner:pass:fail', // if owner pass, all others fail
    ];

    /*
    protected function index(User $user) 
    {
    }
     */

    protected function view(User $user, Post $post)
    {
        //return $post->timeline->followers->contains($user->id);
        switch ($post->type) {
        case PostTypeEnum::FREE:
//dump('policy.f');
            return $post->timeline->followers->count()
                && $post->timeline->followers->contains($user->id);
        case PostTypeEnum::SUBSCRIBER:
//dump('policy.s');
            return $post->timeline->subscribers->count()
                && $post->timeline->subscribers->contains($user->id);
            //return $post->timeline->subscribers->contains($user->id);
            /*
            return $post->timeline->followers->count()
                && $post->timeline->followers()->wherePivot('access_level','premium')->count()
                && $post->timeline->followers()->wherePivot('access_level','premium')->contains($user->id);
             */
        case PostTypeEnum::PRICED:
//dump('policy.p');
            return $post->sharees->count()
                && $post->sharees->contains($user->id); // premium (?)
        }
    }

    /*
    protected function create(User $user)
    {
        throw new \Exception('check update policy for timeline instead');
    }
    */

    protected function update(User $user, Post $post)
    {
        switch ($post->type) {
        case PostTypeEnum::FREE:
            return true;
        case PostTypeEnum::SUBSCRIBER:
            return true; // %TODO
        case PostTypeEnum::PRICED:
            return !($post->ledgersales->count() > 0);
        }
    }

    protected function delete(User $user, Post $post)
    {
        switch ($post->type) {
        case PostTypeEnum::FREE:
            return true;
        case PostTypeEnum::SUBSCRIBER:
            return true; // %TODO
        case PostTypeEnum::PRICED:
            //return !($post->fanledgers->count() > 0);
            return $post->canBeDeleted();
        }
    }

    protected function restore(User $user, Post $post)
    {
        return false;
    }

    protected function forceDelete(User $user, Post $post)
    {
        return false;
    }

    protected function tip(User $user, Post $post)
    {
        return true;
    }

    protected function purchase(User $user, Post $post)
    {
        return true;
    }

    protected function like(User $user, Post $post)
    {
        return $user->can('view', $post);
        //return $post->timeline->followers->contains($user->id);
    }

    protected function comment(User $user, Post $post)
    {
        return $user->can('view', $post);
    }

    protected function isBlockedBy(User $sessionUser, User $user) : bool
    {
        return $sessionUser->$user->isBlockedBy($user);
    }

}
