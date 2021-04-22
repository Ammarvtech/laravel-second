<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/10/2018
 * Time: 12:32 PM
 */

namespace App\Http\Resources\Subscriptions;

use App\Http\Resources\Resource;

class SubscriptionResource extends Resource
{
    public function get()
    {
        return [
            "expiry"       => ( ! is_null($this->model->card_id)) ? $this->model->end_date : "",
            "alert"        => $this->model->is_alerted,
            "plan_name"    => $this->model->plan_name,
            "is_cancelled" => $this->model->is_cancelled,
        ];
    }
}