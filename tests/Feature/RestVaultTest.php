<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Mediafile;
use App\Timeline;
use App\User;
use App\Vault;
use App\Vaultfolder;
use App\Enums\MediafileTypeEnum;

class RestVaultTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group vault
     *  @group regression
     *  @group this
     */
    public function test_can_index_all_of_my_vaultfolders()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $firstVault = $creator->vaults()->first();

        $payload = [
            'filters' => [
                'vault_id' => $firstVault->id,
            ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultfolders.index'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolders);
        $vaultfoldersR = collect($content->vaultfolders);
        $this->assertGreaterThan(0, $vaultfoldersR->count());

        $nonOwned = $vaultfoldersR->filter( function($vf) use(&$firstVault) {
            return $firstVault->id !== $vf->vault_id; // %FIXME: impl dependency
        });
        $this->assertEquals(0, $nonOwned->count(), 'Returned a vaultfolder that does not belong to creator');
        $expectedCount = Vaultfolder::where('vault_id', $firstVault->id)->count(); // %FIXME scope
        $this->assertEquals($expectedCount, $vaultfoldersR->count(), 'Number of vaultfolders returned does not match expected value');
    }

    /**
     *  @group vault
     *  @group regression
     *  @group this
     */
    public function test_can_index_my_root_level_vaultfolders()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $firstVault = $creator->vaults()->first();

        $payload = [
            'filters' => [
                'vault_id' => $firstVault->id,
                'parent_id' => 'root',
            ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultfolders.index'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolders);
        $vaultfoldersR = collect($content->vaultfolders);
        $this->assertGreaterThan(0, $vaultfoldersR->count());

        $nonRoot = $vaultfoldersR->filter( function($vf) {
            return $vf->parent_id !== null;
        });
        $this->assertEquals(0, $nonRoot->count(), 'Returned a vaultfolder not in root folder');

        $expectedCount = Vaultfolder::where('vault_id', $firstVault->id)
            ->whereNull('parent_id')
            ->count(); // %FIXME scope
        $this->assertEquals($expectedCount, $vaultfoldersR->count(), 'Number of vaultfolders returned does not match expected value');
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

