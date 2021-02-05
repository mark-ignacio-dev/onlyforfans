<?php
namespace App;

use Exception;
use App\SluggableTraits;
use App\Interfaces\Ownable;
use App\Interfaces\Guidable;
use App\Interfaces\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vaultfolder extends BaseModel implements Guidable, Sluggable, Ownable
{
    use SluggableTraits;
    use HasFactory;

    protected $guarded = ['id','created_at','updated_at'];
    public static $vrules = [ ];
    protected $appends = [
        'name',
        //'vfparent',
        //'vfchildren',
        //'mediafiles',
    ];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function vault() {
        return $this->belongsTo('App\Vault');
    }
    public function mediafiles() {
        return $this->morphMany('App\Mediafile', 'resource');
    }
    public function vfparent() {
        return $this->belongsTo('App\Vaultfolder', 'parent_id');
    }
    public function vfchildren() {
        return $this->hasMany('App\Vaultfolder', 'parent_id');
    }
    public function sharees() {
        return $this->morphToMany('App\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id');
    }

    // %%% --- Implement Ownable Interface ---

    public function getOwner() : ?User {
        return $this->vault->getOwner();
    }

    //--------------------------------------------
    // %%% Accessors/Mutators
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
        'meta' => 'array',
    ];
    
    public function getBreadcrumb() : array {
        $MAX_DEPTH = 15;
        $iter = 0;

        $breadcrumb = [];
        $vf = Vaultfolder::find($this->id);
        while ( !empty($vf) ) {
            if ($iter++ > $MAX_DEPTH) {
                throw new Exception('Exceeded max sub-folder depth');
            }
            array_unshift($breadcrumb, [
                'pkid' => $vf->id,
                'vfname' => $vf->vfname,
                'slug' => $vf->slug,
            ]);
            $vf = !empty($vf->parent_id) ? Vaultfolder::find($vf->parent_id) : null;
        }
        return $breadcrumb;
    }

    public function getNameAttribute($value) {
        return $this->vfname;
    }

    public function getPathAttribute($value) {
        return $this->vfname; // %TODO: get full path back to root
    }

    //--------------------------------------------
    // Scopes
    //--------------------------------------------

    public function scopeIsRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeIsChildOf($query, Vaultfolder $vf)
    {
        return $query->where('parent_id', $vf-id);
    }

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Implement Sluggable Interface ---

    public function sluggableFields() : array {
        return ['vfname'];
    }

    // %%% --- Overrides in Model Traits (via BaseModel) ---

    public static function _renderFieldKey(string $key) : string
    {
        $key = trim($key);
        switch ($key) {
            default:
                $key =  parent::_renderFieldKey($key);
        }
        return $key;
    }

    public function renderField(string $field) : ?string
    {
        $key = trim($field);
        switch ($key) {
            /*
            case 'meta':
            case 'cattrs':
                return json_encode($this->{$key});
             */
            default:
                return parent::renderField($field);
        }
    }

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName() : string {
        return $this->vfname;
    }

    // %%% --- Other ---

    public function isRootFolder() : bool {
        return empty($this->parent_id);
    }

}
