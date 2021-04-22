<?php

/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/18/2018
 * Time: 12:22 PM
 */

namespace Repos\CreditCards;


use App\CreditCards;
use Contracts\CreditCards\CreditCardContract;
use App\Order;
use App\User;
use Contracts\Payment\PaymentContract;
use Contracts\Options\OptionsContract;
use App\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendMail;
use Auth;

class CreditCardRepo implements CreditCardContract
{

    public function __construct(CreditCards $cards, Order $order, PaymentContract $payment, User $user, OptionsContract $config, Subscription $subscription)
    {
        $this->cards = $cards;
        $this->order = $order;
        $this->payment = $payment;
        $this->user = $user;
        $this->config = $config;
        $this->subscription = $subscription;
    }

    public function get($id)
    {
        return $this->cards->find($id);
    }

    public function getUser($user_id)
    {
        return $this->cards->where('user_id', $user_id)->get();
    }

    public function set($data, $user_id)
    {
        return $this->cards->create([
            'user_id'      => $user_id,
            'type'         => config('api.credit_card_types.' . substr($data->card_number, 0, 1)) ?? "",
            'name_on_card' => $data->name_on_card,
            'expiration_month'  => $data->expiration_month,
            'expiration_year'  => $data->expiration_year,
            'card_code'  => $data->card_code,
            'payment_type' => $data->payment_type,
            'card_number'  => str_repeat('*', strlen($data->card_number) - 4) . substr(
                $data->card_number,
                strlen($data->card_number) - 4
            ),
            'is_default'   => true,
            'is_primary'   => $data->is_primary ?? 0
        ]);
    }

    public function update($id, $data)
    {
        return $this->cards->where('id', $id)->update([
            'type'         => config('api.credit_card_types.' . substr($data->card_number, 0, 1)) ?? "",
            'name_on_card' => $data->name_on_card,
            'expiration_month'  => $data->expiration_month,
            'expiration_year'  => $data->expiration_year,
            'card_code'  => $data->card_code,
            'card_number'  => str_repeat('*', strlen($data->card_number) - 4) . substr(
                $data->card_number,
                strlen($data->card_number) - 4
            ),
            'is_default'   => true,
            'is_primary'   => $data->is_primary ?? 0
        ]);
    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }

    public function order($card, $user_id, $request)
    {
        $amount = Auth::user()->amount;
        //$amount = $this->config->getByName('amount');
        $subscription_duration = $this->config->getByName('subscription_duration');
        $trial = $this->config->getByName('trial');
        //$trial_amount = $this->config->getByName('trial_amount');
        $order = $this->order->create([
            'amount'      => $amount, //($trial->value == 0 || $trial->value == null) ? $amount : $amount,
            'currency'    => Auth::user()->code,
            'country'    => Auth::user()->country,
            'description' => "Free trial",
            'user_id'     => $user_id,
            'card_id'     => $card->id,
            'id_sale'     => null,
            'expiry_date' => Carbon::now()->addDays(($trial->value == 0 || $trial->value == null) ? $subscription_duration->value : $trial->value)
        ]);

        $subscription = $this->subscription->where('user_id', $user_id)->first();

        if ($subscription) {
            $subscription->update([
                'is_cancelled' => 0,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays(($trial->value == 0 || $trial->value == null) ? $subscription_duration->value : $trial->value),
                'plan_name' => 'Your next billing date at 
                '.Carbon::now()->addDays(($trial->value == 0 || $trial->value == null) ? $subscription_duration->value : $trial->value)->toDateString()
            ]);
        } else {
            $subscription = $this->subscription->create([
                'user_id' => $order->user_id,
                'card_id' => $order->card_id,
                'is_premium' => 1,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays(($trial->value == 0 || $trial->value == null) ? $subscription_duration->value : $trial->value),
                'plan_name' => ''
            ]);
           $subscription->update(['plan_name' => 'Free trial, Your next billing date at '.Carbon::parse($subscription->end_date)->toDateString()]);
           //$this->sendSubscription($subscription); 
        }

        if ($trial->value == 0 || $trial->value == null) {
            $order->update(['description' => 'Subscription for a month']);
            $subscription->update(['plan_name' => 'Subscription for a month, Your next billing date at '.Carbon::parse($subscription->end_date)->toDateString()]);
            //$this->sendSubscription($subscription);
            return $this->payment->checkout($order, $card, $request);
        }
        return $this->payment->validate($order, $card, $request);
    }

    public function unPrimaryCards($user_id)
    {
        return $this->cards->where('user_id', $user_id)->update(['is_primary' => 0]);
    }

    public function primaryCard($id)
    {
        return $this->cards->where('id', $id)->update(['is_primary' => 1]);
    }

    public function sendSubscription($subscription){
        $new_array = array( 
            "name" => \Auth::user()->name,
            "message" => 'Subscription for a month, Your next billing date at '.Carbon::parse($subscription->end_date)->toDateString(),
            "template" => "frontend.dynamic_email_template",
            "subject" => "Tasali Media Subscription"
        );
        Mail::to(\Auth::user()->email)->send(new SendMail($new_array));
    }
}
