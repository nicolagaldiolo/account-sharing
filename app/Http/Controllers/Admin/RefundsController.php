<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RefundApplicationStatus;
use App\Events\RefundResponse;
use App\Refund;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class RefundsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return \App\Http\Resources\Refund::collection(Refund::paginate());
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
        //
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
    public function update(Request $request, Refund $refund, $action = null)
    {
        $action = Str::upper($action);
        switch ($action){
            case 'APPROVE' :
                $refund->internal_status = RefundApplicationStatus::Approved;
                $refund->save();

                $subscription = $refund->invoice->subscription_id;
                $subscription = \Stripe\Subscription::retrieve($subscription);
                $subscription->cancel();

                \Stripe\Refund::create([
                    'payment_intent' => $refund->invoice->payment_intent,
                ]);
                break;
            case 'REFUSE' :
                $refund->internal_status = RefundApplicationStatus::Refused;
                $refund->save();
                break;
        }
        event(new RefundResponse($refund, $action));
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
