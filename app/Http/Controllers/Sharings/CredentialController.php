<?php

namespace App\Http\Controllers\Sharings;

use App\Http\Requests\CredentialRequest;
use App\Mail\CredentialConfirmed;
use App\Mail\CredentialUpdated;
use App\Sharing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Sharing as SharingResource;

class CredentialController extends Controller
{

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

        $this->authorize('manage-own-sharing', $sharing);

        $sharing->update($request->validated());


        // Set the credential_updated_at for the sharing
        $sharing->credential_updated_at = Carbon::now();
        $sharing->save();

        // Remove the credential_updated_at for the sharing users
        $ids = $sharing->users->mapWithKeys(function($user, $key) {
            $user->sharing_status->credential_updated_at = null;
            return [$user->id => $user->sharing_status->only(['credential_updated_at'])];
        })->toArray();

        $sharing->load('members');
        $sharing->members()->syncWithoutDetaching($ids);

        event(New \App\Events\CredentialUpdated($sharing));

        return new SharingResource($sharing);

    }

    public function confirm(Sharing $sharing)
    {
        $this->authorize('confirm-credential', $sharing);

        $sharing->users()->updateExistingPivot(Auth::id(), ['credential_updated_at' => Carbon::now()]);

        $sharing->load('members');

        $sharingUpdated = new SharingResource($sharing);
        event(New \App\Events\CredentialConfirmed(Auth::user(), $sharing));

        return $sharingUpdated;
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
