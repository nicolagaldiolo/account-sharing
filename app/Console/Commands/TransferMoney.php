<?php

namespace App\Console\Commands;

use App\Invoice;
use App\Notifications\MoneyTransfered;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

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

        // Get the transferible Invoice
        $ids = Invoice::transferable()->get()->pluck('id');

        // Mark them as transfered
        Invoice::whereIn('id', $ids)->update([
            'transfered' => 1
        ]);

        // Send Notification to owners
        $usersIds = Invoice::whereIn('id', $ids)->with('subscription.sharingUser.sharing')->get()
            ->pluck('subscription.sharingUser.sharing.owner_id')->unique();
        Notification::send(User::whereIn('id', $usersIds)->get(), new MoneyTransfered());

    }
}
