<?php

namespace App\Policies;

use App\Enums\CustodianRole;
use App\Models\CustodianModelConfig;
use App\Models\User;

class CustodianModelConfigPolicy
{
    public function before(User $user): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Any custodian role (admin, approver or read-only) in the same Custodian can view config;
     * only admins change it.
     */
    public function viewByCustodian(User $user, int $custodianId): bool
    {
        return $this->belongsToCustodian($user, $custodianId) && $user->custodian_user?->hasAnyRole() === true;
    }

    public function view(User $user, CustodianModelConfig $config): bool
    {
        return $this->viewByCustodian($user, $config->custodian_id);
    }

    public function create(User $user, int $custodianId): bool
    {
        return $this->canManage($user, $custodianId);
    }

    public function update(User $user, CustodianModelConfig $config): bool
    {
        return $this->canManage($user, $config->custodian_id);
    }

    public function delete(User $user, CustodianModelConfig $config): bool
    {
        return $this->update($user, $config);
    }

    public function updateByCustodian(User $user, int $custodianId): bool
    {
        return $this->canManage($user, $custodianId);
    }

    /**
     * Only a CUSTODIAN_ADMIN of the same Custodian may change config. Read-only wins,
     * even if bad data also gives the user ADMIN.
     */
    private function canManage(User $user, int $custodianId): bool
    {
        if ($this->isCustodianReadOnly($user)) {
            return false;
        }

        return $this->belongsToCustodian($user, $custodianId) && $this->isCustodianAdmin($user);
    }

    private function isCustodianAdmin(User $user): bool
    {
        return $user->custodian_user?->hasPermission(CustodianRole::Admin->value) === true;
    }

    private function isCustodianReadOnly(User $user): bool
    {
        return $user->custodian_user?->isReadOnly() === true;
    }

    private function belongsToCustodian(User $user, int $custodianId): bool
    {
        return optional($user->custodian_user)->custodian_id === $custodianId;
    }
}
