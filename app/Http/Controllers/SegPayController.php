<?php

namespace App\Http\Controllers;

use App\Enums\Financial\AccountTypeEnum;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Enums\PaymentTypeEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Helpers\Tippable as TippableHelpers;
use App\Helpers\Purchasable as PurchasableHelpers;
use App\Helpers\Subscribable as SubscribableHelpers;
use App\Interfaces\Subscribable;
use App\Jobs\FakeSegpayPayment;
use App\Models\Financial\Account;
use App\Models\Financial\SegpayCard;
use App\Rules\InEnum;

class SegPayController extends Controller
{
    /**
     * Generate the pay page Url
     * @return string
     */
    public function generatePayPageUrl(Request $request)
    {
        if (isset($request->item)) {
            $item = PurchasableHelpers::getPurchasableItem($request->item);
            $price = $item->formatMoneyDecimal($item->price);
        } else {
            $price = $request->price;
        }

        if (!isset($price)) {
            abort(400, 'Price or item is required');
        }

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.dynamicTransUrl'), [
            'query' => [ 'value' => $price, ],
        ]);

        $priceEncode = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA)->__toString();

        $baseUrl = Config::get('segpay.secure') ? 'https://' : 'http://';
        $baseUrl .= Config::get('segpay.baseUrl');
        $packageId = Config::get('segpay.packageId');
        $pricePointId = Config::get('segpay.pricePointId');
        $appName = Config::get('app.name');
        $env = Config::get('app.env');

        $xEticketid = "{$packageId}:{$pricePointId}";
        $description = urlencode('All Fans Purchase');
        $save = ($request->save) ? '1' : '0';
        $userId = Auth::user()->getKey();

        $url = "{$baseUrl}?x-eticketid={$xEticketid}&amount={$price}&dynamictrans={$priceEncode}&dynamicdesc={$description}&app={$appName}&env={$env}&user_id={$userId}&save={$save}&type=purchase";
        if (isset($item)) {
            $url .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }

        // Generate Hash signature
        $secret = Config::get('segpay.secret');
        $body = "&app={$appName}&env={$env}&price={$price}&user_id={$userId}&save={$save}&type=purchase";
        if (isset($item)) {
            $body .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }
        $hash = hash_hmac('sha256', $body, $secret, false);
        $url .= "&REF1={$hash}";

        return $url;
    }

    /**
     * Generate the pay page url for a tip
     * @param Request $request
     * @return string
     */
    public function generateTipPayPageUrl(Request $request)
    {
        if (isset($request->item)) {
            $item = TippableHelpers::getTippableItem($request->item);
            $price = $item->formatMoneyDecimal($item->price);
        } else {
            $price = $request->price;
        }

        if (!isset($price)) {
            abort(400, 'Price or item is required');
        }

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.dynamicTransUrl'), [
            'query' => ['value' => $price,],
        ]);

        $priceEncode = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA)->__toString();

        $baseUrl = Config::get('segpay.secure') ? 'https://' : 'http://';
        $baseUrl .= Config::get('segpay.baseUrl');
        $packageId = Config::get('segpay.packageId');
        $pricePointId = Config::get('segpay.pricePointId');
        $appName = Config::get('app.name');
        $env = Config::get('app.env');

        $xEticketid = "{$packageId}:{$pricePointId}";
        $description = urlencode('All Fans Tip');
        $save = ($request->save) ? '1' : '0';
        $userId = Auth::user()->getKey();

        $url = "{$baseUrl}?x-eticketid={$xEticketid}&amount={$price}&dynamictrans={$priceEncode}&dynamicdesc={$description}&app={$appName}&env={$env}&user_id={$userId}&save={$save}&type=tip";
        if (isset($item)) {
            $url .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }

        // Generate Hash signature
        $secret = Config::get('segpay.secret');
        $body = "&app={$appName}&env={$env}&price={$price}&user_id={$userId}&save={$save}&type=tip";
        if (isset($item)) {
            $body .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }
        $hash = hash_hmac('sha256', $body, $secret, false);
        $url .= "&REF1={$hash}";

        return $url;
    }


    public function getPaymentSession(Request $request)
    {
        $request->validate([
            'item' => 'required|uuid',
            'type' => [ 'required', new InEnum(new PaymentTypeEnum())],
            'price' => 'required',
        ]);

        // Get payment item
        if ($request->type === PaymentTypeEnum::PURCHASE) {
            $item = PurchasableHelpers::getPurchasableItem($request->item);
            $description = 'All Fans Purchase';
        } else if ($request->type === PaymentTypeEnum::TIP) {
            $item = TippableHelpers::getTippableItem($request->item);
            $description = 'All Fans Tip';
        } else if ($request->type === PaymentTypeEnum::SUBSCRIPTION) {
            $item = SubscribableHelpers::getSubscribableItem($request->item);
            $description = 'All Fans Subscription';
        }

        if (!isset($item)) {
            abort(400, 'Bad type or item');
        }

        // Validate Price
        if (!$item->verifyPrice($request->price)) {
            abort(400, 'Invalid Price');
        }

        // If environment variable is set, fake results
        if (Config::get('segpay.fake') === true && Config::get('app.env') !== 'production') {
            return [
                'id' => 'faked',
                'pageId' => 'faked',
                'expirationDatTime' => Carbon::now()->addHour(),
            ];
        }

        // Get payment session
        $query = [
            'tokenId' => Config::get('segpay.paymentSessions.token'),
            'dynamicDescription' => urlencode($description),
            'dynamicInitialAmount' => $item->formatMoneyDecimal($request->price),
        ];

        if ($request->type === PaymentTypeEnum::SUBSCRIPTION) {
            $query = array_merge($query, [
                'dynamicInitialDurationInDays'   => 30,
                'dynamicRecurringAmount'         => $item->formatMoneyDecimal($request->price),
                'dynamicRecurringDurationInDays' => 30,
            ]);
        }

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.paymentSessions.baseUrl'), [
            'query' => $query,
        ]);

        return $response;
    }

    /**
     * Fake a segpay purchase if system allows segpay fakes
     *
     * @param Request $request
     * @return void
     */
    public function fake(Request $request)
    {
        if (Config::get('app.env') === 'production' || Config::get('segpay.fake') === false) {
            abort(403);
        }

        $request->validate([
            'item'     => 'required|uuid',
            'type'     => [ 'required', new InEnum(new PaymentTypeEnum()) ],
            'price'    => 'required',
            'currency' => 'required',
            'last_4'   => 'required',
        ]);

        // Get payment item
        if ($request->type === PaymentTypeEnum::PURCHASE) {
            $item = PurchasableHelpers::getPurchasableItem($request->item);
        } else if ($request->type === PaymentTypeEnum::TIP) {
            $item = TippableHelpers::getTippableItem($request->item);
        } else if ($request->type === PaymentTypeEnum::SUBSCRIPTION) {
            $item = SubscribableHelpers::getSubscribableItem($request->item);
        }

        if (!isset($item)) {
            abort(400, 'Bad type or item');
        }

        // Validate Price
        if (!$item->verifyPrice($request->price)) {
            abort(400, 'Invalid Price');
        }

        // Create Card
        $user = Auth::user();
        $card = SegpayCard::create([
            'owner_type' => $user->getMorphString(),
            'owner_id'   => $user->getKey(),
            'token'      => 'fake',
            'nickname'   => $request->nickname ?? 'Fake Card',
            'card_type'  => $request->brand ?? '',
            'last_4'     => $request->last_4 ?? '0000',
        ]);

        // Create account for card
        $account = Account::create([
            'system' => 'segpay',
            'owner_type' => $user->getMorphString(),
            'owner_id' => $user->getKey(),
            'name' => $request->nickname ?? 'Fake Card',
            'type' => AccountTypeEnum::IN,
            'currency' => $request->currency,
            'resource_type' => $card->getMorphString(),
            'resource_id' => $card->getKey(),
        ]);
        $account->verified = true;
        $account->can_make_transactions = true;
        $account->save();

        // Dispatch Event
        FakeSegpayPayment::dispatch($item, $account, $request->type, $request->price);
    }

}
