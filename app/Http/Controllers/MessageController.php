<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Timeline;
use App\Models\User;

class MessageController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->middleware('auth');
    }
    public function index()
    {
        return Message::with('user', 'receiver')->get();
    }
    public function fetchUsers(Request $request)
    {
        $sessionUser = $request->user();
        $timeline = Timeline::where('user_id', $sessionUser->id)->first();
        $followingUserIDs = $timeline->user->followedtimelines->pluck('id');
        $users = Timeline::with(['user', 'avatar'])->whereIn('id', $followingUserIDs)->get()->makeVisible(['user']);
        $users->each(function ($user) {
            $user->username = $user->user->username;
            $user->id = $user->user->id;
        });
    
        return [
            'followers' => $timeline->followers,
            'following' => $users
        ];
    }
    public function fetchContacts(Request $request)
    {
        $sessionUser = $request->user();
        $receivers = Message::where('user_id', $sessionUser->id)->pluck('receiver_id')->toArray();
       
        $contacts = array();
        foreach($receivers as $receiver) {
            $lastMessage = Message::where('receiver_id', $receiver)->latest()->first();
            $user = Timeline::with(['user', 'avatar'])->where('user_id', $receiver)->first()->makeVisible(['user']);
            $user->username = $user->user->username;
            $user->id = $user->user->id;
            array_push($contacts, [
                'last_message' => $lastMessage,
                'profile' => $user
            ]);
        }
        return $contacts;
    }
    public function store(Request $request)
    {
        $user = $request->user();

        $message = $user->messages()->create([
            'message' => $request->input('message'),
            'receiver_id' => $request->input('user'),
        ]);

        // broadcast(new MessageSentEvent($message, $user))->toOthers();

        return [
            'message' => $message,
            'user' => $user,
        ];
    }
    public function clearUser(Request $request, $id)
    {
        $deleted = Message::where('receiver_id', $id)->delete();
        if ($deleted) {
            return [
                'status' => 200
            ];
        }
        return [
            'status' => 400
        ];
    }
}
