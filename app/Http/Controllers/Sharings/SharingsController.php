<?php

namespace App\Http\Controllers\Sharings;

use App\Category;
use App\Enums\PaymentStatus;
use App\Enums\SharingStatus;
use App\Http\Requests\SharingRequest;
use App\Sharing;
use App\SharingUser;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SharingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $param = $request->input('type', '');

        switch ($param){
            case 'pending':
                $sharings = Auth::user()->sharings()->pending()->get();
                break;
            case 'approved':
                $sharings = Auth::user()->sharings()->approved()->get();
                break;
            case 'owner':
                // manipolo i dati tornati raggruppando gli utenti per stato della relazione con sharing(es: pendind: utenti..., joined: utenti...)
                $sharings = $this->getSharingOwners();
                break;
            case 'joined':
                $sharings = Auth::user()->sharings()->joined()->get();
                break;
            default:
                logger("Entro qui");
                $sharings = Sharing::public()->get();
                break;
        }

        return $sharings;
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
    public function store(SharingRequest $request)
    {
        $user = $request->user();
        return $user->sharingOwners()->create($request->validated());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Sharing $sharing)
    {

        $sharing->load(['category', 'activeUsers', 'users' => function($query) {
            return $query->where('users.id', Auth::id());
        }]);

        return $sharing;

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



    public function transition(Request $request, Sharing $sharing, $transition)
    {

        $sharing = Auth::user()->sharings()->where('sharings.id', $sharing->id)->first()->sharing_status;
        $stateMachine = \StateMachine::get($sharing, 'sharing');

        if($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            return tap($sharing)->save();
        }
        return $sharing;

    }

    public function transitionUser(Request $request, Sharing $sharing, User $user, $transition)
    {

        $sharing_status = $user->sharings()->where('sharings.id', $sharing->id)->first()->sharing_status;
        $stateMachine = \StateMachine::get($sharing_status, 'sharing');

        if($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $sharing_status->save();
        }
        return $this->getSharingOwners($sharing->id);
    }

    public function join(Request $request, Sharing $sharing)
    {
        Auth::user()->sharings()->syncWithoutDetaching([$sharing->id]);
        return $sharing;
    }

    public function payment(Request $request, Sharing $sharing)
    {
        $sharing = Auth::user()->sharings()->where('sharings.id', $sharing->id)->first();

        if(!is_null($sharing)){
            $next_payment = $sharing->calcNextPayment();
            $sharingUser = $sharing->sharing_status;

            $stateMachine = \StateMachine::get($sharingUser, 'sharing');
            if($stateMachine->can('pay')){
                $sharingUser->payments()->create([
                    'status' => PaymentStatus::Successful,
                    'expire_on' => $next_payment
                ]);
                $stateMachine->apply('pay');
                $sharingUser->save();
            }
        }
    }

    /*
    public function requestToManage()
    {
        $status = 1;
        return Auth::user()->sharingOwners()->byStatus($status)->get();
    }
    */

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

    protected function getSharingOwners($id = null)
    {
        $sharings = Auth::user()->sharingOwners();
        if($id) $sharings->whereId($id);

        return $sharings->with('users')->get()->each(function($sharing){
            $sharing['sharing_status'] = collect(SharingStatus::getInstances())->each(function($sharingStatus) use($sharing){

                $users = $sharing->users->where('sharing_status.status', $sharingStatus->value)->each(function($user) use($sharing){
                    $sharing_obj = $user->sharings()->whereSharingId($sharing->id)->first()->sharing_status;
                    $user->possible_transitions = \StateMachine::get($sharing_obj, 'sharing')->getPossibleTransitions();
                });
                $sharingStatus->users = $users->values();
            });
        });
    }
}
