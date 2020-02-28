<?php

namespace App\MyClasses;

use App\Sharing;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Stripe
{

    protected $user = '';

    public function __construct()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $this->setUser();
    }

    protected function setUser($user = null)
    {
        $this->user = ($user) ? $user : Auth::user();
    }

    /*
     * ACCOUNT
     */

    public function deleteAllAccounts()
    {
        $accounts = collect(\Stripe\Account::all(['limit' => 99])->data);
        if($accounts->isNotEmpty()){
            $accounts->each(function($item){
                $item->delete();
            });
            $this->deleteAllAccounts();
        }
    }

    public function createAccount($params = null)
    {

        $now = time();

        $account = \Stripe\Account::create([
            'country' => $this->user->country,
            'email' => $this->user->email,
            'type' => 'custom',
            'requested_capabilities' => [
                'card_payments',
                'transfers'
            ],
            'business_type' => 'individual',
            'individual' => [
                'phone' => $this->user->phone,
                'first_name' => $this->user->name,
                'last_name' => $this->user->surname,
                'email' => $this->user->email,
                'dob' => [
                    'day' => $this->user->birthday->day,
                    'month' => $this->user->birthday->month,
                    'year' => $this->user->birthday->year
                ],
                'address' => [
                    'line1' => $this->user->street,
                    'city' => $this->user->city,
                    'postal_code' => $this->user->cap
                ]
            ],
            'tos_acceptance' => [
                'date' => $now,
                'ip' => request()->ip()
            ],
            'business_profile' => [
                'mcc' => '5734',
                'product_description' => ''
            ]
        ]);

        $this->user->update([
            'pl_account_id' => $account->id,
            'tos_acceptance_at' => Carbon::createFromTimestamp($now)
        ]);

        return $account;
    }

    public function getAccount($user = null)
    {
        // Use logged user if not provided
        if(!is_null($user)){
            $this->setUser($user);
        }

        // If the account exists, return it otherwise create it
        if(!empty($this->user->pl_account_id)){
            try {
                $account = \Stripe\Account::retrieve($this->user->pl_account_id);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $account = $this->createAccount();
            }
        }else{
            $account = $this->createAccount();
        }

        return $account;
    }

    /*
     * CUSTOMER
     */

    public function createCustomer()
    {
        $customer = \Stripe\Customer::create([
            'email' => $this->user->email
        ]);

        $this->user->update([
            'pl_customer_id' => $customer->id
        ]);

        return $customer;
    }

    public function deleteAllCustomers()
    {
        $customers = collect(\Stripe\Customer::all(['limit' => 99])->data);
        if($customers->isNotEmpty()){
            $customers->each(function($item){
                $item->delete();
            });
            $this->deleteAllCustomers();
        }
    }

    public function getCustomer($user = null)
    {
        // Use logged user if not provided
        if(!is_null($user)){
            $this->setUser($user);
        }

        // If the customer exists, return it otherwise create it
        if(!empty($this->user->pl_customer_id)){
            try {
                $customer = \Stripe\Customer::retrieve($this->user->pl_customer_id);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $customer = $this->createCustomer();
            }
        }else{
            $customer = $this->createCustomer();
        }

        return $customer;
    }

    public function updateCustomer($id = null, $options = null)
    {
        return \Stripe\Customer::update($id, $options);
    }

    /*
     * PLAN
     */

    public function deleteAllPlans()
    {
        $plans = collect(\Stripe\Plan::all(['limit' => 99])->data);
        if($plans->isNotEmpty()){
            $plans->each(function($item){
                $item->delete();
            });
            $this->deleteAllPlans();
        }
    }

    public function createPlan(Sharing $sharing)
    {

        $user = User::findOrFail($sharing->owner_id);
        $this->setUser($user);

        $plan = \Stripe\Plan::create([
            'amount' => number_format((float)$sharing->price * 100., 0, '.', ''),
            'interval' => 'month',
            'product' => [
                'name' => $sharing->name
            ],
            'currency' => $this->user->currency
        ]);

        $sharing->stripe_plan = $plan->id;
        $sharing->save();

        return $plan;
    }

    /*
     * PRODUCT
     */

    public function deleteAllProducts()
    {
        $products = collect(\Stripe\Product::all(['limit' => 99])->data);
        if($products->isNotEmpty()){
            $products->each(function($item){
                $item->delete();
            });
            $this->deleteAllProducts();
        }
    }

    /*
     * EXTERNAL ACCOUNT
     */
    public function createExternalAccount($token, $user = null)
    {

        // Use logged user if not provided
        if(!is_null($user)){
            $this->setUser($user);
        }

        $account = $this->getAccount();

        $external_account = \Stripe\Account::createExternalAccount(
            $account->id,
            [
                'external_account' => $token,
            ]
        );

        return $external_account;
    }

    /*
     * SUBSCRIPTION
     */
    public function createSubscription($sharing, $user = null)
    {
        $customer = $this->getCustomer($user);

        return \Stripe\Subscription::create([
            'customer' => $customer->id,
            'items' => [
                [
                    'plan' => $sharing->stripe_plan,
                ],
            ],
            'expand' => [
                'latest_invoice.payment_intent'
            ]
        ]);
    }

    public function payInvoice($id){

        // Reattempt payment https://stripe.com/docs/billing/subscriptions/payment#failure-4

        $stripeSubscription = \Stripe\Subscription::retrieve($id);

        if($stripeSubscription->status === 'incomplete' || $stripeSubscription->status === 'past_due') {

            $invoice = \Stripe\Invoice::retrieve([
                'id' => $stripeSubscription->latest_invoice
            ]);

            try {
                $invoice->pay([
                    'expand' => [
                        'payment_intent'
                    ]
                ]);
            }catch (\Exception $e){
                logger($e);
            }
        }
    }
}
