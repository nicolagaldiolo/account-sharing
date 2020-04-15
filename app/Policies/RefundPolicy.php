<?php

namespace App\Policies;

use App\Enums\RefundApplicationStatus;
use App\User;
use App\Refund;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefundPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any refunds.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the refund.
     *
     * @param  \App\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function view(User $user, Refund $refund)
    {
        //
    }

    /**
     * Determine whether the user can create refunds.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the refund.
     *
     * @param  \App\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function update(User $user, Refund $refund)
    {
        //
    }

    /**
     * Determine whether the user can delete the refund.
     *
     * @param  \App\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function delete(User $user, Refund $refund)
    {
        return $user->id === $refund->user->id && $refund->internal_status === RefundApplicationStatus::Pending;
    }

    /**
     * Determine whether the user can restore the refund.
     *
     * @param  \App\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function restore(User $user, Refund $refund)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the refund.
     *
     * @param  \App\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function forceDelete(User $user, Refund $refund)
    {
        //
    }

    public function adminUpdate(User $user, Refund $refund)
    {
        logger("passo di qui");
        return $user->isAdmin && $refund->internal_status === RefundApplicationStatus::Pending;
    }
}
