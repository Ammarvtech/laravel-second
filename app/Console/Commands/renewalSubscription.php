<?php

namespace App\Console\Commands;

use App\Subscription;
use Contracts\Options\OptionsContract;
use Illuminate\Console\Command;

class renewalSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renew:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'renewal Subscription';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $duration = \App\Option::where("name", "subscription_duration")->first()['value'];

        $rows = Subscription::where("end_date", "<", \Carbon\Carbon::now())->whereHas('users', function($query){
            $query->has("primaryCreditCards", ">", 0);
        })->get();

        foreach($rows as $row){
            $start_date =  \Carbon\Carbon::now();
            $row->update([
                "start_date"  => $start_date,
                "end_date"    => \Carbon\Carbon::parse($start_date)->addDays($duration),
                "is_cancelled" => 0,
                "is_premium"  => 1
            ]);
        }

    }
}