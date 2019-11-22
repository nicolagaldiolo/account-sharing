<?php

namespace App\Http\Controllers\Stripe;

use App\MyClasses\Support\Facade\Stripe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PaymentMethodsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function setupintent()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        return \Stripe\SetupIntent::create([
            'payment_method_types' => ['card'],
        ]);
    }

    public function getCustomer()
    {
        return Stripe::getCustomer(Auth::user()->stripe_customer_id);
    }


    public function index()
    {
        return $this->getPaymentMethods();
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

        $paymentMethod = $request->payment_method;

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $payment_method = \Stripe\PaymentMethod::retrieve($paymentMethod);
        $payment_method->attach(['customer' => Auth::user()->stripe_customer_id]);

        $user = Auth::user();
        $allPaymentelements = \Stripe\PaymentMethod::all([
            'customer' => $user->stripe_customer_id,
            'type' => 'card',
        ]);

        // Se ho un solo metodo di pagamento lo imposto come default
        if(count($allPaymentelements->data) == 1) {
            \Stripe\Customer::update($user->stripe_customer_id, [
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethod,
                    ],
                ]
            );
        }

        return $this->getPaymentMethods();
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

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        \Stripe\Customer::update(Auth::user()->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod,
                ],
            ]
        );

        return $this->getPaymentMethods();
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

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $payment_method = \Stripe\PaymentMethod::retrieve($paymentMethod);
        $payment_method->detach();

        return $this->getPaymentMethods();
    }

    protected function getPaymentMethods()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $user = Auth::user();

        $stripeCustomer = Stripe::getCustomer($user->stripe_customer_id);

        return [
            'methods' => \Stripe\PaymentMethod::all([
                'customer' => $user->stripe_customer_id,
                'type' => 'card',
            ]),
            'defaultPaymentMethod' => $stripeCustomer->invoice_settings->default_payment_method
        ];
    }

}
