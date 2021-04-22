<?php
namespace Repos\Countries;

use Contracts\Countries\CountriesContract;
use App\Country;
use Carbon\Carbon;
use Cartalyst\Stripe\Stripe;
use App\Subscription;

class CountriesRepo implements CountriesContract
{
    private $pagination = 20;

    public function __construct(Country $country, Subscription $subscription)
    {
        $this->country   = $country;
        $this->subscription = $subscription;
    }

    public function get($id)
    {
        return $this->country->findOrFail($id);
    }

    public function getAll()
    {
        return $this->country->all();
    }

    public function getPaginated()
    {
        return $this->country->paginate($this->pagination);
    }

    public function backendFilter($request)
    {
        $q = $this->country;

        if (isset($request->keyword) && !empty($request->keyword))
            $q = $q->where('name','LIKE', '%' . $request->keyword . '%');

        return (object) [
            'data'  => $q->paginate($this->pagination),
            'count' => $q->count()
        ];
    }

    public function set($data)
    {
        $package_id = "payment-plan-".\rand(00,99)."-".$data->name."-".$data->code."-".\rand(0000,9999);
        //$package_id_2 = "ongoing-plan-".\rand(00,99)."-".$data->name."-".$data->code."-".\rand(0000,9999);
        $inputs = [
            'name' => $data->name,
            'code' => $data->code,
            'curriency_code' => $data->curriency_code,
            'amount' => $data->amount,
            /*'ongoing_amount' => $data->ongoing_amount,*/
            'status' => 0,
            'plan_id' => $package_id,
            /*'ongoing_plan_id' => $package_id_2*/
        ];

        /*try {
            $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
            $plan_array = array(
                "name"            => 'Monthly',
                "id"              => $package_id_1,
                "interval"        => 'month',
                "interval_count"  => 1,
                "currency"        => $data->curriency_code,
                "amount"          => $data->amount,
            );
            $plan = $stripe->plans()->create($plan_array);
        }catch (Exception $e) {
            $array = $e->getJsonBody();
        }*/

        try {
            $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
            $plan_array = array(
                "name"            => 'Monthly',
                "id"              => $package_id,
                "interval"        => 'month',
                "interval_count"  => 1,
                "currency"        => $data->curriency_code,
                "amount"          => $data->amount,
            );
            $plan = $stripe->plans()->create($plan_array);
        }catch (Exception $e) {
            $array = $e->getJsonBody();
        }

        return $this->country->create($inputs);
    }

    public function update($data, $id)
    {
        $country = $this->get($id);
        $inputs = [
            'name' => $data->name,
            'code' => $data->code,
            'curriency_code' => $data->curriency_code,
            'amount' => $data->amount
        ];

        /*if($country->first_plan_id == "" || $country->amount != $data->amount){
            $package_id_1 = "first-plan-".\rand(00,99)."-".$data->name."-".$data->code."-".\rand(0000,9999);
            $inputs['first_plan_id'] = $package_id_1;
            try {
                $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
                $plan_array = array(
                    "name"            => 'Monthly',
                    "id"              => $package_id_1,
                    "interval"        => 'month',
                    "interval_count"  => 1,
                    "currency"        => $data->curriency_code,
                    "amount"          => $data->amount,
                );
                $plan = $stripe->plans()->create($plan_array);
            }catch (Exception $e) {
                $array = $e->getJsonBody();
            }
        }*/
       
        if($country->plan_id == "" || $country->amount != $data->amount){
           $package_id = "payment-plan-".\rand(00,99)."-".$data->name."-".$data->code."-".\rand(0000,9999);
            $inputs['plan_id'] = $package_id;
            try {
                $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
                $plan_array = array(
                    "name"            => 'Monthly',
                    "id"              => $package_id,
                    "interval"        => 'month',
                    "interval_count"  => 1,
                    "currency"        => $data->curriency_code,
                    "amount"          => $data->amount,
                );
                $plan = $stripe->plans()->create($plan_array);
            }catch (Exception $e) {
                $array = $e->getJsonBody();
            }
            
            /*$subscriptions =  $this->subscription->where('is_premium', 1)->where('plan_id', $country->ongoing_plan_id)->get();
            if(count($subscriptions) > 0){
                $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
                foreach ($subscriptions as $key => $s) {
                    $subscription = $stripe->subscriptions()->update($s->customer_id, $s->subscription_id, [
                        'plan' => $package_id_2,
                        'prorate' => false,
                    ]);
                    Subscription::where('customer_id', $s->customer_id)->where('subscription_id', $s->subscription_id)->update(['plan_id' => $package_id_2]);
                }
            }*/
        }

        return $country->update($inputs);
    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }

    public function countAll(){
        return $this->country->count();
    }

    public function getCode($name){
        return $this->country->where('name', $name)->pluck('code')->first();
    }

    public function getCurrencyCode($name){
        return $this->country->where('name', $name)->pluck('curriency_code')->first();
    }
    
}
