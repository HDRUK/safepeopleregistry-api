<?php

namespace App\Rules\Users;

use App\Models\State;
use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class AffiliatedOrganisation extends BaseRule
{
    protected $tag = AffiliatedOrganisation::class;

    public function evaluate($model, array $conditions): bool
    {
        $verdict = false;
        $path = $conditions['path'] ?? 'registry.affiliations';
        $affiliationArray = Arr::get($model, $path, null);

        foreach ($affiliationArray as $a) {
            if ($a['registryAffiliationState'] === State::AFFILIATION_APPROVED) {
                if ($a['from'] !== '' && $a['to'] === '') {
                    // Assume current affiliation
                    $verdict = true;
                }
            }
        }

        return $verdict;
    }
}
