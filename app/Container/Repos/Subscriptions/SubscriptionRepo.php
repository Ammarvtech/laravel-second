<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/15/2018
 * Time: 1:14 PM
 */

namespace Repos\Subscriptions;


use App\Subscription;
use Contracts\Subscriptions\SubscriptionContract;

class SubscriptionRepo implements SubscriptionContract
{

    public function __construct(Subscription $subscription)
    {
        $this->subscribe = $subscription;
    }

    public function set($user, $freeSignUp = false)
    {
        $this->subscribe->card_id    = @$user->primaryCreditCards()->first()->id;
        $this->subscribe->start_date = $user->premium_start_date;
        $this->subscribe->end_date   = $user->premium_end_date;
        $this->subscribe->is_premium = ( ! $freeSignUp) ? true : false;
        $this->subscribe->is_alerted = ( ! $freeSignUp) ? true : false;
        $this->subscribe->plan_name  = $data->plan_name ?? "";


        return $user->subscriptions()->save($this->subscribe);
    }

    public function update($user, $freeSignUp = false)
    {
        $this->subscribe->card_id    = $user->primaryCreditCards->id;
        $this->subscribe->start_date = $user->premium_start_date;
        $this->subscribe->end_date   = $user->premium_end_date;
        $this->subscribe->is_premium = ( ! $freeSignUp) ? true : false;
        $this->subscribe->is_alerted = ( ! $freeSignUp) ? true : false;
        $this->subscribe->plan_name  = $data->plan_name ?? "";


        return $user->subscriptions()->update($this->subscribe->toArray());
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function get($id)
    {
        return $this->subscribe->firstOrFail($id);
    }

    public function getAll($user_id)
    {
        return $this->subscribe->where('user_id', $user_id)->get();
    }
}