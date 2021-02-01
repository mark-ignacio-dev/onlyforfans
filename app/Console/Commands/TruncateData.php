<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App;
use DB;
use Exception;
use App\Mediafile;

class TruncateData extends Command
{
    protected $signature = 'truncate:data';

    protected $description = 'Development-only script to truncate selected DB tables pre-seeding';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $isEnvLocal = App::environment(['local']);
        $dbName = env('DB_DATABASE');
        $this->info( '%%% DB Name: '.$dbName.', Is env local?: '.($isEnvLocal?'true':'false') );
        if ( $dbName !== 'fansplat_dev' && !$isEnvLocal ) {
            throw new Exception('Environment not in whitelist: '.App::environment());
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // disable
        foreach ( self::$truncateList as $t ) {
            $this->info( ' - Truncate "'.$t.'"...');
            switch ($t) {
                case 'mediafiles':
                    $mediafiles = Mediafile::get();
                    $mediafiles->each( function($mf) {
                        $this->removeMediafile($mf);
                    });
                    break;
                default:
                    DB::table($t)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // enable

        /*
         */
    }

    private function removeMediafile($mf) {
        Storage::disk('s3')->delete($mf->filename); // Remove from S3
        $mf->delete();
    }

    private static $truncateList = [
        'role_user',
        'permission_role',
        'shareables',
        'likeables',
        'comments',
        'mediafiles',
        'stories',
        //'subscriptions',

        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'user_settings',
        'permissions',
        'roles',

        'vaultfolders',
        'vaults',

        'posts',
        'timelines',
        'users',

        'username_rules',

    ];
}
