<?php
namespace App;

use DB;
use App\SluggableTraits;
use App\Interfaces\Guidable;

class Invite extends BaseModel implements Guidable
{
    protected $guarded = ['id','created_at','updated_at'];

    public static $vrules = [
    ];

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function inviter() {
        return $this->belongsTo('App\User', 'inviter_id');
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    public function getJoinLinkAttribute($value) {
        return route('auth.register', ['token' => $this->token]);
    }

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName() : string {
        return $this->guid;
    }

    // %%% --- Other ---

    // %FIXME: move to observer/boot 'create'
    /*
    public static function doCreate(string $vname, User $owner) : Vault {
        $vault = DB::transaction(function () use($vname, &$owner) {
            $v = Vault::create([
                'vname' => $vname,
                'user_id' => $owner->id,
            ]);
            $vf = Vaultfolder::create([
                'parent_id' => null,
                'vault_id' => $v->id,
                'vfname' => 'Root',
            ]);
            $v->refresh();
            return $v;
        });
        return $vault;
    }
     */

}