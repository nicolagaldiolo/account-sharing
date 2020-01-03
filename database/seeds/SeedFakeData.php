<?php

use App\Category;
use App\Chat;
use App\Enums\SubscriptionStatus;
use App\Imports\ServiceDataImport;
use App\MyClasses\Stripe;
use App\RenewalFrequency;
use App\Sharing;
use App\Enums\RenewalFrequencies;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Enums\SharingStatus;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SeedFakeData extends Seeder
{

    use \App\Http\Traits\SharingTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $stripeObj = app(Stripe::class);

        // If accounts exist, delete them
        $stripeObj->deleteAllAccounts();

        // If customers exist, delete them
        $stripeObj->deleteAllCustomers();


        // If plans exist, delete them
        $stripeObj->deleteAllPlans();

        // If products exist, delete them
        $stripeObj->deleteAllProducts();

        // Crate the users
        $users = factory(\App\User::class, 1)->create([
            'name' => env('DEMONAME', 'Firstname'),
            'surname' => env('DEMOLASTNAME', 'Lastname'),
            'email' => env('DEMOEMAIL', 'demouser@example.com'),
            'password' => bcrypt(env('DEMOPASS', 'password'))
        ])->merge(factory(\App\User::class, 3)->create());


        // Per ogni utente creo un customer, gli attacco un metodo di pagamento e lo rendo di default per il customer
        $users->each(function($user){

            $platformStripeCustomer = \App\MyClasses\Support\Facade\Stripe::getCustomer($user);

            $payment_method_to_attach = \Stripe\PaymentMethod::retrieve('pm_card_visa');
            $payment_method_to_attach->attach(['customer' => $platformStripeCustomer->id]);
            \Stripe\Customer::update($platformStripeCustomer->id, [
                    'invoice_settings' => [
                        'default_payment_method' => $payment_method_to_attach,
                    ],
                ]
            );
        });


        // Creo le frequenze di rinnovo
        $renewalFrequencies = collect([]);
        collect([
            [
                'value' => 1,
                'type' => RenewalFrequencies::Months
            ],
            [
                'value' => 1,
                'type' => RenewalFrequencies::Years
            ]
        ])->each(function($item) use ($renewalFrequencies){
            $renewalFrequencies->push(factory(RenewalFrequency::class)->create($item));
        });


        // Import the categories
        Excel::import(new ServiceDataImport, storage_path('import_data/service_data.xlsx'));
        $categories = Category::withoutGlobalScope('country')->get();

        // Per ogni utente creo, account, customer, e condivisioni
        //$users->take(2)->each(function($me) use($categories, $renewalFrequencies, $users, $stripeObj){

        $users->each(function($me) use($categories, $renewalFrequencies, $users, $stripeObj){

            //$categories->take(1)->each(function($category) use($me, $renewalFrequencies, $users, $account){
            $categories->where('country', $me->country)
                ->take(2)
                ->each(function($category) use($me, $renewalFrequencies, $users, $stripeObj){

                    $sharing = factory(Sharing::class)->create([
                        'name' => $category->name,
                        'price' => $category->price,
                        'renewal_frequency_id' => $renewalFrequencies->random(1)->pluck('id')->first(),
                        'category_id' => $category->id,
                        'owner_id' => $me->id
                    ]);

                    $stripeObj->createPlan($sharing, $me);

                    // Per ogni condivisione assegno degli utenti random e mi assicuro di togliere l'utente corrente

                    $usersToManage = $users->where('country', $me->country)->reject(function($value) use($me){
                        return $value->id === $me->id;
                    });

                    $usersToAttach = $usersToManage->random(min($usersToManage->count(), 3))->pluck('id')->mapWithKeys(function($item){
                        $sharingStatus = SharingStatus::getValues();
                        return [
                            $item => [
                                'status' => SharingStatus::Approved
                            ]
                        ];
                    });

                    // L'utente corrente lo aggiungo come joiner e owner
                    $usersToAttach->put($me->id, [
                        'status' => SharingStatus::Joined,
                        'credential_updated_at' => Carbon::now(),
                    ]);

                    $sharing->users()->sync($usersToAttach);


                    // Per ogni utente joiner creo 5 messaggi in chat
                    $usersToAttach->filter(function ($value) {
                        return $value['status'] === SharingStatus::Joined;
                    })->each(function($value, $key) use($sharing){
                        factory(Chat::class, 5)->create([
                            'sharing_id' => $sharing->id,
                            'user_id' => $key
                        ]);
                    });

                    $sharing->activeUsersWithoutOwner()->get()->each(function($item) use($me, $sharing) {
                        $this->createSubscription($item, $sharing);
                    });

                    /*
                    $me->payouts()->create([
                        'stripe_id' => 'xxxxxxxx',
                        'amount' => number_format((float)$sharing->price * 100., 0, '.', ''),
                        'currency' => 'eur',
                        'ccnumber' => '8745'
                    ])->transactions()->create();
                    */

            });
        });

    }
}
