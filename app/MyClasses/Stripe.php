<?php

namespace App\MyClasses;

class Stripe
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");
    }

    /*
     * ACCOUNT
     */

    public function allAccount($params = null)
    {
        return \Stripe\Account::all($params);
    }

    public function getAccount($id = null)
    {
        return \Stripe\Account::retrieve($id);
    }

    public function createAccount($params = null)
    {
        return \Stripe\Account::create($params);
    }

    /*
     * CUSTOMER
     */

    public function createCustomer($params = null, $options = null)
    {
        return \Stripe\Customer::create($params, $options);
    }

    public function allCustomer($params = null)
    {
        return \Stripe\Customer::all($params);
    }

    public function getCustomer($id = null, $options = null)
    {
        return \Stripe\Customer::retrieve($id, $options);
    }

    public function updateCustomer($id = null, $options = null)
    {
        return \Stripe\Customer::update($id, $options);
    }


    /*
     * SOURCE
     */
    public function allSource($id = null, $options = null)
    {
        return \Stripe\Customer::allSources($id, $options);
    }

    public function createSource($source = null, $metadata = null)
    {
        return \Stripe\Customer::createSource($source, $metadata);
    }

    public function deleteSource($id = null, $source = null)
    {
        $customer = $this->getCustomer($id);
        if(count($customer->sources->data) <= 1){
            abort(403, 'Operation not permitted');
        }else{
            \Stripe\Customer::deleteSource($id, $source);
            return $this->getCustomer($id);
        }
    }

    /*
     * PLAN
     */
    public function createPlan($params = null, $options = null)
    {
        return \Stripe\Plan::create($params, $options);
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
