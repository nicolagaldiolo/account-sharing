<?php

namespace App\Http\Controllers\Stripe;

use App\MyClasses\Support\Facade\Stripe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        //ASSOLUTAMENTE DA ELIMINARE, VA CENTRALIZZATA ALTROVE LA CREAZIONE DEL CUSTOMER
        $user = Auth::user();
        if(is_null($user->stripe_customer_id)){
            // Creo il Customer
            $stripeCustomer = Stripe::createCustomer([
                'email' => $user->email,
                'source' => 'tok_visa',
            ]);
            $user->stripe_customer_id = $stripeCustomer->id;
            $user->save();
        }else{
            $stripeCustomer = Stripe::getCustomer($user->stripe_customer_id);
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        //dd(Stripe::getCustomer($user->stripe_customer_id));

        return Stripe::getCustomer(Auth::user()->stripe_customer_id);

        //return \Stripe\PaymentMethod::all([
        //    'customer' => $user->stripe_customer_id,
        //    'type' => 'card',
        //]);
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
        /*$paymentMethod = json_decode($request->getContent(), true);

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");
        $payment_method = \Stripe\PaymentMethod::retrieve($paymentMethod['id']);
        $payment_method->attach(['customer' => Auth::user()->stripe_customer_id]);

        \Stripe\Customer::update(Auth::user()->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $payment_method->id,
                ],
            ]
        );
        */


        /*return Stripe::createSource(
            Auth::user()->stripe_customer_id,[
                'source' => $token
            ]
        );
        */
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
        $data = json_decode($request->getContent(), true);

        return Stripe::updateCustomer(Auth::user()->stripe_customer_id, $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        return Stripe::deleteSource(Auth::user()->stripe_customer_id, $data['id']);
    }
}
