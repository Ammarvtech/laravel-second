<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/22/2018
 * Time: 1:13 PM
 */

namespace Contracts\API\PushNotification;


interface PushNotificationContract
{
    public function send($to = null, $data = null);
}