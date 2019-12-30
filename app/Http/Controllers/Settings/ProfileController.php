<?php

namespace App\Http\Controllers\Settings;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            'name' => 'sometimes',
            'surname' => 'sometimes',
            'email' => 'sometimes|email|unique:users,email,'.$user->id
        ]);

        return tap($user)->update($request->only('name','surname','email'));
    }

    public function completeRegistration(Request $request)
    {

        $user = $request->user();

        $this->validate($request, [
            'country' => 'required',
            'birthday' => 'required|date|before_or_equal:' . Carbon::now()->subYears(config('custom.limit_user_age'))->toDateString(),
        ]);

        $user->update($request->only('country','birthday'));

        return new \App\Http\Resources\User($user);
    }

    public function neededInfo(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            'phone' => 'required',
            'street' => 'required',
            'city' => 'required',
            'cap' => 'required',
        ]);

        $user->update($request->only('phone','street','city','cap'));

        return new \App\Http\Resources\User($user);
    }

    public function verifyAccount(Request $request)
    {

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");


        $user = $request->user();

        //dd($request);

        $path = '';
        //if($request->hasFile('file') && $request->file('file')->isValid()){
            $path_front = $request->file('file')->store('uploaded_documents');
            $path_back = $request->file('file2')->store('uploaded_documents');



            $file_uploaded_front = \Stripe\File::create([
                'purpose' => 'identity_document',
                'file' => fopen(storage_path('/app/public/' . $path_front), 'r')
            ],
            [
                'stripe_account' => $user->pl_account_id
            ]);

            $file_uploaded_back = \Stripe\File::create([
                'purpose' => 'identity_document',
                'file' => fopen(storage_path('/app/public/' . $path_back), 'r')
            ],
            [
                'stripe_account' => $user->pl_account_id
            ]);


            \Stripe\Account::update(
                $user->pl_account_id,
                [
                    'individual' => [
                        'verification' => [
                            'document' => [
                                'front' => $file_uploaded_front->id,
                                'back' => $file_uploaded_back->id,
                            ],
                            'additional_document' => [
                                'front' => $file_uploaded_front->id,
                                'back' => $file_uploaded_back->id,
                            ]
                        ]
                    ]
                ]
            );

            //dd($file_uploaded);


        //}



    }

    public function bankAccount(Request $request)
    {

        $user = $request->user();
        $bankAccount = $request->bank_account;

        return createExternalAccount($bankAccount);

    }
}
