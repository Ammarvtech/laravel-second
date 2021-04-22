<?php

namespace Contracts\Payment;

use App\Order;
use App\User;
use App\Subscription;

interface PaymentContract
{
    public function __construct(Order $order, User $user, Subscription $subscription);
    public function validate($order, $card, $request);
    public function checkout($order, $card, $request);
    public function cardParams($order, $card, $request);
}
