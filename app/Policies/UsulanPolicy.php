<?php

namespace App\Policies;

use App\Models\Usulan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UsulanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_usulan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Usulan $usulan): bool
    {
        return $user->can('view_usulan');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_usulan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Usulan $usulan): bool
    {
        return $user->can('update_usulan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Usulan $usulan): bool
    {
        return $user->can('delete_usulan');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_usulan');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Usulan $usulan): bool
    {
        return $user->can('force_delete_usulan');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_usulan');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Usulan $usulan): bool
    {
        return $user->can('restore_usulan');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_usulan');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Usulan $usulan): bool
    {
        return $user->can('replicate_usulan');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_usulan');
    }
}
