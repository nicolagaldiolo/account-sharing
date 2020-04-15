<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RefundApplicationStatus;
use App\Events\RefundResponse;
use App\Refund;
use App\Subscription;
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
        return \App\Http\Resources\Admin\Refund::collection(Refund::pending()->with([
            'user',
            'invoice.subscription.sharingUser.sharing'
        ])->paginate());
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

    public function update(Request $request, Refund $refund)
    {
        $this->authorize('adminUpdate', $refund);

        $action = Str::upper($request->get('action', ''));

        switch ($action){
            case 'APPROVE' :
                \Stripe\Refund::create([
                    'payment_intent' => $refund->invoice->payment_intent,
                ]);
                break;
            case 'REFUSE' :
                $refund->update([
                    'internal_status' => RefundApplicationStatus::Refused
                ]);
                break;
            default :
                abort(403, 'Action not authorized');
                break;
        }

        return new \App\Http\Resources\Admin\Refund($refund);
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
