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
            'name' => $this->name,
            'surname' => $this->surname,
            'birthday' => $this->birthday,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'pl_account_id' => $this->pl_account_id,
            'pl_customer_id' => $this->pl_customer_id,
            'country' => $this->country,
            'phone' => $this->phone,
            'street' => $this->street,
            'city' => $this->city,
            'cap' => $this->cap,
            'tos_acceptance_at' => $this->tos_acceptance_at,
            'photo_url' => $this->photo_url,
            'username' => $this->username,
            'registration_completed' => $this->registration_completed,
            'additional_data_needed' => $this->additional_data_needed
        ];

    }
}
