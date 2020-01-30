<?php

namespace App\Http\Resources;

use App\SharingUser;
use Illuminate\Http\Resources\Json\JsonResource;

class Credential extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);

        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'credential_updated_at' => $this->credential_updated_at,
            $this->mergeWhen($this->credentiable_type === SharingUser::class, [
                'user_id' => $this->credentiable->user_id
            ])
        ];
    }
}
