<?php

namespace App\Http\Controllers\Sharings;

use App\Category;
use App\Enums\RenewalStatus;
use App\Enums\SharingStatus;
use App\Http\Requests\SharingRequest;
use App\Sharing;
use App\SharingUser;
use App\User;
use const http\Client\Curl\AUTH_ANY;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

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
        return $this->getSharing($sharing);
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
        // Sistemare questa cosa
        // sono costretto a fare la query per avere i dati in più che mi servono per lo sharing
        $sharing = Auth::user()->sharings()->where('sharings.id', $sharing->id)->first();
        $sharingUser = $sharing->sharing_status;

        $stateMachine = \StateMachine::get($sharingUser, 'sharing');

        if($stateMachine->can($transition)) {
            switch ($transition){
                case 'pay':
                    $next_renewal = $sharing->calcNextRenewal();
                    $sharingUser->renewals()->create([
                        'status' => RenewalStatus::Confirmed,
                        'expires_at' => $next_renewal
                    ]);
                    $sharingUser->renewals()->create([
                        'status' => RenewalStatus::Pending,
                        'expires_at' => $sharing->calcNextRenewal($next_renewal)
                    ]);
                    // da capire se la transazione di salvataggio va fatta così oppure sia meglio
                    // creare un nuovo state machine e condizione il cambiamento di stato solo al pagamento completato,
                    // ora invece il controllo non c'è.
                    $stateMachine->apply($transition);
                    $sharingUser->save();
                    break;
                default:
                    $stateMachine->apply($transition);
                    $sharingUser->save();
                    break;
            }
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

    public function renewalAction(Request $request, Sharing $sharing, User $user, $action)
    {
        switch ($action) {
            case 'left':
                $user->sharings()->where('sharings.id', $sharing->id)->first()->sharing_status->renewals()->whereStatus(RenewalStatus::Pending)->orderBy('id', 'desc')->first()->update([
                    'status' => RenewalStatus::Stopped
                ]);
                break;
            case 'restore':
                $user->sharings()->where('sharings.id', $sharing->id)->first()->sharing_status->renewals()->whereStatus(RenewalStatus::Stopped)->orderBy('id', 'desc')->first()->update([
                    'status' => RenewalStatus::Pending
                ]);
                break;
        }

        return $this->getSharing($sharing);
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

    protected function getSharing(Sharing $sharing)
    {

        $sharing->load(['category']);
        $sharing->sharing_state_machine = $this->getSharingStateMachineAttribute($sharing);

        $sharing->active_users = $sharing->activeUsers()->get()->each(function($user) use($sharing){

            if(Auth::id() === $sharing->owner_id || Auth::id() == $user->id ) {
                $renewal = $user->sharings()->where('sharings.id', $sharing->id)->first()->sharing_status->renewals()->orderBy('expires_at', 'desc')->first();
                if ($renewal) {
                    $user->manageable = true;
                    $user->renewalInfo = [
                        'renewal_status' => $renewal->status,
                        'renewal_date' => $renewal->expire_on,
                        'refund_day_limit' => $renewal->expire_on->subDays(config('custom.day_refund_limit'))
                    ];
                }
            }else{
                $user->manageable = false;
            }
            return $user;
        });

        return $sharing;
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

    public function getSharingStateMachineAttribute($item){
        $sharing = Auth::user()->sharings()->where('sharings.id', $item->id)->first();

        if(!is_null($sharing)){
            $stateMachine = \StateMachine::get($sharing->sharing_status, 'sharing');
            return [
                'status' => [
                    'value' => $stateMachine->getState(),
                    'metadata' => $stateMachine->metadata('state'),
                ],
                'transitions' => collect([])->merge(collect($stateMachine->getPossibleTransitions())->map(function($value) use($stateMachine){
                    return [
                        'value' => $value,
                        'metadata' => $stateMachine->metadata()->transition($value)
                    ];
                }))->all(), // altrimenti non mi torna un array;
            ];
        }else{
            return null;
        }
    }

}
