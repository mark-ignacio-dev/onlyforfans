<?php

namespace App\Models;

use Money\Money;
use Carbon\Carbon;
use InvalidArgumentException;
use App\Models\Traits\UsesUuid;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use Illuminate\Support\Collection;
use App\Enums\Financial\AccountTypeEnum;
use App\Models\Casts\Money as CastsMoney;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Exceptions\InvalidCastException;
use App\Enums\Financial\TransactionTypeEnum;
use App\Interfaces\Ownable;
use App\Models\Financial\Traits\HasCurrency;
use App\Models\Traits\OwnableTraits;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Exceptions\InvalidIntervalException;

/**
 * Subscription model
 *
 * ===== Properties ========================================================== *
 * @property string $id
 * @property string $subscribable_id    - Id of subscribable item
 * @property string $subscribable_type  - Type of subscribable item
 * @property string $user_id            - User subscribing to item
 * @property string $account_id         - Id of account being charged
 * @property bool   $manual_charge      - If the application needs to take care of the charges or if it is done
 *                                          automatically by payment processor
 * @property string $period             - Period unit, e.i Daily, Monthly, Yearly
 * @property int    $period_interval    - Period Interval, 15 Daily would be every 15 days, 1 Monthly is every month
 * @property Money  $price              - Price of the subscription
 * @property string $currency           - Currency of the subscription price
 * @property bool   $active             - If the subscription is currently active
 * @property Carbon $canceled_at        - When the subscription was canceled
 * @property string $access_level       - Access level of this subscription
 * @property array  $custom_attributes
 * @property array  $metadata
 * @property string $last_transaction_id - The id of the last transaction for this subscription
 * @property Carbon $next_payment_at    - Timestamp of when the next payment transaction is due to occur.
 * @property Carbon $last_payment_at    - Timestamp of when the last payment transaction occurred.
 *
 * ===== Relations =========================================================== *
 * @property User         $user
 * @property Subscribable $subscribable
 * @property Account      $account
 *
 * ===== Scopes ============================================================== *
 * @method static Builder active()
 * @method static Builder canceled()
 * @method static Builder due()
 * @method static Builder inactive()
 *
 * @package App\Models
 */
class Subscription extends Model implements Ownable
{
    use UsesUuid,
        HasCurrency,
        SoftDeletes,
        HasCurrency,
        OwnableTraits;

    protected $table = 'subscriptions';

    protected $guarded = [];

    protected $dates = [
        'next_payment_at',
        'last_payment_at',
    ];

    protected $casts = [
        'price' => CastsMoney::class,
    ];

    /* --------------------------- Relationships ---------------------------- */
    #region Relationships
    public function subscribable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    #endregion Relationships

    /* ------------------------------- Scopes ------------------------------- */
    #region Scopes
    /**
     * Only return active subscriptions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Only return canceled subscriptions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeCanceled($query)
    {
        return $query->whereNotNull('canceled_at');
    }

    /**
     * Return only due subscriptions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeDue($query)
    {
        return $query->where('next_payment_at', '<=', Carbon::now());
    }

    /**
     * Only return inactive subscriptions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    #endregion Scopes


    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    /**
     * Cancels an active subscription
     *
     * @return void
     */
    public function cancel()
    {
        // Cancel Payments
        // if ( is segpay subscription ) {
        //     Call SRS cancel account
        // }

        // Check if passed next bill date
        $this->canceled_at = Carbon::now();
        if ($this->isDue()) {
            $this->subscribable->revokeAccess($this->user);
            $this->active = false;
            $this->save();
            return;
        }
        $this->save();
        return;
    }

    /**
     * Process a subscription transaction
     *
     * @param $force - If true, will ignore time sense last payment and period
     * @return Collection|bool
     */
    public function process($force = false, $options = [])
    {
        if ($force === false && !$this->isDue()) {
            return false;
        }

        if ($this->manual_charge) {
            $this->processManual();
        }

        if ($this->account->type === AccountTypeEnum::IN) {
            $this->account->moveToInternal($this->price);
            $transactions = $this->account->getInternalAccount()->moveTo(
                $this->subscribable->getOwnerAccount($this->account->system, $this->account->currency),
                $this->price,
                [
                    'ignoreBalance'    => true,
                    'type'             => TransactionTypeEnum::SUBSCRIPTION,
                    'purchasable_type' => $this->subscribable->getMorphString(),
                    'purchasable_id'   => $this->subscribable->getKey(),
                    'metadata'         => ['subscription' => $this->getKey()],
                ]
            );
            $this->last_transaction_id = $transactions['debit']->getKey();
            $this->last_payment_at = Carbon::now();
            $this->updateNextPayment();
            $this->active = true;
            $this->save();

            // Grant access
            $this->subscribable->grantAccess(
                $this->getOwner()->first(),
                $this->access_level,
                $options['access_cattrs'] ?? [],
                $options['access_meta'] ?? [],
            );

            return $transactions;
        } else if ($this->account->type === AccountTypeEnum::INTERNAL) {
            $transactions = $this->account->moveTo(
                $this->subscribable->getOwnerAccount($this->account->system, $this->account->currency),
                $this->price,
                [
                    'type'             => TransactionTypeEnum::SUBSCRIPTION,
                    'purchasable_type' => $this->subscribable->getMorphString(),
                    'purchasable_id'   => $this->subscribable->getKey(),
                    'metadata'         => ['subscription' => $this->getKey()],
                ]
            );
            $this->last_transaction_id = $transactions['debit'];
            $this->last_payment_at = Carbon::now();
            $this->updateNextPayment();
            $this->active = true;
            $this->save();

            // Grant access
            $this->subscribable->grantAccess(
                $this->getOwner()->first(),
                $this->access_level,
                $options['access_cattrs'] ?? [],
                $options['access_meta'] ?? [],
            );

            return $transactions;
        }
        return false;


    }

    /**
     * Process a subscription manually
     *
     * @return
     */
    public function processManual()
    {
        // TODO: kick off payment processing
    }

    /**
     * Updates the next_payment_at and saves
     *
     * @return void
     */
    public function updateNextPayment()
    {
        if (isset($this->next_payment_at) === false) {
            $this->next_payment_at = Carbon::now();
        }
        $this->next_payment_at = $this->next_payment_at->add($this->period, $this->period_interval);
        $this->save();
    }

    #endregion Functions

    /* ------------------------------- Ownable ------------------------------ */
    #region Ownable

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->user ]);
    }

    #endregion Ownable


    /* ---------------------------- Verifications --------------------------- */
    #region Verifications

    /**
     * Checks if the subscription is due to be renewed
     * @return bool
     */
    public function isDue(): bool
    {
        if (isset($this->next_payment_at) === false) {
            return true;
        }
        return $this->next_payment_at->greaterThanOrEqualTo(Carbon::now());
    }

    #endregion Verification

}
