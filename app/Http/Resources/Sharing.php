<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Member as MemberResource;
use App\Http\Resources\Category as CategoryResource;

class Sharing extends JsonResource
{
    private $user;

    public function __construct($resource, $user = null)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
        $this->user = ($user instanceof \App\User) ? $user : null;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public function toArray($request)
    {
        $userStatus = null;

        // If user provided get the sharingUser for that user otherwise use the logged user
        $sharingUser = is_null($this->user) ? $this->sharingUser : $this->sharingUser($this->user)->first();

        if(!is_null($sharingUser)){
            $stateMachine = $sharingUser->stateMachine();
            $userStatus = [
                'id' => $sharingUser->id,
                'state' => [
                    'value' => $stateMachine->getState(),
                    'metadata' => $stateMachine->metadata('state'),
                ],
                'transitions' => collect($stateMachine->getPossibleTransitions())->map(function ($value) use ($stateMachine) {
                    return [
                        'value' => $value,
                        'metadata' => $stateMachine->metadata()->transition($value)
                    ];
                })->all()
            ];
        }

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'availability' => $this->availability,
            'status' => $this->status,
            'price' => $this->price,
            'price_with_fee' => round($this->priceWithFee, 2),
            'price_no_fee' => round($this->priceNoFee, 2),
            'multiaccount' => $this->multiaccount,
            'credential_status' => $this->credentialStatus,
            'image' => $this->publicImage,
            'created_at' => $this->created_at,
            'owner' => new MemberResource($this->whenLoaded('owner')),
            'renewal_frequency' => $this->renewalFrequency->frequency,
            'users' => MemberResource::collection($this->whenLoaded('users')),
            'visibility' => $this->visibility,
            'category' => new CategoryResource($this->whenLoaded('category')),
            //$this->mergeWhen($request->is('api/sharings/*'), [
                $this->mergeWhen(Auth::user()->can('manage-sharing', $this), [
                    'members' => MemberResource::collection($this->whenLoaded('members')),
                ]),

                $this->mergeWhen($userStatus, [
                    'user_status' => $userStatus
                ]),
            //])

        ];

    }
}
