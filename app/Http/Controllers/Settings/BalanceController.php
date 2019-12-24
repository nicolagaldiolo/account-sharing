<?php

namespace App\Http\Controllers\Settings;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $account_id = Auth::user()->pl_account_id;

        // Calcolo il saldo in pending
        $invoices = DB::table('invoices')->selectRaw('total_less_fee AS total, currency')->where('account_id', $account_id);
        $refunds = DB::table('refunds')->select(DB::raw('total_less_fee * -1 AS total'), 'currency')->join('invoices', 'refunds.payment_intent', 'invoices.payment_intent')->where('invoices.account_id', $account_id);
        $transfers = DB::table('transfers')->select(DB::raw('amount * -1 AS total'), 'currency')->where('account_id', $account_id);

        $pending = DB::query()->select(DB::raw('SUM(total) as total, currency'))->fromSub(
            $invoices->unionAll($refunds)->unionAll($transfers),
            'alias'
        )->groupBy('currency')->get();


        // Calcolo il saldo disponibile
        $transfers_positive = DB::table('transfers')->select(DB::raw('amount AS total'), 'currency')->where('account_id', $account_id);
        $payouts = DB::table('payouts')->select(DB::raw('amount * -1 AS total'), 'currency')->where('account_id', $account_id);

        $available = DB::query()->select(DB::raw('SUM(total) as total, currency'))->fromSub(
            $transfers_positive->unionAll($payouts),
            'alias'
        )->groupBy('currency')->get();


        return [
            'pending' => $pending,
            'available' => $available,
            'dayofdelay' => (int) config('custom.day_refund_limit')
        ];
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
