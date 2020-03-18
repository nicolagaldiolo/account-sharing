<?php

namespace App\Http\Traits;

use App\Category;
use App\Credential;
use App\Enums\CredentialsStatus;
use App\Enums\RenewalFrequencies;
use App\Enums\SharingStatus;
use App\RenewalFrequency;
use App\Sharing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait Utility
{
    // utlizzato per settare il campo total_less_fee del modello invoice (capire se serve)
    protected function calcNetPrice($price = 0)
    {
        return (intval($price) > 0) ? (intval($price) - intval(config('custom.stripe.stripe_fee')) - intval(config('custom.stripe.platform_fee'))) : 0;
    }

    protected function getPrice($price = 0, $capacity, RenewalFrequency $renewalFrequency)
    {

        $stripeFee = floatval(config('custom.stripe.stripe_fee')) / 100;
        $platformFee = floatval(config('custom.stripe.platform_fee')) / 100;
        $netPrice = $price / $capacity;
        $totalPrice = $netPrice + $stripeFee + $platformFee;

        switch ($renewalFrequency->type){
            case RenewalFrequencies::Years:
                $netPrice = $netPrice * 12;
                $totalPrice = $totalPrice * 12;
                break;
        }

        return [
            'netPrice' => ($netPrice * $renewalFrequency->value),
            'totalPrice' => ($totalPrice * $renewalFrequency->value)
        ];

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

    protected function getRandomImageFromArchive($path){
        return collect(Storage::files($path))->filter(function($file){
            return !Str::endsWith($file, '.DS_Store');
        })->map(function($file){
            return Storage::url($file);
        })->random();
    }
}
