<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Registry;
use App\Models\Project;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\ProjectHasUser;
use App\Models\ProjectHasCustodian;
use App\Models\WebhookEventTrigger;
use App\Models\CustodianWebhookReceiver;
use App\Models\ProjectHasOrganisation;
use Spatie\WebhookServer\WebhookCall;
use App\Traits\ValidationManager;
use Illuminate\Support\Facades\Auth;
use App\Jobs\UpdateProjectUserValidation;
use App\Models\CustodianHasProjectUser;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use App\Notifications\ProjectHasUser\ProjectHasUserCreatedEntityUser;
use App\Notifications\ProjectHasUser\ProjectHasUserCreatedEntityOrganisation;
use App\Notifications\ProjectHasUser\ProjectHasUserCreatedEntityCustodian;

use function activity;

class ProjectHasUserObserver
{
    use ValidationManager;

    public const WEBHOOK_EVENT_TRIGGER_NAME = 'user-left-project';

    /**
     * Handle the ProjectHasUser "created" event.
     */
    public function created(ProjectHasUser $projectHasUser): void
    {
        $user = $projectHasUser->registry->user;
        $project = $projectHasUser->project;
        $affiliation = $projectHasUser->affiliation;
        $role = $projectHasUser->role;

        if ($affiliation) {
            $organisationId = $affiliation->organisation->id;

            ProjectHasOrganisation::firstOrCreate([
                'project_id' => $project->id,
                'organisation_id' => $organisationId
            ]);
        }

        $custodianIds = ProjectHasCustodian::where('project_id', $project->id)
            ->pluck('custodian_id');

        foreach ($custodianIds as $custodianId) {
            CustodianHasProjectUser::firstOrCreate(
                [
                    'project_has_user_id' => $projectHasUser->id,
                    'custodian_id' => $custodianId,
                ]
            );

            // if ($affiliation) {
            //     $organisationId = $affiliation->organisation->id;

            //     $this->notifyUserChanged($projectHasUser, $organisationId, $custodianId);
            // }
        }

        UpdateProjectUserValidation::dispatch(
            $projectHasUser
        );

        if ((app()->bound('seeding') && app()->make('seeding') === true)) {
            return;
        }

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'project_id' => $project->id,
                'project_title' => $project->title,
                'affiliation_id' => $affiliation?->id,
                'affiliation_name' => $affiliation?->organisation->organisation_name,
                'role_id' => $role->id,
                'role_name' => $role->name,
            ])
            ->event('created')
            ->useLog('project_has_user')
            ->log('user_added_to_project');
    }

    /**
     * Handle the ProjectHasUser "updated" event.
     */
    public function updated(ProjectHasUser $projectHasUser): void
    {
        $changes = $projectHasUser->getChanges();
        if (empty($changes)) {
            return;
        }

        $original = $projectHasUser->getOriginal();
        $oldValues = array_intersect_key($original, $changes);

        $user = $projectHasUser->registry->user;
        $project = $projectHasUser->project;
        $projectId = $projectHasUser->project_id;
        $projectHasUserId = $projectHasUser->id;
        $affiliation = $projectHasUser->affiliation;
        $role = $projectHasUser->role;

        if (array_key_exists('project_role_id', $changes)) {
            $changes['role'] = $role->name;
            unset($changes['project_role_id']);
        }
        if (array_key_exists('affiliation_id', $changes)) {
            $changes['affiliation'] = $affiliation->organisation->organisation_name;
            unset($changes['affiliation_id']);
        }

        $custodianIds = ProjectHasCustodian::where('project_id', $projectId)
            ->select('custodian_id')
            ->pluck('custodian_id');

        $existing = CustodianHasProjectUser::where('project_has_user_id', $projectHasUserId)
            ->whereIn('custodian_id', $custodianIds)
            ->select('custodian_id')
            ->pluck('custodian_id')
            ->toArray();

        $insertData = [];

        foreach ($custodianIds as $custodianId) {
            if (!in_array($custodianId, $existing)) {
                $insertData[] = [
                    'project_has_user_id' => $projectHasUserId,
                    'custodian_id' => $custodianId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        if (!empty($insertData)) {
            CustodianHasProjectUser::insert($insertData);
        }


        if ($affiliation) {
            $organisationId = $affiliation->organisation->id;
            ProjectHasOrganisation::firstOrCreate([
                'project_id' => $project->id,
                'organisation_id' => $organisationId
            ]);
        }

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'project_id' => $project->id,
                'project_title' => $project->title,
                'affiliation_id' => $affiliation->id,
                'affiliation_name' => $affiliation->organisation->organisation_name,
                'role_id' => $role->id,
                'role_name' => $role->name,
                'attributes' => $changes,
                'old' => $oldValues,
            ])
            ->event('updated')
            ->useLog('project_has_user')
            ->log('user_updated_on_project');

        UpdateProjectUserValidation::dispatch(
            $projectHasUser
        );
    }

    /**
     * Handle the ProjectHasUser "deleted" event.
     */
    public function deleted(ProjectHasUser $projectHasUser): void
    {

        $user = $projectHasUser->registry->user;
        $project = $projectHasUser->project;
        $affiliation = $projectHasUser->affiliation;

        if ($affiliation) {
            $organisationId = $affiliation->organisation->id;

            $otherUsersWithSameAffiliation = ProjectHasUser::where('project_id', $project->id)
                ->whereHas('affiliation', function ($query) use ($organisationId) {
                    $query->where('organisation_id', $organisationId);
                })
                ->where('id', '!=', $projectHasUser->id)
                ->exists();

            if (!$otherUsersWithSameAffiliation) {
                ProjectHasOrganisation::where([
                    'project_id' => $project->id,
                    'organisation_id' => $organisationId
                ])->delete();
            }
        }


        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'project_id' => $project->id,
                'project_title' => $project->title,
            ])
            ->event('deleted')
            ->useLog('project_has_user')
            ->log('user_removed_from_project');


        // In this instance, we're intercepting the delete event of a user
        // being removed from a project. As such, when this happens, and
        // provided this project has custodian approval, we fire a
        // webhook to alert custodian's that they may be required
        // to remove data access for said user.
        $registry = Registry::where('digi_ident', $projectHasUser->user_digital_ident)->first();
        $user = User::where('registry_id', $registry->id)->first();


        // find all custodians on this project and notifying them of the user leaving
        $custodianIds = ProjectHasCustodian::where('project_id', $projectHasUser->project_id)
            ->pluck('custodian_id');

        foreach ($custodianIds as $custodianId) {
            $whr = CustodianWebhookReceiver::where([
                'custodian_id' => $custodianId,
            ])->first();

            if ($whr) {
                if (WebhookEventTrigger::where('name', 'user-left-project')->first()->id === $whr->webhook_event) {
                    WebhookCall::create()
                        ->url($whr->url)
                        ->payload([
                            'type' => 'user-left-project',
                            'user' => $user->registry->digi_ident,
                            'project' => Project::where('id', $projectHasUser->project_id)->first()->unique_id,
                        ])
                        // Uses has_hmac with sha256 to encode payload with custodian
                        // unique identifier, thus custodian's known that the payload
                        // hasn't been tampered with in transit.
                        ->useSecret(Custodian::where('id', $custodianId)->first()->unique_identifier)
                        ->dispatch();
                }
            }
        }
    }

    private function notifyUserChanged(ProjectHasUser $projectHasUser, int $organisationId, int $custodianId): void
    {
        $entities = $this->getEntityData($projectHasUser, $organisationId, $custodianId);

        $userNotification = new ProjectHasUserCreatedEntityUser(
            $entities['custodian'],
            $entities['project'],
            $entities['organisation'],
            $projectHasUser->affiliation,
            $entities['user']
        );

        Notification::send($entities['user'], $userNotification);

        foreach ($entities['organisationUsers'] as $user) {
            $organisationNotification = new ProjectHasUserCreatedEntityOrganisation(
                $entities['custodian'],
                $entities['project'],
                $projectHasUser->affiliation,
                $entities['user']
            );

            Notification::send($user, $organisationNotification);
        }

        foreach ($entities['custodianUsers'] as $user) {
            $custodianNotification = new ProjectHasUserCreatedEntityCustodian(
                $entities['custodian'],
                $entities['project'],
                $entities['organisation'],
                $projectHasUser->affiliation,
                $entities['user']
            );

            Notification::send($user, $custodianNotification);
        }
    }

    private function getEntityData(ProjectHasUser $projectHasUser, int $organisationId, int $custodianId)
    {
        $digiIdent = $projectHasUser->user_digital_ident;

        $user = User::whereHas('registry', function ($query) use ($digiIdent) {
            $query->where('digi_ident', $digiIdent);
        })->first();

        $custodian = Custodian::where([
            'id' => $custodianId,
        ])->first();

        $project = Project::where([
            'id' => $projectHasUser->project_id,
        ])->first();

        $organisationUsers = User::where([
            'organisation_id' => $organisationId,
        ])->get();

        $custodianUsers = User::whereNotNull('custodian_user_id')->with(['custodian_user' =>
            function ($query) use ($custodianId) {
                $query->where('custodian_id', $custodianId);
            }
        ])->get();

        $organisation = Organisation::where([
            'id' => $organisationId,
        ])->first();

        return ['user' => $user, 'custodian' => $custodian, 'organisation' => $organisation, 'organisationUsers' => $organisationUsers, 'custodianUsers' => $custodianUsers, 'project' => $project];
    }

    /**
     * Handle the ProjectHasUser "restored" event.
     */
    public function restored(ProjectHasUser $projectHasUser): void
    {
        //
    }

    /**
     * Handle the ProjectHasUser "force deleted" event.
     */
    public function forceDeleted(ProjectHasUser $projectHasUser): void
    {
        //
    }
}
