<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Fanledger;
use App\Post;
use App\Timeline;
use App\Enums\PaymentTypeEnum;
use App\Enums\PostTypeEnum;

class FanledgersController extends AppBaseController
{
    public function index(Request $request)
    {
        $sessionUser = Auth::user();
        $filters = $request->input('filters', []);

        $query = Post::query();
        foreach ($filters as $f) {
            switch ($f['key']) {
                case 'todo':
                    break;
            }
        }
        $fanledgers = $query->get();

        return response()->json([
            'fanledgers' => $fanledgers,
        ]);
    }
}