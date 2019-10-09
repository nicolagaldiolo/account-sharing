<?php

namespace App\Http\Controllers\Sharings;

use App\Http\Requests\CredentialRequest;
use App\Http\Traits\SharingTrait;
use App\Sharing;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CredentialController extends Controller
{

    use SharingTrait;

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

    public function update(CredentialRequest $request, Sharing $sharing)
    {
        $sharingUser = $sharing->users()->findOrFail(Auth::id())->sharing_status;
        $this->authorize('manage-own-sharing', $sharingUser);

        $sharing->update($request->validated());

        // Estraggo tutti gli utenti attivi, se sono l'admin setto la data di aggiornamento password as ora mentre
        // per gli altri la spiano

        $ids = $sharing->users->mapWithKeys(function($user, $key) {
            $user->sharing_status->credential_updated_at = ($user->sharing_status->owner === 1) ? Carbon::now() : null;
            return [$user->id => $user->sharing_status->only(['credential_updated_at'])];
        })->toArray();

        $sharing->activeUsers()->syncWithoutDetaching($ids);

        return $this->getSharing($sharing);
    }

    public function confirm(Sharing $sharing)
    {
        $this->authorize('confirm-credential', $sharing);

        $sharing->users()->updateExistingPivot(Auth::id(), ['credential_updated_at' => Carbon::now()]);

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
