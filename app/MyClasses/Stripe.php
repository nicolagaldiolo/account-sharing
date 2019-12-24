<?php

namespace App\MyClasses;

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

    public function deleteAllAccount()
    {
        $accounts = collect(\Stripe\Account::all(['limit' => 99])->data);
        if($accounts->isNotEmpty()){
            $accounts->each(function($item){
                $item->delete();
            });
            $this->deleteAllAccount();
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

    public function deleteAllCustomer()
    {
        $customers = collect(\Stripe\Customer::all(['limit' => 99])->data);
        if($customers->isNotEmpty()){
            $customers->each(function($item){
                $item->delete();
            });
            $this->deleteAllCustomer();
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
    public function createPlan($sharing = null, $user = null)
    {

        // Get account
        $account = $this->getAccount($user);

        if(!is_null($sharing) && $account){
            $plan = \Stripe\Plan::create([
                'amount' => number_format((float)$sharing->price * 100., 0, '.', ''),
                'interval' => 'month',
                'product' => [
                    'name' => $sharing->name
                ],
                'metadata' => [
                    'account_id' => $account->id
                ],
                'currency' => 'eur' // incastrato, da rendere dinamico
            ]);

            $sharing->stripe_plan = $plan->id;
            $sharing->save();
        }

        return $plan;
    }







    /*
     * TOKEN
     */
    public function createToken($params = null, $options = null)
    {
        return \Stripe\Token::create($params, $options);
    }

    /*
     * SUBSCRIPTION
     */
    public function createSubscription($params = null, $options = null)
    {
        return \Stripe\Subscription::create($params, $options);
    }
}
