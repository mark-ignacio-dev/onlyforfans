<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Invite;
use App\Models\Mediafile;
use App\Models\Vault;
use App\Models\Vaultfolder;
use App\Enums\InviteTypeEnum;
use App\Mail\ShareableInvited;

// $request->validate([ 'vf_id' => 'required', ]);
class VaultfoldersController extends AppBaseController
{
    public function index(Request $request)
    {
        $filters = $request->filters ?? [];

        if ( !$request->user()->isAdmin() ) {
            do {
                if ( array_key_exists('vault_id', $filters) ) {
                    $vault = Vault::findOrFail($request->filters['vault_id']);
                    if ( $request->user()->can('view', $vault) ) {
                        break; // allowed
                    }
                }
                abort(403); // none of the above match, therefore unauthorized
            } while(0);
        }

        $query = Vaultfolder::query();
        $query->with(['mediafiles', 'vfparent', 'vfchildren']);

        foreach ( $request->input('filters', []) as $k => $v ) {
            switch ($k) {
            case 'parent_id':
                if ( is_null($v) || ($v==='root') ) {
                    $query->isRoot();
                } else {
                    $query->isChildOf($v);
                }
                break;
            default:
                $query->where($k, $v);
            }
        }
        $vaultfolders = $query->get();

        return response()->json([
            'vaultfolders' => $vaultfolders,
        ]);
    }

    // %TODO: check session user owner
    public function show(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('view', $vaultfolder);

        $vaultfolder->load('vfchildren', 'vfparent', 'mediafiles');
        $breadcrumb = $vaultfolder->getBreadcrumb();
        $shares = collect();
        $vaultfolder->mediafiles->each( function($vf) use(&$shares) {
            $vf->sharees->each( function($u) use(&$vf, &$shares) {
                $shares->push([
                    'sharee_id' => $u->id,
                    'sharee_name' => $u->name,
                    'sharee_username' => $u->username,
                    'shareable_type' => 'mediafiles', // %FIXME: cleaner way to do this?, ie just get the pivot (?)
                    'shareable_id' => $vf->id,
                ]);
            });
        });
        $vaultfolder->vfchildren->each( function($vf) use(&$shares) {
            $vf->sharees->each( function($u) use(&$vf, &$shares) {
                $shares->push([
                    'sharee_id' => $u->id,
                    'sharee_name' => $u->name,
                    'sharee_username' => $u->username,
                    'shareable_type' => 'vaultfolders', // %FIXME: cleaner way to do this?, ie just get the pivot (?)
                    'shareable_id' => $vf->id,
                ]);
            });
        });

        return response()->json([
            'sessionUser' => $request->user(),
            'vaultfolder' => $vaultfolder,
            'breadcrumb' => $breadcrumb,
            'shares' => $shares,
        ]);
    }

    // %FIXME: this should be in VaultController, an does the Vault policy (?)
    public function store(Request $request)
    {
        $vrules = [
            'vault_id' => 'required|integer|min:1',
            'vfname' => 'required|string',
        ];

        $attrs = [];
        if ( $request->has('parent_id') && !is_null($request->parent_id) ) {
            $vrules['parent_id'] = 'required|integer|min:1'; // add validation rule
            $attrs['parent_id'] = $request->parent_id;
        } else {
            $attrs['parent_id'] = null; // parent will be root folder, skip parent_id validation (optional param in effect)
        }
        $request->validate($vrules);
        $attrs['vault_id'] = $request->vault_id;
        $attrs['vfname'] = $request->vfname;

        $vault = Vault::find($request->vault_id);
        $this->authorize('update', $vault);
        /*
        if ( $request->user()->cannot('update', $vault) || $request->user()->cannot('create', Vaultfolder::class) ) {
            abort(403);
        }
         */

        $vaultfolder = Vaultfolder::create($attrs);

        return response()->json([
            'vaultfolder' => $vaultfolder,
        ], 201);
    }

    public function update(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('update', $vaultfolder);

        $vrules = [
            'vfname' => 'required|sometimes|string',
        ];

        $attrs = [];
        if ( $request->has('parent_id') && !is_null($request->parent_id) ) {
            $vrules['parent_id'] = 'required|integer|min:1'; // add validation rule
            $attrs['parent_id'] = $request->parent_id;
        }
        $request->validate($vrules);
        $attrs['vfname'] = $request->vfname;

        $vaultfolder->fill($attrs);
        $vaultfolder->save();

        return response()->json([
            'vaultfolder' => $vaultfolder,
        ]);
    }

    public function destroy(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('delete', $vaultfolder);
        if ( $vaultfolder->isRootFolder() ) {
            abort(400);
        }
        $vaultfolder->delete();
        return response()->json([]);
    }

    // ---

    // Invite one or more persons to register for the site to share a (single) vaultfolder (access)
    public function invite(Request $request, Vaultfolder $vaultfolder)
    {
        $this->authorize('update', $vaultfolder);

        $vrules = [
            'invitees' => 'required|array',
            'invitees.*.email' => 'required|email',
            'invitees.*.name' => 'string',
        ];

        $sharees = $request->input('invitees', []);
        $invites = collect();
        foreach ( $sharees as $se ) {
            $i = Invite::create([
                'inviter_id' => $request->user()->id,
                'email' => $se['email'],
                'itype' => InviteTypeEnum::VAULT,
                'custom_attributes' => [
                    'shareables' => $request->invitees ?? [],
                    'vaultfolder_id' => $vaultfolder->id,
                ],
            ]);
            Mail::to($i->email)->queue( new ShareableInvited($i) );
            //$i->send();
            $invites->push($i);
        }

        //$request->user()->sharedVaultfolders()->syncWithoutDetaching($vaultfolder->id); // do share %TODO: need to do when they register (!)

        return response()->json([
            'invites' => $invites,
        ]);
    }


}