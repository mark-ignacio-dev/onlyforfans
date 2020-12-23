<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Mediafile;
use App\Vault;
use App\Vaultfolder;

class VaultfoldersController extends AppBaseController
{
    public function index(Request $request)
    {
        $sessionUser = Auth::user();

        //$this->validate($request, [
        $request->validate([
            'vf_id' => 'required',
        ]);
        $vfId = $request->vf_id;

        if ( is_null($vfId) || $vfId==='root' ) {
            $myVault = $sessionUser->vaults()->first(); // %FIXME
            $cwf = $myVault->getRootFolder(); // 'current working folder'
        } else {
            $cwf = Vaultfolder::findOrFail($vfId);
        }

        return response()->json([
            'cwf' => $cwf,
            'parent' => $cwf->vfparent,
            'children' => $cwf->vfchildren,
            'mediafiles' => $cwf->mediafiles,
        ]);
    }

    // %TODO: check session user owner
    public function show(Request $request, $pkid)
    {
        $sessionUser = Auth::user();
        $vaultfolder = Vaultfolder::where('id', $pkid)->with('vfchildren', 'vfparent', 'mediafiles')->first();
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
            'sessionUser' => $sessionUser,
            'vaultfolder' => $vaultfolder,
            'breadcrumb' => $breadcrumb,
            'shares' => $shares,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer|min:1',
            'vault_id' => 'required|integer|min:1',
            'vfname' => 'required|string',
        ]);

        $sessionUser = Auth::user();
        $vaultfolder = Vaultfolder::create( $request->only('parent_id', 'vault_id', 'vfname') );

        return response()->json([
            'vaultfolder' => $vaultfolder,
        ]);
    }

}
