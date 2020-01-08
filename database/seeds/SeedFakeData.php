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
use Faker\Factory as Faker;

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

        $faker = Faker::create();

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
        ])->merge(factory(\App\User::class, 9)->create());

        // Create a customer for every users and attach them a default payment method
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

        // Create the renewal frequency
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

        $users->each(function($me) use($categories, $renewalFrequencies, $users, $stripeObj, $faker){

            $user_random_value = $faker->optional(0.6)->randomDigit;

            $categories->where('country', $me->country)->each(function($category) use($me, $renewalFrequencies, $users, $stripeObj, $faker, $user_random_value){

                $current_data = Carbon::now();
                $new_credential_updated_at = Carbon::now()->addSeconds(10);

                $sharing_random_value = $faker->optional(0.5)->randomDigit;

                $sharing = factory(Sharing::class)->create([
                    'name' => $category->name,
                    'price' => $category->price,
                    'renewal_frequency_id' => $renewalFrequencies->random(1)->pluck('id')->first(),
                    'category_id' => $category->id,
                    'owner_id' => $me->id,
                    'username' => is_null($sharing_random_value) ? null : $faker->username,
                    'password' => is_null($sharing_random_value) ? null : $faker->password,
                    'credential_updated_at' => is_null($sharing_random_value) ? null : $current_data
                ]);

                $stripeObj->createPlan($sharing);

                // Assign random user for every sharing
                $usersToManage = $users->where('country', $me->country)->where('id', '<>', $me->id);

                // Set a creadential_updated_at field if is set in the sharing
                $usersToAttach = $usersToManage->random(min($usersToManage->count(), 4))->pluck('id')->mapWithKeys(function($id){

                    $sharing_status = SharingStatus::getValues();
                    unset($sharing_status[SharingStatus::Joined]);

                    return [
                        $id => [
                            'status' => array_rand($sharing_status)
                        ]
                    ];
                });

                $sharing->users()->sync($usersToAttach);


                $sharing->approvedUsers()->get()->each(function($item) use($me, $sharing) {
                    $this->createSubscription($item, $sharing);
                });

                $sharing->members()->get()->each(function($member) use($sharing, $new_credential_updated_at, $user_random_value){

                    // Create 5 chat messages for every member
                    factory(Chat::class, 5)->create([
                        'sharing_id' => $member->sharing_status->sharing_id,
                        'user_id' => $member->sharing_status->user_id
                    ]);

                    // Set the credential_updated_at field for some members (only if credential isset in the sharing from owner)
                    $member->sharings()->updateExistingPivot($sharing->id, [
                        'credential_updated_at' => (!is_null($sharing->credential_updated_at) && !is_null($user_random_value)) ? $new_credential_updated_at : null
                    ]);
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
