<?php

namespace App\Http\Controllers\Sharings;

use App\Credential;
use App\Enums\CredentialsStatus;
use App\Http\Requests\CredentialRequest;
use App\Http\Traits\UtilityTrait;
use App\Mail\CredentialConfirmed;
use App\Mail\CredentialUpdated;
use App\Sharing;
use App\SharingUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Sharing as SharingResource;
use App\Http\Resources\Credential as CredentialResource;

class CredentialController extends Controller
{
    use UtilityTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Sharing $sharing)
    {
        $this->authorize('manage-sharing', $sharing);

        return CredentialResource::collection($this->getCredentials($sharing));
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

    public function update(Request $request, Sharing $sharing, User $recipient=null)
    {
        $this->authorize('manage-own-sharing', $sharing);

        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);

        if($sharing->multiaccount){
            $sharingUser = $sharing->sharingUser($recipient)->firstOrFail();
            $credential = $this->updateCredential($sharingUser->id, SharingUser::class, $request);
            $sharingUser->update([
                'credential_status' => CredentialsStatus::Toverify
            ]);
        }else{
            $credential = $this->updateCredential($sharing->id, Sharing::class, $request);
            $ids = $sharing->users->mapWithKeys(function($user, $key) {
                $user->sharing_status->credential_status = CredentialsStatus::Toverify;
                return [$user->id => $user->sharing_status->only(['credential_status'])];
            })->toArray();
            $sharing->members()->syncWithoutDetaching($ids);
        }

        $sharing->load(['owner', 'members']);
        event(New \App\Events\CredentialUpdated($sharing, $recipient));

        return new CredentialResource($credential);

    }

    public function confirm(Sharing $sharing, $action)
    {
        $action = intval($action);

        $this->authorize('confirm-credential', [$sharing, $action]);

        $sharing->users()->updateExistingPivot(Auth::id(), ['credential_status' => $action]);

        $sharing->load(['owner', 'members']);
        event(New \App\Events\CredentialConfirmed(Auth::user(), $sharing, $action));

        return new SharingResource($sharing);
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

    public function get(Sharing $sharing)
    {
        $this->authorize('ask-credential', $sharing);

        $sharing->owner->notify(new \App\Notifications\AskCredentials($sharing, Auth::user()));

        return new SharingResource($sharing);

    }
}
