<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Project;
use App\Models\Affiliation;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\ProjectHasUser;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class TestController extends Controller
{
    use Responses;
    
    public function test(Request $request)
    {
        $projectId = 1;
        $project = Project::find($projectId);

        // if (!Gate::allows('viewProjectUserDetails', $project)) {
        //     return $this->ForbiddenResponse();
        // };

        $userProjectFilter = request()->get('user_project_filter');
        if ($userProjectFilter && !in_array(strtoupper($userProjectFilter), ['IN', 'NOT_IN'])) {
            return $this->ErrorResponse('Invalid project filter.');
        }

        $users = User::searchViaRequest()
            ->where('user_group', User::GROUP_USERS)
            ->filterByState()
            ->when(strtoupper($userProjectFilter) === ProjectHasUser::USER_IN_PROJECT, function ($query) use ($projectId) {
                $query->whereHas('registry.projectUsers', function ($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                });
            })
            ->when(strtoupper($userProjectFilter) === ProjectHasUser::USER_NOT_IN_PROJECT, function ($query) use ($projectId) {
                $query->whereDoesntHave('registry.projectUsers', function ($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                });
            })
            ->with([
                'modelState',
                'registry.affiliations',
                'registry.affiliations.organisation',
                'registry.projectUsers.role',
                'registry.projectUsers.affiliation'
            ])
            ->get();

        $idCounter = 1;

        $expandedUsers = $users->flatMap(function ($user) use ($projectId, &$idCounter) {
            // Get affiliations used in THIS project
            $projectAffiliations = $user->registry->affiliations
                ->filter(function ($affiliation) use ($user, $projectId) {
                    return $user->registry->projectUsers->contains(function ($projectUser) use ($affiliation, $projectId) {
                        return $projectUser->affiliation_id == $affiliation->id 
                            && $projectUser->project_id == $projectId; // Added project check
                    });
                });

            // If no affiliations for this project, include ALL affiliations (or handle differently)
            if ($projectAffiliations->isEmpty()) {
                $projectAffiliations = $user->registry->affiliations; // or return empty, depends on your needs
            }

            return $projectAffiliations->map(function ($affiliation) use ($user, $projectId, &$idCounter) {
                return $this->formatProjectUserAffiliation($affiliation, $user, $projectId, $idCounter++);
            });
        });

        // $expandedUsers = $users->flatMap(function ($user) use ($projectId, &$idCounter) {
        //     return $user->registry->affiliations
        //         ->filter(function ($affiliation) use ($user) {
        //             return $user->registry->projectUsers->contains(function ($projectUser) use ($affiliation) {
        //                 return $projectUser->affiliation_id == $affiliation->id;
        //             });
        //         })
        //         ->map(function ($affiliation) use ($user, $projectId, &$idCounter) {
        //             return $this->formatProjectUserAffiliation($affiliation, $user, $projectId, $idCounter++);
        //         });
        // });

        // $expandedUsers = $users->getCollection()->flatMap(function ($user) use ($projectId, &$idCounter) {
        //     return $user->registry->affiliations
        //         ->filter(function ($affiliation) use ($user) {
        //             return $user->registry->projectUsers->contains(function ($projectUser) use ($affiliation) {
        //                 return $projectUser->affiliation_id == $affiliation->id;
        //             });
        //         })
        //         ->map(function ($affiliation) use ($user, $projectId, &$idCounter) {
        //             return $this->formatProjectUserAffiliation($affiliation, $user, $projectId, $idCounter++);
        //         });
        // });

        // $paginatedResult = new \Illuminate\Pagination\LengthAwarePaginator(
        //     $expandedUsers,
        //     $expandedUsers->total(),
        //     $expandedUsers->perPage(),
        //     $expandedUsers->currentPage(),
        //     ['path' => $request->url(), 'query' => $request->query()]
        // );

        // return $this->OKResponse($paginatedResult);

        // $perPage = request()->get('per_page', 25);
        // $currentPage = LengthAwarePaginator::resolveCurrentPage();
        // $currentItems = $expandedUsers->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // $paginatedUsers = new LengthAwarePaginator(
        //     $currentItems,
        //     $expandedUsers->count(),
        //     $perPage,
        //     $currentPage,
        //     ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        // );

        // return $this->OKResponse($paginatedUsers);


        return response()->json([
            'message' => 'success',
            // 'users' => $users,
            'expanded_users' => $expandedUsers,
        ], 200);
    }

    public function formatProjectUserAffiliation(Affiliation $affiliation, User $user, int $projectId, $idCounter): array
    {
        $matchingProjectUser = $user->registry->projectUsers
            ->first(function ($projectUser) use ($projectId, $affiliation) {
                return $projectUser->project_id == $projectId &&
                    $projectUser->affiliation_id == $affiliation->id;
            });

        return [
            'id' => $idCounter,
            'project_user_id' => $matchingProjectUser?->id,
            'user_id' => $user->id,
            'registry_id' => $user->registry_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'professional_email' => $affiliation->email,
            'affiliation_id' => $affiliation->id,
            'organisation_name' => $affiliation->organisation?->organisation_name,
            'role' => $matchingProjectUser?->role,
        ];
    }
}
