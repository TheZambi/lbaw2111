<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\DB;
use App\Models\Address;
use App\Models\Shipping;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $this->authorize('checkout', Auth::user());

        $step = request()->query("step");
        if($step != null && $step != 1 && $step != 2 && $step != 3) {
            return redirect('/checkout');
        } else if($step == null) {
            $request->session()->pull('billing');
            $request->session()->pull('shipping');
        }

        if($step == 1) { // addresses
            $request->session()->pull('billing');
            $request->session()->pull('shipping');

            $request->session()->forget('shipping_option');

            $categories = Category::all()->sortBy("category_id");
            $countries = Country::all();
            return view('pages.checkout')->with("categories", $categories)->with("step", $step)->with("countries", $countries);
        }
        else if($step == 2) { // shipping

            if(!$request->session()->has('billing') || !$request->session()->has('shipping'))
                return redirect()->route('checkout')->with('error', 'Billing and shipping addresses must be correctly set before choosing a shipping option.');
            
            $categories = Category::all()->sortBy("category_id");
            $shipping_options = Shipping::all();
            return view('pages.checkout')->with("categories", $categories)->with("step", $step)->with("shipping_options", $shipping_options);
  
        } else if($step == 3) { // payment

            if(!$request->session()->has('billing') || !$request->session()->has('shipping'))
                return redirect()->route('checkout')->with('error', 'Billing and shipping addresses must be correctly set before payment.');
            
            if(!$request->session()->has('shipping_option')) {
                return redirect()->route('checkout')->with('error', 'Shipping option must be correctly set before payment.');
            }

            $shipping_option = Shipping::find(session('shipping_option'));

            if($shipping_option == NULL) {
                return redirect()->route('checkout')->with('error', 'Shipping option must be correctly set before payment.');
            }

            $categories = Category::all()->sortBy("category_id");
            return view('pages.checkout')->with("categories", $categories)->with("step", $step)->with("shipping_option", $shipping_option);
        }

        $categories = Category::all()->sortBy("category_id");
        
        return view('pages.checkout')->with("categories", $categories)->with("step", $step);
    }

    public function getAddressForm($type)
    {
        if($type != "shipping" && $type != "billing") {
            return response()->json("Invalid address type", 400);
        }
        return view("partials.checkoutAddressForm")->with("addressType", $type)->with("countries", Country::all());
    }

    public function getAddressInfo($type)
    {
        if($type != "shipping" && $type != "billing") {
            return response()->json("Invalid address type", 400);
        }
        return view("partials.checkoutAddressInfo")->with("addressType", $type);
    }

    public function toAddresses() {

        $this->authorize('checkout', Auth::user());
        return redirect('/checkout?step=1');
    }

    public function toShipping(Request $request) {

        $this->authorize('checkout', Auth::user());

        $post = $request->post();
        $user = Auth::user();

        $billing = [];
        $shipping = [];

        
        if(isset($post['useDefinedBilling'])) {
            if($user->billingAddress()->count() == 0) {
                return back()->with('error', 'User does not have any billing address defined')->withInput();

            } else {
                $billing = ['created' => false,
                            'address_id' => $user->billingAddress()['address_id']
                            ];
            } 
        } else {
            $street = $post['billingStreetFormCheckout'];
            $city = $post['billingCityFormCheckout'];
            $country_id = $post['billingCountryFormCheckout'];
            $zip_code = $post['billingZipcodeFormCheckout'];

            if($street == NULL || $city == NULL || $country_id == NULL || $zip_code == NULL) {
                return back()->with('error', 'Invalid billing address! Fields missing')->withInput();
            }

            $billing = ['created' => true,
                        'street' => $street,
                        'city' => $city,
                        'country_id' => $country_id,
                        'zip_code' => $zip_code,
                        ];
        }

        if($post['shippingUse'] == "defined") {

            if($user->shippingAddress()->count() == 0) {
                return back()->with('error', 'User does not have any shipping address defined')->withInput();

            } else {
                $shipping = ['created' => false,
                            'address_id' => $user->shippingAddress()['address_id']
                            ];
            }
         
        } else if($post['shippingUse'] == 'equal') {
            $shipping = $billing;

        } else if($post['shippingUse'] == 'other') {
            $street = $post['shippingStreetFormCheckout'];
            $city = $post['shippingCityFormCheckout'];
            $country_id = $post['shippingCountryFormCheckout'];
            $zip_code = $post['shippingZipcodeFormCheckout'];

            if($street == NULL || $city == NULL || $country_id == NULL || $zip_code == NULL) {
                return back()->with('error', 'Invalid shipping address! Fields missing')->withInput($request->input());
            }

            $shipping = ['created' => true,
                        'street' => $street,
                        'city' => $city,
                        'country_id' => $country_id,
                        'zip_code' => $zip_code,
                        ];
        }

        $request->session()->put('billing', $billing);
        $request->session()->put('shipping', $shipping);

        return redirect('/checkout?step=2');
    }

    public function toPayment(Request $request) {

        $user = Auth::user();
        $this->authorize('checkout', $user);

        $post = $request->post();
        
        if($post['shippingMethod'] == null)
            return back()->with('error', 'Shipping method not selected.');

        $shipping_id = $post['shippingMethod'];

        if( !is_numeric($shipping_id) || (Shipping::find($shipping_id) == NULL)) {
             return back()->with('error', 'Invalid shipping option.');
        }

        session()->put('shipping_option', $shipping_id);

        return redirect('/checkout?step=3');
    }

    public function finishCheckout(Request $request) {
        $user = Auth::user();
        $this->authorize('checkout', Auth::user());

        if(!$request->session()->has('shipping') || !$request->session()->has('shipping') || !$request->session()->has('shipping_option')) {
            return redirect()->route('checkout')->with('error', 'Invalid checkout information. Could not proceed to payment.');
        }

        $post = $request->post();
        if($post['finish'] == "Balance") {
            // TODO: pay with balance
            return $this->payBalance($user, session('shipping'), session('billing'), session('shipping_option'));
            
        } else if($post['finish'] == "Paypal") {
            // TODO: pay with paypal
            return $this->payPaypal($user, session('shipping_option'));
        } else {
            return redirect()->route('checkout')->with('error', 'Invalid payment option.');
        }
    }

    private function processCheckout($user, $shipping, $billing, $shipping_option) {
        $billing_id = $this->getAddressId($billing);
        $shipping_id = $this->getAddressId($shipping);

        session()->forget('checkout_id');
        session()->forget('shipping');
        session()->forget('billing');
        session()->forget('shipping_option');

        DB::select('call checkout(?, ?, ?, ?)', [$user["user_id"], $billing_id, $shipping_id, $shipping_option]);

        DB::table('cart')->where('user_id', $user["user_id"])->delete();
    }

    private function getAddressId($address_session) {
        if($address_session['created']) {
            DB::table('address')
            ->insert(array('city' => $address_session['city'], 'street' => $address_session['street'],
            'zip_code' => $address_session['zip_code'], 'country_id' => $address_session['country_id']));

            return DB::getPdo()->lastInsertId();
        } else {
            $current_address = DB::select("SELECT * FROM address WHERE address_id = ?", array($address_session['address_id']))[0];
            DB::table('address')
            ->insert(array('city' => $current_address->city, 'street' => $current_address->street,
            'zip_code' => $current_address->zip_code, 'country_id' => $current_address->country_id));

            return DB::getPdo()->lastInsertId();
        }
    }

    private function payBalance($user, $shipping, $billing, $shipping_option) {
        $result = DB::transaction(function () use($user, $shipping, $billing, $shipping_option) {
            $sum_prices = $user->cartTotalShipping($shipping_option);

            $sum_prices = floatval(preg_replace('/[^\d\.]/', '', $sum_prices)); // parse money

            $currentBalance = floatval(preg_replace('/[^\d\.]/', '', $user['balance'])); // parse money

            if($currentBalance >= $sum_prices) {
                $this->processCheckout($user, $shipping, $billing, $shipping_option);
                return 0;
            }
            return -1;
        });

        if($result == 0) {
            return redirect('/userProfile/'.$user['user_id'].'/purchaseHistory')->with('checkout_success', 'Checkout successful.');
        } else {
            return redirect('/checkout')->with('checkout_error', 'You do not have enough balance.');
        }
    }

    private function payPaypal($user, $shipping_option) {
        $sum_prices = DB::transaction(function () use($user, $shipping_option) {
            $sum_prices = $user->cartTotalShipping($shipping_option);

            $sum_prices = floatval(preg_replace('/[^\d\.]/', '', $sum_prices)); // parse money
            return $sum_prices;
        });

        $provider = \PayPal::setProvider();
        $provider->setApiCredentials(config('paypal'));
        $provider->setAccessToken($provider->getAccessToken());

        $order = $provider->createOrder([
            "intent"=> "CAPTURE",
            "purchase_units"=> [
                0 => [
                    "amount"=> [
                        "currency_code"=> "USD",
                        "value"=> $sum_prices
                    ]
                ]
                    ],
            "application_context" => [
                'shipping_preference'=> 'NO_SHIPPING',
                'brand_name' => 'Fneuc Shop',
                'return_url' => 'http://lbaw2111.lbaw-prod.fe.up.pt/checkout/capture'
                ]
          ]);

        session(['checkout_id' => $order['id']]);
        return redirect($order['links'][1]['href']);
    }

    public function finishPaypal(Request $request) {
        $user = Auth::user();
        $this->authorize('checkout', $user);

        $order_id = $request->query('token');

        if($order_id == null || session('checkout_id') == null || $order_id != session('checkout_id')) {
            abort(403, 'Expired order.');
        }

        if(!$request->session()->has('shipping') || !$request->session()->has('billing') || !$request->session()->has('shipping_option')) {
            abort(403, 'Expired order.');
        }

        $provider = \PayPal::setProvider();
        $provider->setApiCredentials(config('paypal'));
        $provider->setAccessToken($provider->getAccessToken());
        $capture = $provider->capturePaymentOrder(session('checkout_id'));

        if(array_key_exists("type",$capture)) {
            if($capture['type'] === 'error') {
                return redirect('/checkout')->with('checkout_error', 'It appears that there has been a problem with the payment. Please try again.');
            }
        } else if($capture['status'] === 'COMPLETED') {

            DB::transaction(function () use($user, $capture) {
                $this->addBalanceValue($user["user_id"], $capture['purchase_units'][0]['payments']['captures'][0]['amount']['value']);
                $this->processCheckout($user, session('shipping'), session('billing'), session('shipping_option'));
            });
            return redirect('/userProfile/'.$user['user_id'].'/purchaseHistory')->with('checkout_success', 'Checkout successful.');
        } 

    }

    public function addBalanceValue($id, $value) {
        $user = User::findOrFail($id);
        
        $currentBalance = floatval(preg_replace('/[^\d\.]/', '', $user['balance'])); // parse money
        $newBalance = $currentBalance + floatval($value);

        DB::table('users')
                ->where('user_id', $user['user_id'])
                ->update(['balance' => $newBalance]);
    }
}
