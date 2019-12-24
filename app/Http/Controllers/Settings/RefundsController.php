<?php

namespace App\Http\Controllers\Settings;

use App\Enums\RefundApplicationStatus;
use App\Invoice;
use App\Refund;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RefundsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $payment_intent = $request->only('payment_intent');
        $invoice = Invoice::where('payment_intent', $payment_intent)->firstOrFail();

        // Authorization
        $this->authorize('refund', $invoice);

        // Validation
        $this->validate($request, [
            'payment_intent' => 'unique:refunds',
        ]);

        $refund = $invoice->refunds()->create([
            'internal_status' => RefundApplicationStatus::Pending
        ]);

        $refund->transactions()->create();

        $this->manage($refund, 'approve');

        return $refund;
    }

    public function manage(Refund $refund, $action = null)
    {
        switch ($action){
            case 'approve' :
                $refund->internal_status = RefundApplicationStatus::Approved;
                $refund->save();
                $this->submit($refund);
                break;
            case 'refuse' :
                $refund->internal_status = RefundApplicationStatus::Refused;
                $refund->save();
                break;
        }
    }

    public function submit(Refund $refund)
    {
        $subscription = $refund->invoice->subscription_id;

        $subscription = \Stripe\Subscription::retrieve($subscription);
        $subscription->cancel();

        \Stripe\Refund::create([
            'payment_intent' => $refund->invoice->payment_intent,
        ]);
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
