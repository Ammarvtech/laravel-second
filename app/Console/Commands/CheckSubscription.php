<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Contracts\Users\UsersContract;
use Carbon\Carbon;
use App\User;
use App\Order;
use App\paylane\PayLaneRestClient;
use App\CreditCards;
use Contracts\Options\OptionsContract;

class CheckSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:premium';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if subscription end date expired';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OptionsContract $config, User $user)
    {
        $this->config = $config;
        $this->user = $user;
        $this->username = env('PayLane_API');
        $this->password = env('PayLane_PASSWORD');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new PayLaneRestClient($this->username, $this->password);
        $orders = Order::whereHas('card', function ($query) {
            $query->where('is_primary', 1)->where('is_delete',0);
        })->get();
        $amount = $this->config->getByName('amount');
        if(!$orders){
            return false;
        }
        foreach ($orders as $order) {
            if ($order->expiry_date != null && $order->expiry_date < Carbon::now()) {
                User::where('id', $order->user_id)->update(['is_premium' => 0]);
                $resale_params = [
                    'id_sale'     => $order->id_sale,
                    'amount'      => $order->amount,
                    'currency'    => $order->currency,
                    'description' => $order->description,
                ];
                // perform the resale:
                try {
                    $status = $client->resaleBySale($resale_params);
                } catch (Exception $e) {
                    // handle exceptions here
                }

                // checking transaction status example (optional):
                if ($client->isSuccess()) {
                    $id_sale = $status['id_sale'];
                    $subscription_duration = $this->config->getByName('subscription_duration');
                    $order->update(['id_sale' => $id_sale, 'status' => 'success', 'expiry_date' => Carbon::now()->addDays($subscription_duration->value)]);
                    $this->user->where('id', $order->user_id)->update([
                        'is_premium' => 1,
                        'premium_start_date' => Carbon::now(),
                        'premium_end_date' => Carbon::now()->addDays($subscription_duration->value)
                    ]);
                } else {
                    die("Error number: {$status['error']['error_number']}, \n" .
                        "Error description: {$status['error']['error_description']}");
                }
            }
        }
    }
}
