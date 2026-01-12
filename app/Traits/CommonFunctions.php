<?php

namespace App\Traits;

use Str;
use App\Models\User;
use App\Models\State;
use App\Models\SystemConfig;

trait CommonFunctions
{
    public function getSystemConfig(string $name): ?string
    {
        $systemConfig = SystemConfig::where('name', $name)->first();
        if ($systemConfig) {
            return $systemConfig->value;
        }

        return null;
    }

    public function mapModelFromString(string $model): string
    {
        $modelName = '\\App\\Models\\'.Str::studly(Str::singular($model));

        return $modelName;
    }

    public function validateModelStateFilter(array $filter): bool
    {
        foreach ($filter as $f) {
            if (!in_array($f, State::STATES)) {
                return false;
            }
        }

        return true;
    }

    public function getUsersFromOrganisationById(?int $organisationId)
    {
        $users = User::query()
            ->where([
                'organisation_id' => $organisationId,
                'is_delegate' => 1
            ])
            ->get();

        if ($users->isEmpty()) {
            $users = User::query()
                ->where([
                    'organisation_id' => $organisationId,
                    'is_sro' => 1
                ])
                ->get();
        }

        return $users;
    }

}
