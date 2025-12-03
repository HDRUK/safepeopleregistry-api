<?php


namespace App\Traits;

use App\Models\Organisation;

trait TracksModelChanges
{
    public function getUserTrackedChanges($oldUser, $newUser): array
    {
        $changes = [];

        $fieldsToTrack = ['first_name', 'last_name', 'email', 'role', 'location'];

        foreach ($fieldsToTrack as $field) {
            if ($oldUser[$field] !== $newUser[$field]) {
                $changes[$field] = [
                    'old' => $oldUser[$field],
                    'new' => $newUser[$field],
                ];
            }
        }

        if ($oldUser['organisation_id'] !== $newUser['organisation_id']) {
            $old = $oldUser['organisation_id']
                ? Organisation::find($oldUser['organisation_id'])
                : null;

            $new = $newUser['organisation_id']
                ? Organisation::find($newUser['organisation_id'])
                : null;

            $changes['organisation'] = [
                'old' => $old->organisation_name ?? 'N/A',
                'new' => $new->organisation_name ?? 'N/A',
            ];
        }

        return $changes;
    }
}