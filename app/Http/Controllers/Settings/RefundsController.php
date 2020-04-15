<?php

namespace App\Http\Controllers\Settings;

use App\Enums\RefundApplicationStatus;
use App\Events\RefundRequest;
use App\Events\RefundResponse;
use App\Http\Resources\Transaction;
use App\Http\Traits\Utility;
use App\Invoice;
use App\Refund;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class RefundsController extends Controller
{
    use Utility;

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
        // Get the Invoice
        $invoice = Invoice::where('payment_intent', $request->only('payment_intent'))->firstOrFail();

        // Authorization Request
        $this->authorize('refund', $invoice);

        // Validation
        $this->validate($request, [
            'payment_intent' => 'unique:refunds',
            'reason' => 'required|string|max:500',
        ]);

        $refund = $invoice->refund()->create([
            'internal_status' => RefundApplicationStatus::Pending,
            'reason' => $request->input('reason')
        ]);

        $transaction = $refund->transactions()->where('transactiontable_id', $refund->id)->with('transactiontable')->firstOrFail();

        return new Transaction($transaction);
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
    public function destroy(Refund $refund)
    {
        // Authorization
        $this->authorize('delete', $refund);
        $refund->delete();

        $transaction = \App\Transaction::where('transactiontable_id', $refund->invoice->id)->with('transactiontable')->firstOrFail();
        return new Transaction($transaction);
    }
}
