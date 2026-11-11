<?php

namespace App\Policies;

use App\Enums\CustodianRole;
use App\Models\CustodianUser;
use App\Models\User;

class CustodianUserPolicy
{
    public function before(User $user): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        if ($this->isCustodianReadOnly($user)) {
            return false;
        }

        return $this->isCustodianAdmin($user) || $this->isCustodianApprover($user);
    }

    public function update(User $user, CustodianUser $custodianUser): bool
    {
        // Read-only wins, even if bad data also gives the user ADMIN or APPROVER.
        if ($this->isCustodianReadOnly($user)) {
            return false;
        }

        if (! $this->sameCustodian($user, $custodianUser)) {
            return false;
        }

        if ($this->isCustodianAdmin($user)) {
            return true;
        }

        // A CUSTODIAN_APPROVER may manage other custodian users, but not a CUSTODIAN_ADMIN.
        return $this->isCustodianApprover($user) && ! $custodianUser->hasPermission(CustodianRole::Admin->value);
    }

    public function delete(User $user, CustodianUser $custodianUser): bool
    {
        return $this->update($user, $custodianUser);
    }

    public function invite(User $user, CustodianUser $custodianUser): bool
    {
        return $this->update($user, $custodianUser);
    }

    private function isCustodianAdmin(User $user): bool
    {
        return $user->custodian_user?->hasPermission(CustodianRole::Admin->value) === true;
    }

    private function isCustodianApprover(User $user): bool
    {
        return $user->custodian_user?->hasPermission(CustodianRole::Approver->value) === true;
    }

    private function isCustodianReadOnly(User $user): bool
    {
        return $user->custodian_user?->isReadOnly() === true;
    }

    private function sameCustodian(User $user, CustodianUser $custodianUser): bool
    {
        return optional($user->custodian_user)->custodian_id === $custodianUser->custodian_id;
    }
}
