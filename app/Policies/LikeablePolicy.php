<?php
namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Interfaces\Likeable;

// %FIXME: eventually replace this with specific model policies?
class LikeablePolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    // post, story, comment, mediafile, etc
    public function like(User $user, Likeable $like)
    {
        if ( $like instanceof \App\Story ) {
            return $like->timeline->followers->contains($user->id);
        }
        return false; // for now dis-allow all others - %FIXME
    }
}
