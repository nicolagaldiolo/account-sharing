<?php

namespace App\Http\Controllers\Settings;

use App\Enums\RefundApplicationStatus;
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
        $user = Auth::user();

        // Get pending balance
        $invoices = DB::table('invoices')->selectRaw('total_less_fee AS total')
            ->where('user_id', $user->id);

        $refunds = DB::table('refunds')->select(DB::raw('total_less_fee * -1 AS total'))->join('invoices', 'refunds.payment_intent', 'invoices.payment_intent')
            ->where('invoices.user_id', $user->id)
            ->where('refunds.internal_status', RefundApplicationStatus::Approved);

        $payouts = DB::table('payouts')->select(DB::raw('amount * -1 AS total'))->where('account_id', $user->pl_account_id);

        $pending = DB::query()->select(DB::raw('SUM(total) as total'))->fromSub(
            $invoices->unionAll($refunds)->unionAll($payouts), 'balance'
        )->get();


        // Get available balance
        $invoices2 = DB::table('invoices')->selectRaw('total_less_fee AS total')
            ->where('user_id', $user->id)
            ->where('transfered', 1);
        $payouts2 = DB::table('payouts')->select(DB::raw('amount * -1 AS total'))->where('account_id', $user->pl_account_id);

        $available = DB::query()->select(DB::raw('SUM(total) as total'))->fromSub(
            $invoices2->unionAll($payouts2), 'balance'
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
