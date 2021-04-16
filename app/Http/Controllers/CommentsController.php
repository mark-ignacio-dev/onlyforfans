<?php
namespace App\Http\Controllers;

use DB;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Notifications\CommentReceived;
use App\Http\Resources\CommentCollection;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentsController extends AppBaseController
{
    // %TODO: refactor with scopes
    public function index(Request $request)
    {
        $request->validate([
            'filters' => 'array',
            'filters.post_id' => 'uuid|exists:posts,id', // if admin or post-owner only (per-post comments by fan use posts controller)
            'filters.user_id' => 'uuid|exists:users,id', // if admin only
            'filters.parent_id' => 'uuid|exists:comments,id',
        ]);

        $filters = $request->filters ?? [];

        // Init query
        $query = Comment::with('user', 'replies.user');

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            // non-admin: only view own comments
            $query->where('user_id', $request->user()->id); 
            unset($filters['user_id']);

            if ( array_key_exists('post_id', $filters) ) {
                $post = Post::find($filters['post_id']);
                $this->authorize('update', $post); // non-admin must own post filtered on
            }
        }

        // Apply any filters
        foreach ($filters as $key => $f) {
            // %TODO: subgroup under 'filters' (need to update axios.GET calls as well in Vue)
            switch ($key) {
                case 'user_id':
                case 'post_id':
                case 'parent_id':
                    $query->where($key, $f);
                    break;
            }
        }

        $data = $query->paginate( $request->input('take', env('MAX_COMMENTS_PER_REQUEST', 10)) );
        return new CommentCollection($data);
    }

    public function show(Request $request, Comment $comment)
    {
        $this->authorize('view', $comment);
        return response()->json([
            'comment' => $comment,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|uuid|exists:posts,id',
            'user_id' => 'required|uuid|exists:users,id',
            'parent_id' => 'nullable|uuid|exists:comments,id',
            'description' => 'required|string|min:3',
        ]);

        $post = Post::find($request->post_id);
        $this->authorize('comment', $post);

        $attrs = $request->except('post_id'); // timeline_id is now postable
        $attrs['commentable_type'] = 'posts'; // %FIXME: hardcoded
        $attrs['commentable_id'] = $post->id;

        $comment = Comment::create($attrs);
        $post->user->notify(new CommentReceived($post, $request->user()));
        $comment->prepFor();

        return response()->json([
            'comment' => $comment,
        ], 201);
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);
        $request->validate([
            'description' => 'required|string|min:1',
        ]);
        $attrs = $request->only([ 'description' ]);
        $comment->fill($attrs);
        $comment->save();

        return response()->json([
            'comment' => $comment,
        ]);
    }

    public function destroy(Request $request, Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->json([]);
    }

}
