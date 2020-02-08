<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Resources\PaymentMethod;
use App\Http\Resources\PaymentMethodCollection;
use App\Http\Resources\Setupintent;
use App\Http\Traits\StripeTrait;
use App\MyClasses\Support\Facade\Stripe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/*
 * For generate apiResource from third parts API
 * https://medium.com/@jeffochoa/consuming-third-pary-apis-with-laravel-resources-c13a0c7dc945
 */

class PaymentMethodsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $stripeCustomer = Stripe::getCustomer();
        $paymentMethods = \Stripe\PaymentMethod::all([
            'customer' => $stripeCustomer->id,
            'type' => 'card',
        ]);

        return (new PaymentMethodCollection(collect($paymentMethods->data)))->additional(
            ['meta' => [
                'defaultPaymentMethod' => $stripeCustomer->invoice_settings->default_payment_method,
            ]]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $newPaymentMethod = $request->payment_method;
        $customer_id = Auth::user()->pl_customer_id;

        $allPaymentelements = \Stripe\PaymentMethod::all([
            'customer' => $customer_id,
            'type' => 'card',
        ]);

        $this->authorize('can-add-payment-method', count($allPaymentelements->data));

        $payment_method = \Stripe\PaymentMethod::retrieve($newPaymentMethod);
        $payment_method->attach(['customer' => $customer_id]);

        \Stripe\Customer::update($customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $newPaymentMethod,
                ],
            ]
        );

        return (new PaymentMethod(PaymentMethod::make(json_decode($payment_method->toJSON(), true))->resolve()))->additional(['meta' => [
            'defaultPaymentMethod' => $newPaymentMethod,
        ]]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $paymentMethod = $request->payment_method;

        \Stripe\Customer::update(Auth::user()->pl_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod,
                ],
            ]
        );

        return [
            'data' => [
                'defaultPaymentMethod' => $paymentMethod,
            ]
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $paymentMethod = $request->payment_method;
        $oldPaymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethod);
        $oldPaymentMethod->detach();

        return new PaymentMethod(PaymentMethod::make(json_decode($oldPaymentMethod->toJSON(), true))->resolve());
    }

    public function setupintent()
    {
        $setup_intent = \Stripe\SetupIntent::create([
            'payment_method_types' => ['card'],
        ]);

        return [
            'data' => [
                'client_secret' => $setup_intent->client_secret
            ]
        ];
    }

}
