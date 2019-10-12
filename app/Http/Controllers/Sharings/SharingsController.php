<?php

namespace App\Http\Controllers\Sharings;

use App\Enums\RenewalStatus;
use App\Enums\SharingStatus;
use App\Http\Requests\CredentialRequest;
use App\Http\Requests\SharingRequest;
use App\Http\Traits\SharingTrait;
use App\Sharing;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SharingsController extends Controller
{
    use SharingTrait;

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

    public function prova(Sharing $sharing)
    {

        return $sharing;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SharingRequest $request)
    {
        $sharing = Sharing::create($request->validated());
        return Auth::user()->sharings()->save($sharing, [
            'status' => SharingStatus::Joined,
            'owner' => true
        ]);

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

    public function transition(Request $request, Sharing $sharing, $transition = null)
    {
        // Cerco la relazione tra utente e sharing, se non esiste la creo
        $userSharing = Auth::user()->sharings()->find($sharing->id);

        if(!$userSharing) {
            Auth::user()->sharings()->attach($sharing->id);
            $userSharing = Auth::user()->sharings()->find($sharing->id);
        }

        $sharingStatus = $userSharing->sharing_status;
        $stateMachine = \StateMachine::get($sharingStatus, 'sharing');

        if($transition && $stateMachine->can($transition)) {
            switch ($transition){
                case 'pay':

                    $next_renewal = $userSharing->calcNextRenewal();

                    $current_renewal = $sharingStatus->renewals()->create([
                        'status' => RenewalStatus::Confirmed,
                        'starts_at' => Carbon::now()->startOfDay(),
                        'expires_at' => $next_renewal
                    ]);

                    $sharingStatus->renewals()->create([
                        'status' => RenewalStatus::Pending,
                        'starts_at' => $current_renewal->expires_at->addDay()->startOfDay(),
                        'expires_at' => $userSharing->calcNextRenewal($next_renewal)
                    ]);

                    // da capire se la transazione di salvataggio va fatta così oppure sia meglio
                    // creare un nuovo state machine e condizione il cambiamento di stato solo al pagamento completato,
                    // ora invece il controllo non c'è.
                    $stateMachine->apply($transition);
                    $sharingStatus->save();
                    break;
                default:
                    $stateMachine->apply($transition);
                    $sharingStatus->save();
                    break;
            }
        }
        return $this->getSharing($userSharing);


    }

    public function transitionUser(Request $request, Sharing $sharing, User $user, $transition)
    {
        $sharing_status = $user->sharings()->findOrFail($sharing->id)->sharing_status;
        $stateMachine = \StateMachine::get($sharing_status, 'sharing');

        if($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $sharing_status->save();
        }
        return $this->getSharingOwners($sharing->id);
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

}
