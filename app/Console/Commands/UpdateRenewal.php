<?php

namespace App\Console\Commands;

use App\Enums\RenewalStatus;
use App\Renewal;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateRenewal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renewal:update';

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
        Renewal::toPay()->with('sharingUser.sharing')->get()->each(function($item){

            if(true){ // pagamento confermato

                $item->update([
                    'status' => RenewalStatus::Confirmed
                ]);

                $item->sharingUser->renewals()->create([
                    'status' => RenewalStatus::Pending,
                    'starts_at' => $item->expires_at->startOfDay(),
                    'expires_at' => $item->sharingUser->sharing->calcNextRenewal($item->expires_at)
                ]);
            }
        });

    }
}
