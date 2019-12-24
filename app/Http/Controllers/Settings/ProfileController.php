<?php

namespace App\Http\Controllers\Settings;

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
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'country' => 'sometimes',
            'birthday' => 'sometimes',
            'phone' => 'sometimes',
            'address' => 'sometimes',
            'city' => 'sometimes',
            'postal_code' => 'sometimes'
        ]);

        return tap($user)->update($request->only('name','surname','email','country','birthday','phone','address','city','postal_code'));
    }

    public function update_new(Request $request)
    {

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion("2019-10-08");

        $user = $request->user();
        $token = $request->get('token');

        \Stripe\Account::update($user->pl_account_id, [
            'account_token' => $token,
        ]);
    }
}
