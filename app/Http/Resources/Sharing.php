<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Member as MemberResource;

class Sharing extends JsonResource
{

    private $user;

    public function __construct($resource, \App\User $user = null)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;

        $this->user = $user;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $status = null;

        // If user provided get the sharingUser for that user otherwise use the logged user
        $sharingUser = is_null($this->user) ? $this->sharingUser : $this->sharingUser($this->user)->first();

        if(!is_null($sharingUser)){
            $stateMachine = $sharingUser->stateMachine();
            $status = [
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
            'name' => $this->name,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'availability' => $this->availability,
            'price' => $this->price,
            'image' => $this->image,

            //$this->mergeWhen($request->is('api/sharings/*'), [
                $this->mergeWhen(Auth::user()->can('manage-sharing', $this), [
                    'username' => $this->username,
                    'password' => $this->password,
                    'credential_updated_at' => $this->credential_updated_at,
                ]),

                $this->mergeWhen($status, [
                    'status' => $status
                ]),
                'members' => MemberResource::collection($this->whenLoaded('members')),
                //'created_at' => $this->created_at,



                /*
                 *
                 */
                //'visibility' => $this->visibility,
                //'renewal_frequency_id' => $this->renewal_frequency_id,
                //'category_id' => $this->category_id,
                //'owner_id' => $this->owner_id,
                //'category' => $this->category,
                'owner' => $this->owner,
                //'visility_list' => $this->visility_list,
                //'sharing_state_machine' => $this->sharing_state_machine,
                //'active_users_without_owner' => $this->activeUsersWithoutOwner,
                //'active_users' => $this->members()->get()->each(function($user){
                //    return $user->sharing_status->subscription;
                //})
            //])
        ];

    }
}
