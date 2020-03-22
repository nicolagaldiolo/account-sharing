<?php

namespace App\Http\Controllers\Settings;

use App\Http\Resources\Balance;
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
        $user_id = Auth::id();

        // Calcolo il saldo in pending
        $invoices = DB::table('invoices')->selectRaw('total_less_fee AS total')
            ->where('user_id', $user_id)
            ->where('transfered', 0);

        $refunds = DB::table('refunds')->select(DB::raw('total_less_fee * -1 AS total'))->join('invoices', 'refunds.payment_intent', 'invoices.payment_intent')->where('invoices.user_id', $user_id);

        $transfers = DB::table('invoices')->selectRaw(DB::raw('total_less_fee * -1 AS total'))
            ->where('user_id', $user_id)
            ->where('transfered', 1);

        $pending = DB::query()->select(DB::raw('SUM(total) as total'))->fromSub(
            $invoices->unionAll($refunds)->unionAll($transfers), 'balance'
        )->get();


        // Calcolo il saldo disponibile
        $transfers_positive = DB::table('invoices')->select('total_less_fee AS total')
            ->where('user_id', $user_id)
            ->where('transfered', 1);
        $payouts = DB::table('payouts')->select(DB::raw('amount * -1 AS total'))->where('account_id', $user_id);

        $available = DB::query()->select(DB::raw('SUM(total) as total'))->fromSub(
            $transfers_positive->unionAll($payouts),
            'balance'
        )->get();


        $balance = [
            'pending' => $pending,
            'available' => $available
        ];

        return new Balance($balance);
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
