<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ActivityLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ActivityLog $activityLog): Response
    {
        return $user->id === $activityLog->user_id
            ? Response::allow()
            : Response::deny('Anda tidak memiliki hak akses untuk melihat riwayat audit ini. Catatan riwayat audit hanya dapat diakses oleh pemilik akun terkait.');
    }
}
