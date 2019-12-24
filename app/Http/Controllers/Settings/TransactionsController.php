<?php

namespace App\Http\Controllers\Settings;

use App\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction as TransactionResource;
use Illuminate\Support\Facades\Auth;
class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $params = $request->only(['type', 'from', 'to', 'subtype']);

        $user = Auth::user();

        $transactions_type = collect([
            'INVOICE' => 'App\Invoice',
            'REFUNDS' => 'App\Refund',
            'PAYOUTS' => 'App\Payout',
        ]);

        if(array_key_exists('type', $params) && '' != $params['type']) {
            $transactions_type = $transactions_type->only($params['type']);
        }

        $transactions_subtype = collect([
            'INCOMING' => 'owner',
            'OUTCOMING' => 'user'
        ]);

        if(array_key_exists('subtype', $params) && '' != $params['subtype']) {
            $transactions_subtype = $transactions_subtype->only($params['subtype']);
        }

        $transactions = Transaction::whereHasMorph('transactiontable', $transactions_type->all(), function (Builder $query, $type) use($user, $transactions_subtype){

        //$transactions = Transaction::whereHasMorph('transactiontable', $transactions_type->all(), function (Builder $query, $type) use($user){

            $query->whereId('');

            if($transactions_subtype->contains('owner')) {
                $query->orWhereHas('owner', function ($query) use ($user) {
                    $query->where('pl_account_id', $user->pl_account_id);
                });
            };

            if($transactions_subtype->contains('user')) {
                $query->orWhereHas('user', function ($query) use($user){
                    $query->where('pl_customer_id', $user->pl_customer_id);
                });
            }

        })->with('transactiontable')->latest();

        if(array_key_exists('from', $params) && '' != $params['from']) {
            $transactions = $transactions->whereDate('created_at', '>=', Carbon::createFromFormat('Y-m-d', $params['from']));
        }

        if(array_key_exists('to', $params) && '' != $params['to']) {
            $transactions = $transactions->whereDate('created_at', '<=', Carbon::createFromFormat('Y-m-d', $params['to']));
        }

        return TransactionResource::collection($transactions->paginate(20));
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
