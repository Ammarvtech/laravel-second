<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/22/2018
 * Time: 1:57 PM
 */

namespace Repos\API\PushNotification;


use App\Http\Resources\Notifications\TrackResource;
use Contracts\API\PushNotification\PushNotificationContract;

class PushNotificationRepo implements PushNotificationContract
{
    protected $url = "https://fcm.googleapis.com/fcm/send";
    protected $api_access_key = "AIzaSyCfQKd7m2T51vsQUXSr7GHYD_GzGHQFx4g";
    protected $sandBox = true;


    protected function testingData($type = 'text')
    {
        $to = [
            "dPVU7IYXig8:APA91bGtJYGcVy9en3HZnajybgnj8Wgvvai_G3jC_vjxRf9AG8svfJBRe8y91EhqMJYmnf3wYZlhUSQPSKYyIehKCv6k6v9ZIBacTciqQqMbsMAtwr_MWLDYkn2ygqSVAGcBuA4VgHn2",
//            "fCZCWKYtr0A:APA91bGvMwU8DIHi4Mr97_FhqsLE6mHNCNOLN08mbiVHra9PIZy8T-9YNsCTFkscVWKzenSkH5C7PpZHzRA0Vk2dg7jIZxMcjn5wC3e4EqnnrJTakW5IQZZV4xFqvhCNeO6IgDiGajCK"
        ];
        switch($type){
            case "payment":
                $data = [
                    "track"   => false,
                    "message" => "check your payment",
                    "type"    => "payment",
                    "name"    => "Payment"
                ];
                break;
            case "video":
                $data = [
                    "track"   => true,
                    "name"    => "Title",
                    "message" => "text",
                    "type"    => "video",
                    "row"     => (new TrackResource(\App\Movie::find(1)))->get()
                ];
                break;
            default:
                $data = [
                    "track"   => false,
                    "message" => "You Have a new text",
                    "type"    => "text",
                    "name"    => "text"
                ];
                break;
        }
        return $this->fieldsRequest($to, $data);
    }

    protected function body($data)
    {
        $video = (isset($data['track']) && $data['track']) ? $data['row'] : [];

        return array_merge([
            'notification_type' => $data['type'] ?? "",
            "name"              => $data['name'] ?? "",
            "message"           => $data['message'] ?? "",
            "vibrate"           => 'true',
            "sound"             => 'true',
            "is_expired"        => (\JWTAuth::toUser()->premium_end_date < \Carbon\Carbon::now())
        ], $video);
    }

    protected function fieldsRequest($to, $datas)
    {
        $to = (count($to) == 1) ? ["to" => $to[0]] : [
            'registration_ids' => $to
        ];

        return array_merge(
            [
                'data'     => $this->body($datas),
                "priority" => "HIGH"
            ],
            $to
        );
    }

    public function send($to = null, $data = null)
    {
        $msg = ( ! $this->sandBox) ? $this->fieldsRequest($to, $data) : $this->testingData($data['type']);

        return $this->sendRequest($to, $msg);
    }


    protected function sendRequest($to, $fields)
    {

        $headers = array
        (
            'Authorization: key=' . $this->api_access_key,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result)->success;
    }
}