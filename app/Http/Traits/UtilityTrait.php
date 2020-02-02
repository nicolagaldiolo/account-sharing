<?php

namespace App\Http\Traits;

use App\Category;
use App\Credential;
use App\Enums\CredentialsStatus;
use App\Sharing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait UtilityTrait
{
    protected function calcNetPrice($price = 0)
    {
        return (intval($price) > 0) ? (intval($price) - intval(config('custom.stripe.stripe_fee')) - intval(config('custom.stripe.platform_fee'))) : 0;
    }

    protected function getFreeSlot(Category $category)
    {
        return ($category->capacity) - 1;
    }

    protected function calcCapacity($slot)
    {
        return $slot + 1;
    }

    protected function getCredentials(Sharing $sharing)
    {
        $user = Auth::user();
        return ($user->can('manage-own-sharing', $sharing)) ? $sharing->credentials : $sharing->credentials($user)->get();
    }

    protected function calcCredentialStatus(Sharing $sharing)
    {
        $credential_status = collect(CredentialsStatus::toArray())->map(function ($item){
            return $key = collect([
                'status' => $item,
                'size' => 0
            ]);
        });

        $sharing->members->pluck('sharing_status.credential_status')->each(function ($item) use ($credential_status){
            $credential_status[CredentialsStatus::getKey($item)]['size'] = $credential_status[CredentialsStatus::getKey($item)]['size'] + 1;
        });

        if($sharing->members->count() > 0){
            return $credential_status->sortBy(function ($item){
                return [$item['size'] * -1, $item['status'] * -1];
            })->first()['status'];
        }else{
            return CredentialsStatus::Toverify;
        }
    }

    protected function updateCredential($id, $type, Request $request)
    {
        return Credential::updateOrCreate([
            'credentiable_id' => $id,
            'credentiable_type' => $type,
        ], [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'credential_updated_at' => Carbon::now()
        ]);
    }
}
