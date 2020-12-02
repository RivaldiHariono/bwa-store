<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Cart;
use App\Transaction;
use App\TransactionDetail;

use Exception;

use Midtrans\Snap;
use Midtrans\Config;



class CheckoutController extends Controller
{
    public function process(Request $request){
        //Save Users Data
        $user = Auth::user();
        $user->update($request->except('total_price'));

        //Process Checkout
        $code = 'Store-' . mt_rand(0000,9999);
        $carts = Cart::with(['product','user'])
                ->where('users_id', $user->id)
                ->get();

        //Transaction Create
        $transaction = Transaction::create([
            'users_id' => $user->id,
            'insurance_price' => 0,
            'shipping_price' => 0,
            'total_price' => $request->total_price,
            'transaction_status' => 'PENDING',
            'code' => $code,
        ]);


        foreach($carts as $cart){
            $trx = 'Trx-' . mt_rand(0000,9999);

            TransactionDetail::create([
            'transactions_id' => $transaction->id,
            'products_id' => $cart->product->id,
            'price' => $cart->product->price,
            'shipping_status' => 'PENDING',
            'resi' => '',
            'code' => $trx,
        ]);

        }

        //delete cart data
        /*Cart::with(['product','user'])
                ->where('users_id',Auth::user()->id)
                ->delete();*/
        Cart::with(['product','user'])->delete();



        //konfigurasi midtrans
        // Set your Merchant Server Key
        Config::$serverKey = config('service.midtrans.ServerKey');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = config('service.midtrans.isProduction');
        // Set sanitization on (default)
        Config::$isSanitized = config('service.midtrans.isSanitized');
        // Set 3DS transaction for credit card to true
        Config::$is3ds = config('service.midtrans.is3ds');

        //Array to send to midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $code,
                'gross_amount' => (int) $request->total_price,
            ],
            'customer_details' =>[
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'enabled_payments' => [
                'gopay', 'permata_va', 'bca_va'
            ],
            'vtweb' => []
            
            ];

            try {
                // Get Snap Payment Page URL
                $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;
            
                // Redirect to Snap Payment Page
                return redirect($paymentUrl);
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }

    }

    public function callback(Request $request){
        
    }
}
