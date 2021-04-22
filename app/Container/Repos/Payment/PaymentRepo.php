<?php

namespace Repos\Payment;

use Contracts\Payment\PaymentContract;
use App\Order;
use App\paylane\PayLaneRestClient;
use Illuminate\Http\Request;
use App\User;
use App\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendMail;
use Cartalyst\Stripe\Stripe;

class PaymentRepo implements PaymentContract
{
    public function __construct(Order $order, User $user, Subscription $subscription)
    {
        $this->order = $order;
        $this->user = $user;
        $this->subscription = $subscription;
        $this->username = env('PayLane_API');
        $this->password = env('PayLane_PASSWORD');
    }

    public function cardParams($order, $card, $request)
    {
        $client = new PayLaneRestClient($this->username, $this->password);

        $card_params = [
            'sale'   => [
                'amount'      => (float) $order->amount,
                'currency'    => $order->currency,
                'description' => $order->description,
            ],
            'customer' => [
                'name'    => $order->user->name,
                'email'   => $order->user->email,
                'ip'      => $_SERVER['REMOTE_ADDR'],

            ],
            'card' => [
                'card_number' => $request->card_number,
                'expiration_month' => $card->expiration_month,
                'expiration_year' => $card->expiration_year,
                'name_on_card' =>  $card->name_on_card,
                'card_code' => $card->card_code,
            ],
        ];

        try {
            $status = $client->cardSale($card_params);
        } catch (Exception $e) {
            // handle exceptions here
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $status['error']['error_description']]);
        }

        // checking transaction status:
        if ($client->isSuccess()) {
            $id_sale = $status['id_sale'];
            $order->update(['id_sale' => $id_sale, 'status' => $status['success']]);
            return $status;
        } else {
            return $status;
        }
    }


    /*public function cardParams($order, $card, $request)
    {
        $client = new PayLaneRestClient($this->username, $this->password);

        $card_params = [
            'sale'   => [
                'amount'      => (float) $order->amount,
                'currency'    => $order->currency,
                'description' => $order->description,
            ],
            'customer' => [
                'name'    => $order->user->name,
                'email'   => $order->user->email,
                'ip'      => $_SERVER['REMOTE_ADDR'],

            ],
            'card' => [
                'card_number' => $request->card_number,
                'expiration_month' => $card->expiration_month,
                'expiration_year' => $card->expiration_year,
                'name_on_card' =>  $card->name_on_card,
                'card_code' => $card->card_code,
            ],
        ];

        try {
            $status = $client->checkCard3DSecure($card_params);
            dd($status);
        } catch (Exception $e) {
            // handle exceptions here
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $status['error']['error_description']]);
        }

        //check card enrollment
        if($client->isSuccess()){
            $id_3dsecure_auth = $status['id_3dsecure_auth'];
            try {
                $status = $client->saleBy3DSecureAuthorization(array ('id_3dsecure_auth' => $id_3dsecure_auth));
            } catch (Exception $e) {
                return redirect()->back()->with(['msg-type' => 'error', 'msg' => $status['error']['error_description']]);
            }

            if ($client->isSuccess()) {
                $id_sale = $status['id_sale'];
                $order->update(['id_sale' => $id_sale, 'status' => $status['success']]);
                return $status;
            } else {
                return $status;
            }
        }else{
            return $status;
        }

        // checking transaction status:
        if ($client->isSuccess()) {
            $id_sale = $status['id_sale'];
            $order->update(['id_sale' => $id_sale, 'status' => $status['success']]);
            return $status;
        } else {
            return $status;
        }
    }*/

    public function checkout($order, $card, $request)
    {
        if($request->payment_type == "paylane")
            $status = $this->cardParams($order, $card, $request);
        if($request->payment_type == "stripe"){
            $charge = $this->stripeCardParams($order, $card, $request);
            if(!isset($charge['id'])){
                return redirect()->back()->with(['msg-type' => 'error', 'msg' => $charge]);
            }
            return redirect(route('home'));
        }
        // checking sale status:
        if (is_array($status)) {
            $subscription = $this->subscription->where('user_id', $order->user_id)->first();
            $this->user->where('id', $order->user_id)->update([
                'is_premium' => 1,
                'premium_start_date' => Carbon::now(),
                'premium_end_date' => $subscription->end_date
            ]);
            $this->sendSubscription($subscription);
            return redirect(route('home'));
        } else {
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $status['error']['error_description']]);
        }
    }

    public function validate($order, $card, $request)
    {
        if($request->payment_type == "stripe"){
            $charge = $this->stripeCardParams($order, $card, $request);
            if(!isset($charge['id'])){
                return redirect()->back()->with(['msg-type' => 'error', 'msg' => $charge]);
            }
            $charge_id = $charge['id']; 
            $amount = (float) $order->amount;
            $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
            $refund = $stripe->refunds()->create( $charge_id,$amount, ['reason' => 'duplicate']);
            $order->update(['id_sale' => $charge_id, 'status' => 'refund']);
            $subscription = $this->subscription->where('user_id', $order->user_id)->first();
            $this->sendSubscription($subscription);
            return redirect(route('home'));
        }
            
        if($request->payment_type == "paylane"){
            $cardSale = $this->cardParams($order, $card, $request);
            
            if (!(is_array($cardSale))) {
                return redirect()->back()->with(['msg-type' => 'error', 'msg' => trans('frontend.something_went_wrong')]);
            }
            if ($cardSale['success'] == false) {
                return redirect()->back()->with(['msg-type' => 'error', 'msg' => $cardSale['error']['error_description']]);
            }
            $id_first_sale = $cardSale['id_sale'];
            $client = new PayLaneRestClient($this->username, $this->password);
            $refund_params = array(
                'id_sale' => $id_first_sale,
                'amount'  => $order->amount,
                'reason'  => 'Free Trial Refund',
            );

            // perform the refund:
            try {
                $status = $client->refund($refund_params);
            } catch (Exception $e) {
                // handle exceptions here
                return redirect()->back()->with(['msg-type' => 'error', 'msg' => $cardSale['error']['error_description']]);
            }

            // checking refund status:
            if ($client->isSuccess()) {
                $id_refund = $id_first_sale;
                $order->update(['id_sale' => $id_refund, 'status' => 'refund']);
                $subscription = $this->subscription->where('user_id', $order->user_id)->first();
                $this->user->where('id', $order->user_id)->update([
                    'is_premium' => 1,
                    'premium_start_date' => Carbon::now(),
                    'premium_end_date' => $subscription->end_date
                ]);
                $this->sendSubscription($subscription);
                return redirect(route('home'));
            } else {
                return redirect()->back()->with(['msg-type' => 'error', 'msg' => $cardSale['error']['error_description']]);
            }
        }
    }

   public function sendSubscription($subscription){
        $new_array = array( 
            "name" => \Auth::user()->name,
            "next_date" => Carbon::parse($subscription->end_date)->format('d F Y')
        );

        $to_name = \Auth::user()->name;
        $to_email = \Auth::user()->email;
          
        Mail::send('frontend.subscription-email', $new_array, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
            ->subject('Tasali Subscription Confirmation');
            $message->from('tasali@tasali.media','Tasali');
        });

       //Mail::to(\Auth::user()->email)->send(new SendMail($new_array));
    }


    public function paypalParams($order)
    {
        $client = new PayLaneRestClient($this->username, $this->password);
        $paypal_params = [
            'sale'   => [
                'amount'      => (float) $order->amount,
                'currency'    => 'EUR',//$order->currency,
                'description' => 'Free trial'
            ],
            'back_url' => url('register/paypalCheckout')
        ];

        try {
            $status = $client->paypalSale($paypal_params);
        } catch (Exception $e) {
            // handle exceptions here
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $status['error']['error_description']]);
        }

        // checking transaction status:
        if ($client->isSuccess()) {
            echo "Success, id_sale: {$status['id_sale']} \n";
        } else {
            return redirect()->back()->with(['msg-type' => 'error', 'msg' =>    "Error ID: {$status['error']['id_error']}, \n"."Error number: {$status['error']['error_number']}, \n"."Error description: {$status['error']['error_description']}"]); 
        }
        
        header('Location: ' . $status['redirect_url']);
        die;

    }

    public function stripeCardParams($order, $card, $request)
    {
        $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $token = $stripe->tokens()->create([
                'card' => [
                     'number' => $request->card_number,
                     'exp_month' => $request->expiration_month,
                     'exp_year' => $request->expiration_year,
                     'cvc' => $request->card_code,
                ],
            ]);
            
            if (!isset($token['id'])) {
                return 'Invalid card credentials!';
            }
            
            $charge = $stripe->charges()->create([
                'card' => $token['id'],
                'currency' => $order->currency,
                'amount' => (float) $order->amount,
                'description' => $order->description,
            ]);

            if($charge['status'] == 'succeeded') {
                $id_sale = $charge['id'];
                $order->update(['id_sale' => $id_sale, 'status' => 'success']);
                $subscription = $this->subscription->where('user_id', $order->user_id)->first();
                $this->user->where('id', $order->user_id)->update([
                    'is_premium' => 1,
                    'premium_start_date' => Carbon::now(),
                    'premium_end_date' => $subscription->end_date
                ]);
                //$this->sendSubscription($subscription);
                //return redirect(route('home'));
                return $charge;
            } else {
                return 'Invalid card credentials!';
            }
        }catch (Exception $e) {
            return $e->getMessage();
        } catch(\Cartalyst\Stripe\Exception\CardErrorException $e) {
            return  $e->getMessage();
        } catch(\Cartalyst\Stripe\Exception\MissingParameterException $e) {
            return $e->getMessage();
        }
    }

}
