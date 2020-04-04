<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'username' => $this->username,
            'birthday' => $this->birthday,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'pl_account_id' => $this->pl_account_id,
            'pl_customer_id' => $this->pl_customer_id,
            'country' => $this->country,
            'currency' => $this->currency,
            'phone' => $this->phone,
            'street' => $this->street,
            'city' => $this->city,
            'cap' => $this->cap,
            'tos_acceptance_at' => $this->tos_acceptance_at,
            'photo_url' => $this->photo_url,
            'registration_completed' => $this->registration_completed,
            'additional_data_needed' => $this->additional_data_needed,
            'admin' => $this->isAdmin
        ];

    }
}
