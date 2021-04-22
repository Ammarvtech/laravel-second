<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/10/2018
 * Time: 12:41 PM
 */

namespace App\Http\Resources\CreditCards;

use App\Http\Resources\Resource;

class CreditCardResource extends Resource
{
    public function get()
    {
        return [
            "type"         => $this->model->type,
            "card_number"  => $this->model->card_number,
            "name_on_card" => $this->model->name_on_card,
            "expiry"       => $this->model->expiry_date,
        ];
    }
}