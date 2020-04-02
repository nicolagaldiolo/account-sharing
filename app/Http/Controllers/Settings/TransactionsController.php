<?php

namespace App\Http\Controllers\Settings;

use App\Enums\RefundApplicationStatus;
use App\Invoice;
use App\Payout;
use App\Refund;
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

        $params = collect($request->only(['type', 'subtype', 'refundtype', 'from', 'to']));

        $user = Auth::user();

        // Filter the transactions type
        $transactions_type = collect([
            'INVOICE' => Invoice::class,
            'REFUNDS' => Refund::class,
            'PAYOUTS' => Payout::class,
        ]);

        if($params->has('type')){
            $transactions_type = $transactions_type->only($params->get('type'));
        }

        // Filter the transaction's direction
        $transactions_subtype = collect([
            'INCOMING' => 'owner',
            'OUTCOMING' => 'user'
        ]);

        if($params->has('subtype')) {
            $transactions_subtype = $transactions_subtype->only($params->get('subtype'));
        }

        $transactions = Transaction::whereHasMorph('transactiontable', $transactions_type->unique()->all(), function (Builder $query, $type) use($user, $transactions_subtype, $params){
            switch ($type){
                case Invoice::class:
                    $query->whereNull('id');
                    if($transactions_subtype->contains('owner')) {
                        $query->orWhereHas('owner', function ($query) use ($user, $type) {
                            $query->where('user_id', $user->id);
                        });
                    };
                    if($transactions_subtype->contains('user')) {
                        $query->orWhereHas('user', function ($query) use($user){
                            $query->where('pl_customer_id', $user->pl_customer_id);
                        });
                    }
                    break;
                case Refund::class:
                    $refundtype = collect(RefundApplicationStatus::getValues());
                    if($params->has('refundtype')) {
                        $refundtype = $refundtype->only($params->get('refundtype'));
                    }
                    $query->whereIn('internal_status', $refundtype);
                    $query->where(function ($query) use($transactions_subtype,$user,$type){
                        $query->whereNull('id');
                        if($transactions_subtype->contains('user')) {
                            $query->orWhereHas('owner', function ($query) use ($user, $type) {
                                $query->where('user_id', $user->id);
                            });
                        };
                        if($transactions_subtype->contains('owner')) {
                            $query->orWhereHas('user', function ($query) use($user){
                                $query->where('pl_customer_id', $user->pl_customer_id);
                            });
                        }
                    });
                    break;
                case Payout::class:
                    $query->where('account_id', $user->pl_account_id);
                    break;
            }
        })->with('transactiontable')->latest();

        if($params->has('from')) {
            $transactions = $transactions->whereDate('created_at', '>=', Carbon::createFromFormat('Y-m-d', $params->get('from')));
        }

        if($params->has('to')) {
            $transactions = $transactions->whereDate('created_at', '<=', Carbon::createFromFormat('Y-m-d', $params->get('to')));
        }

        return TransactionResource::collection($transactions->paginate(config('custom.paginate')));
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
