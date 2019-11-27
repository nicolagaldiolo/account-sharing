<?php

use App\Category;
use App\Chat;
use App\MyClasses\Stripe;
use App\RenewalFrequency;
use App\Sharing;
use App\Enums\RenewalFrequencies;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Enums\SharingStatus;
use Illuminate\Support\Facades\Auth;

class SeedFakeData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $stripeObj = app(Stripe::class);

        // Se ci sono utenti con account Stripe li elimino
        collect($stripeObj->allAccount(['limit' => 99])->data)->each(function($item) use($stripeObj){
            $stripeObj->getAccount($item->id)->delete();
        });

        // Se ci sono Customer li elimino
        collect($stripeObj->allCustomer(['limit' => 99])->data)->each(function($item) use($stripeObj){
            $stripeObj->getCustomer($item->id)->delete();
        });


        // Se ci sono Plan li elimino
        collect(\Stripe\Plan::all(['limit' => 99])->data)->each(function($item){
            \Stripe\Plan::retrieve($item->id)->delete();
        });

        // Se ci sono Prodotti li elimino
        collect(\Stripe\Product::all(['limit' => 99])->data)->each(function($item){
            \Stripe\Product::retrieve($item->id)->delete();
        });


        // Creo gli utenti
        $users = factory(\App\User::class, 1)->create([
            'name' => env('DEMONAME', 'Firstname'),
            'surname' => env('DEMOLASTNAME', 'Lastname'),
            'email' => env('DEMOEMAIL', 'demouser@example.com'),
            'password' => bcrypt(env('DEMOPASS', 'password'))
        ])->merge(factory(\App\User::class, 9)->create());

        // Per ogni utente creo un customer, gli attacco un metodo di pagamento e lo rendo di default per il customer
        $users->each(function($user){

            $platformStripeCustomer = \Stripe\Customer::create([
                'email' => $user->email,
            ]);
            $user->pl_customer_id = $platformStripeCustomer->id;
            $user->save();

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

        // Creo le categorie
        $categories = collect([]);
        collect([
            [
                'name' => 'Netflix Premium',
                'customizable' => false,
            ],
            [
                'name' => 'Amazon Music',
                'customizable' => false,
            ],
            [
                'name' => 'Apple Music',
                'customizable' => false,
            ],
            [
                'name' => 'Spotify Family',
                'customizable' => false,
            ],
            [
                'name' => 'Custom',
                'customizable' => true,
            ]
        ])->each(function($item) use($categories){
            $categories->push(factory(Category::class)->create($item));
        });

        // Per ogni utente creo, account, customer, e condivisioni
        $users->take(2)->each(function($me) use($categories, $renewalFrequencies, $users, $stripeObj){

            $account = \Stripe\Account::create([
                'country' => 'IT',
                'email' => $me->email,
                'type' => 'custom',
                'business_type' => 'individual',
                // Before the 2019-09-09 API version, the transfers capability was referred to as platform_payments. If you're using an API version older than 2019-09-09, you need to use platform_payments.
                // For platforms creating connected accounts in Australia, Austria, Belgium, Czech Republic, Denmark, Estonia, Finland, France, Germany, Greece, Ireland, Italy, Latvia, Lithuania, Luxembourg, the Netherlands, New Zealand, Norway, Poland, Portugal, Slovakia, Slovenia, Spain, Sweden, Switzerland, or the United Kingdom, request both the card_payments and transfers capabilities to enable card processing for your connected accounts.
                "requested_capabilities" => ["card_payments", "transfers"],
                'individual' => [
                    'email' => $me->email,
                    'first_name' => $me->name,
                    'last_name' => $me->surname,
                    'phone' => '+393917568474',
                    'dob' => [
                        'day' => $me->birthday->day,
                        'month' => $me->birthday->month,
                        'year' => $me->birthday->year
                    ],
                    'address' => [
                        'line1' => 'Via Giovanni Caboto',
                        'city' => 'Verona',
                        'postal_code' => '37068'
                    ]
                ],
                'tos_acceptance' => [
                    'date' => time(),
                    'ip' => request()->ip() // Assumes you're not using a proxy
                ],
                'business_profile' => [
                    'mcc' => '4900',
                    'url' => 'https://www.google.it'
                ],
            ]);

            $me->pl_account_id = $account->id;
            $me->save();

            $categories->take(1)->each(function($category) use($me, $renewalFrequencies, $users, $account){

                $sharing = factory(Sharing::class)->create([
                    'name' => $category->name,
                    'price' => $category->price,
                    'renewal_frequency_id' => $renewalFrequencies->random(1)->pluck('id')->first(),
                    'category_id' => $category->id,
                ]);

                $stripePlan = \Stripe\Plan::create([
                    "amount" => number_format((float)$sharing->price * 100., 0, '.', ''),
                    "interval" => "month",
                    "product" => [
                        "name" => $sharing->name
                    ],
                    "currency" => "eur"
                ], ['stripe_account' => $account->id]);

                $sharing->stripe_plan = $stripePlan->id;
                $sharing->save();

                // Per ogni condivisione assegno degli utenti random e mi assicuro di togliere l'utente corrente
                $usersToAttach = $users->reject(function($value) use($me){
                    return $value->id === $me->id;
                })->random(1)->each(function($item) use($me){
                    $stripeCustomer = $item->customers()->where('user_pl_account_id', $me->id)->first();
                    if(is_null($stripeCustomer)){

                        // Retrievev default paymentMethod from customer platform
                        $defaultPaymentMethod = \Stripe\Customer::retrieve($item->pl_customer_id)->invoice_settings->default_payment_method;

                        // Create a new payment method
                        $new_payment_method = \Stripe\PaymentMethod::create([
                            'customer' => $item->pl_customer_id,
                            'payment_method' => $defaultPaymentMethod,
                        ], ['stripe_account' => $me->pl_account_id]);

                        $new_account_customer = \Stripe\Customer::create([
                            'email' => $item->email,
                            'payment_method' => $new_payment_method->id,
                        ], ['stripe_account' => $me->pl_account_id]);

                        \Stripe\Customer::update($new_account_customer->id, [
                            'invoice_settings' => [
                                'default_payment_method' => $new_payment_method->id,
                            ],
                        ], ['stripe_account' => $me->pl_account_id]);

                        $stripeCustomer = $item->customers()->create([
                            'customer_id' => $new_account_customer->id,
                            'user_pl_account_id' => $me->id,
                        ]);
                    }

                })->pluck('id')->mapWithKeys(function($item){
                    $sharingStatus = SharingStatus::getValues();
                    return [
                        $item => [
                            'status' => SharingStatus::Approved
                        ]
                    ];
                });


                // L'utente corrente lo aggiungo come joiner e owner
                $usersToAttach->put($me->id, [
                    'owner' => true,
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

                $sharing->approvedUsers()->get()->each(function($item) use($me, $sharing) {

                    $customer = $item->customers()->where('user_pl_account_id', $me->id)->firstOrFail();

                    // Creo la subscription
                    $subscription = \Stripe\Subscription::create([
                        "customer" => $customer->customer_id,
                        "items" => [
                            [
                                "plan" => $sharing->stripe_plan,
                            ],
                        ],
                        "application_fee_percent" => 10,
                    ], ['stripe_account' => $me->pl_account_id]);
                });

            });
        });
    }
}
