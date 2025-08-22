<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MauKhaoSat;
use Illuminate\Auth\Access\HandlesAuthorization;

class MauKhaoSatPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, MauKhaoSat $mauKhaoSat)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->hasPermission('mau_khaosat', 'create');
    }

    public function update(User $user, MauKhaoSat $mauKhaoSat)
    {
        return $user->hasPermission('mau_khaosat', 'edit') ||
            $user->id === $mauKhaoSat->nguoi_tao_id;
    }

    public function delete(User $user, MauKhaoSat $mauKhaoSat)
    {
        return $user->hasPermission('mau_khaosat', 'delete') &&
            $mauKhaoSat->dotKhaoSat()->count() === 0;
    }
}