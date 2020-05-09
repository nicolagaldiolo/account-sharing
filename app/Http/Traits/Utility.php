<?php

namespace App\Http\Traits;

use App\Category;
use App\Credential;
use App\Enums\CredentialsStatus;
use App\Enums\RenewalFrequencies;
use App\Enums\SharingStatus;
use App\RenewalFrequency;
use App\Sharing;
use App\SharingUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait Utility
{

    protected function getPrice($price = 0, $capacity = 0, RenewalFrequency $renewalFrequency)
    {

        $stripeFee = $this->convertStripePrice(floatval(config('custom.stripe.stripe_fee')));
        $platformFee = $this->convertStripePrice(floatval(config('custom.stripe.platform_fee')));
        $fee = $stripeFee + $platformFee;
        $netPrice = $price / $capacity;
        $totalPrice = $netPrice + $fee;

        switch ($renewalFrequency->type){
            case RenewalFrequencies::Years:
                $netPrice = $netPrice * 12;
                $totalPrice = $totalPrice * 12;
                $fee = $fee * 12;
                break;
        }

        return [
            'netPrice' => ($netPrice * $renewalFrequency->value),
            'totalPrice' => ($totalPrice * $renewalFrequency->value),
            'fee' => ($fee * $renewalFrequency->value)
        ];

    }

    protected function convertStripePrice($price = 0)
    {
        return $price / 100;
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

    protected function processSharingImage($request)
    {
        $image_file = '';

        if(!empty($request->file('img_file'))){
            $image_file = $request->file('img_file');
        }else if(!empty($request->input('img_string'))){
            $image_file = $this->generateUploadedFile($request->input('img_string'));
        }
        return $image_file;
    }

    protected function generateUploadedFile($file)
    {
        $image_file = '';

        $file_path = (Str::contains($file, URL::to('/'))) ? Str::replaceFirst(URL::to('/') . '/storage/', '', $file) : $file;

        $internal_path = storage_path('app/public') . '/' . $file_path;
        $info = pathinfo($internal_path);

        if (file_exists($internal_path)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $image_file = new UploadedFile($internal_path, $info['basename'], $finfo->file($internal_path));
        }
        return $image_file;
    }

    protected function getRandomImageFromArchive($path)
    {
        return collect(Storage::files($path))->filter(function($file){
            return !Str::endsWith($file, '.DS_Store');
        })->map(function($file){
            return Storage::url($file);
        })->random();
    }

    protected function applyTransition(SharingUser $sharingUser, $transition)
    {
        $sharingUser->refresh(); //Refresh the object because it isn't updated

        if(is_null(Auth::id())){
            $user = User::findOrFail($sharingUser->user_id);
            Auth::login($user);
        }

        if ($transition && $sharingUser->canApply($transition)) {
            $sharingUser->apply($transition);
            $sharingUser->save();
        };

    }
}
