<?php

namespace App\Console\Commands;

use App\Invoice;
use Illuminate\Console\Command;

class TransferMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfermoney:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        Invoice::transferable()->get()->each(function ($item){
            \Stripe\Transfer::create([
                "amount" => $item->total_less_fee,
                "currency" => $item->currency,
                "destination" => $item->account_id,
                "transfer_group" => $item->payment_intent
            ]);
        });
    }
}
