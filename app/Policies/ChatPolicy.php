<?php

namespace App\Policies;

use App\Sharing;
use App\User;
use App\Chat;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any chats.
     *
     * @param  \App\User  $user
     * @return mixed
     */

    public function viewAnyChats(User $user, Sharing $sharing)
    {
        $sharing->load('activeUsers');
        return $user->id === $sharing->owner_id || $sharing->activeUsers()->get()->pluck('id')->contains($user->id);
    }

    /**
     * Determine whether the user can view the chat.
     *
     * @param  \App\User  $user
     * @param  \App\Chat  $chat
     * @return mixed
     */
    public function view(User $user, Chat $chat)
    {
        //
    }

    /**
     * Determine whether the user can create chats.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the chat.
     *
     * @param  \App\User  $user
     * @param  \App\Chat  $chat
     * @return mixed
     */
    public function update(User $user, Chat $chat)
    {
        //
    }

    /**
     * Determine whether the user can delete the chat.
     *
     * @param  \App\User  $user
     * @param  \App\Chat  $chat
     * @return mixed
     */
    public function delete(User $user, Chat $chat)
    {
        //
    }

    /**
     * Determine whether the user can restore the chat.
     *
     * @param  \App\User  $user
     * @param  \App\Chat  $chat
     * @return mixed
     */
    public function restore(User $user, Chat $chat)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the chat.
     *
     * @param  \App\User  $user
     * @param  \App\Chat  $chat
     * @return mixed
     */
    public function forceDelete(User $user, Chat $chat)
    {
        //
    }
}
