<?php
namespace App\Http\Controllers;

use DB;
use Exception;
use Throwable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Http\Resources\MediafileCollection;
use App\Http\Resources\ChatmessagegroupCollection;
use App\Http\Resources\Chatmessagegroup as ChatmessageResourcegroup;

use App\Models\User;
use App\Models\Mediafile;
use App\Models\Chatthread;
use App\Models\Chatmessage;


class ChatmessagesController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'chatthread_id'  => 'uuid|exists:chatthreads,id',
            'sender_id'      => 'uuid|exists:users,id',
            'participant_id' => 'uuid|exists:users,id',
            'is_flagged'     => 'boolean',
        ]);
        $filters = $request->only([
            'chatthread_id',
            'sender_id',
            'participant_id',
            'is_flagged',
        ]) ?? [];

        $query = Chatmessage::query(); // Init query

        // %NOTE: this method only returns delivered messages! ...use separate api endpoint
        //   for all messages or messages to be delivered
        $query->where('is_delivered', true); 

        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            //$query->where('user_id', $request->user()->id); // non-admin: can only view own...
            //unset($filters['user_id']);

            $query->whereHas('chatthread.participants', function($q1) use(&$request) {
                $q1->where('user_id', $request->user()->id); // limit to threads where session user is a participant
            });

            if ( array_key_exists('chatthread_id', $filters) ) {
                $chatthread = Chatthread::findOrFail($filters['chatthread_id']);
                $this->authorize('view', $chatthread);
            }
            if ( array_key_exists('sender_id', $filters) ) {
            }
            if ( array_key_exists('participant_id', $filters) ) {
            }
        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
            case 'sender_id':
                $query->where('sender_id', $v); // %FIXME: if non-admin limit 
                break;
            case 'participant_id':
                $query->whereHas('chatthread.participants', function($q1) use($key, $v) {
                    $q1->where('user_id', $v);
                });
                break;
            default:
                $query->where($key, $v);
            }
        }

        $data = $query->latest()->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new ChatmessageCollection($data);
    }


    public function search(Request $request)
    {
        $request->validate([
            'chatthread' => 'required|uuid|exists:chatthreads,id',
            'q'          => 'required_without:query',
            'query'      => 'required_without:q',
        ]);

        $chatthread = Chatthread::find($request->chatthread);

        $this->authorize('view', $chatthread);

        $query = $request->input('q') ?? $request->input('query');

        $data = Chatmessage::search($query)->where('chatthread_id', $chatthread->id)
            ->paginate($request->input('take', Config::get('collections.size.mid', 20) ));

        return new ChatmessageCollection($data);
    }

    /**
     * Return a paginated list of images that are in a chatthread
     * @param Request $request
     * @return void
     */
    public function gallery(Request $request, Chatthread $chatthread)
    {
        // $request->validate([
        //     'chatthread_id' => 'required|uuid|exists:chatthreads,id',
        // ]);

        $this->authorize('view', $chatthread);

        $query = Mediafile::whereHasMorph('resource', Chatmessage::class, function($q1) use ($chatthread) {
            $q1->where('chatthread_id', $chatthread->getKey());
        });

        $data = $query->latest()->paginate($request->input('take', Config::get('collections.size.mid', 20)));

        return new MediafileCollection($data);
    }


}
