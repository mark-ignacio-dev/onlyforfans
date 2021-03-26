<?php

namespace Tests\Unit;

use App\Enums\Financial\TransactionTypeEnum;
use Money\Money;
use Money\Currency;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Timeline;
use App\Models\Financial\Transaction;
use PHPUnit\Framework\IncompleteTestError;
use Tests\Helpers\Financial\AccountHelpers;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Purchaseable Unit Test Cases
 *
 * @group unit
 * @group financial
 * @group purchaseable
 *
 * @package Tests\Unit
 */
class PurchaseableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify price point works correctly
     * @group post
     * @return void
     */
    public function test_verify_post_price_point()
    {
        // Create Post with Price
        $post = Post::factory()->pricedAt(1000)->create();

        $this->assertTrue($post->verifyPrice(1000));
        $money = new Money(1000, new Currency('USD'));
        $this->assertTrue($post->verifyPrice($money));

        $this->assertFalse($post->verifyPrice(800));
        $money = new Money(800, new Currency('USD'));
        $this->assertFalse($post->verifyPrice($money));
    }

    /**
     * Can purchase post
     * @group post
     * @depends test_verify_post_price_point
     * @return void
     */
    public function test_can_purchase_post()
    {
        $post = Post::factory()->pricedAt(1000)->create();
        $purchaserAccounts = AccountHelpers::loadWallet(1000);

        $purchaserAccounts['internal']->purchase($post, 1000);

        $this->assertDatabaseHas(app(Transaction::class)->getTable(), [
            'account_id' => $purchaserAccounts['internal']->getKey(),
            'debit_amount' => 1000,
            'type' => TransactionTypeEnum::SALE,
            'purchasable_id' => $post->getKey(),
            'purchasable_type' => $post->getMorphString(),
        ]);
    }

    /**
     * Chargeback disables access to post
     * @group post
     * @depends test_can_purchase_post
     * @return void
     */
    public function test_chargeback_disables_post_access()
    {
        $this->markTestIncomplete();
    }

}