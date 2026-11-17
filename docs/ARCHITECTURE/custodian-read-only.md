# Custodian read-only access

Custodian users with the `CUSTODIAN_READ_ONLY` role can see everything their Custodian can see, but can't change data. This page explains how the role is stored, where it's enforced and how the feature flag affects it. To add a new write route, see [Adding a write route](../CONVENTIONS/write-routes.md).

## The role

- **Custodian roles** are rows in `custodian_user_has_permissions` that point to `permissions`. There are three: `CUSTODIAN_ADMIN`, `CUSTODIAN_APPROVER` and `CUSTODIAN_READ_ONLY`. They're listed in `App\Enums\CustodianRole`.
- **Checking a role:** `CustodianUser::hasPermission(CustodianRole $role)` and `CustodianUser::isReadOnly()`. `isReadOnly()` ignores the feature flag, deliberately.
- **Adding the permission:** the deployment step `2026_11_11_120000_add_custodian_read_only_permission` adds it to existing databases, and `PermissionSeeder` adds it to fresh ones.

### Roles are mutually exclusive

A custodian user holds at most one custodian role. `KEYCARD_*` permissions aren't roles and can sit alongside any role. There's no database constraint; exclusivity is enforced in code:

- `CustodianUserHasPermissionObserver` refuses to create a second role row and throws `CustodianRoleConflictException`, which renders as a 422.
- `POST /v1/custodian_users` and `PUT /v1/custodian_users/{id}` check the request first and return the same 422. `update` replaces the user's permission rows, so changing role works.
- `POST /v1/custodian_users/bulk` takes a single `permission` per user.

If a user somehow holds two roles, read-only wins: the policies check `isReadOnly()` first.

## What read-only users can and can't do

They **can**:

- use every GET route, within the existing view policies;
- update their own profile, email and password;
- mark their own notifications read or unread;
- tick their own checklist items;
- use POST endpoints that only search or read.

They **can't** change Custodian data. They can't create, edit, delete, approve or invite.

## Where it's enforced

There's no middleware and no global `Gate::before`. Each write is refused where its authorisation already lives:

1. **Policy abilities.** Where a write already has a policy, the ability refuses read-only users:
   - `CustodianPolicy::update` and `delete`. `CustodianPolicy::viewMembersOnly` is the read-only-friendly ability for views limited to the Custodian's own members.
   - `UserPolicy::update`, in the custodian branch that covers editing colleagues. Users can still edit themselves.
   - `UserPolicy::invite`.
   - `ActionLogPolicy::update`, for Custodian action logs only.
   - `CustodianUserPolicy` and `CustodianModelConfigPolicy` write abilities.
2. **The `write` gate** (`AuthServiceProvider`). Where a write has no other authorisation check, the controller method starts with `if (Gate::denies('write'))`. The gate refuses read-only users and allows everyone else, so it only enforces the read-only restriction and is not a general write permission.

`tests/Feature/CustodianReadOnlyRouteCoverageTest.php` lists every write method in the controllers custodian users work with, and how each one treats read-only users. CI fails when a write method is added to one of those controllers without being classified.

Server-to-server routes, authenticated by `x-client-id` (for example `POST /v1/custodian_users/bulk`), act as the Custodian rather than as a user, so the read-only role doesn't apply to them.

## The feature flag: `CustodianReadOnlyEnabled`

A global Pennant flag, **on by default**.

- **How the flag is added:** the migration `2026_11_12_130000_add_custodian_read_only_enabled_feature` adds it to existing databases, and `FeatureSeeder` adds it to fresh ones. A missing row reads as **off**.
- **How the front end uses it:** like the other flags, the front end reads it from `GET /v1/features`. It gets the user's role from `GET /v1/custodian_users/{id}`. While the flag is off, it shows read-only users a "contact support" notice. The API adds no flag-specific signal.
- **While the flag is off, nobody becomes read-only.** `store`, `update` and `bulkStore` return 422 `Read-only access is not enabled` for `CUSTODIAN_READ_ONLY`, and `invite` refuses read-only users. Users who are already read-only keep the role, so their other details can still be edited.
- **Writes stay blocked whatever the flag says.** Turning the flag off never gives read-only users more access.

## Invites

Inviting a read-only user sends the `custodian_invite_read_only` email. The template is chosen from the user's role, not their first permission row.

Changing an existing user's role sends no email, the same as any role change.

## Notifications

Read-only users are kept informed, but are never asked to do something they can't do.

| Event | Administrators and approvers | Read-only users |
|---|---|---|
| A user on one of the Custodian's projects leaves their organisation | `affiliation_ended_custodian` email and `AffiliationEnded` notification: revoke their data access, then confirm | `affiliation_ended_custodian_read_only` email and `AffiliationEnded` notification (`NotificationAudience::CustodianReadOnly`): for information; administrators and approvers have been asked to revoke |
| A revocation is still unconfirmed (weekly reminder) | `affiliation_access_revocation_reminder` email | Not sent |
| A user is added to one of the Custodian's projects through an affiliation | `project_user_added_custodian` email and in-app notification | The same |

`AffiliationLeaverService::getCustodianUsers()` separates the two groups. The new-joiner email is sent from `ProjectHasUserObserver`, once for each project user added.

The new-joiner email and the read-only leaving email only repeat an in-app notification, so they're sent on a best-effort basis. If one can't be sent (for example, its template is missing), the failure is logged as a warning and the user is still added to the project or still leaves. The email to administrators and approvers, which asks them to act, isn't wrapped this way.

The deployment step `2026_11_12_120000_reseed_email_templates_custodian_notifications` adds the new email templates to existing databases. It doesn't catch seeding errors, so a failure halts the deploy and the step runs again next time.

## Tests

The tests are the source of truth for what's covered. Start with `tests/Feature/CustodianReadOnly*Test.php`
and `CustodianRoleExclusivityTest`; other tests that use a read-only user live alongside the feature they cover
(`grep -rl "CustodianRole::ReadOnly" tests` finds them).

What they're there to guarantee:

- **Read-only users can't write**, through policies or the `write` gate, and an approver can still do the same thing.
- **Read-only users keep what they're allowed**: reading, their own profile, notifications and checklist items.
- **Every write method in a covered controller is classified.** `CustodianReadOnlyRouteCoverageTest` fails CI when one
  is added without saying how read-only users are treated, so this one keeps itself current.
- **Roles stay exclusive**, and read-only wins if bad data gives someone two roles.
- **The feature flag never loosens access**: turning it off stops anyone becoming read-only, but never lets read-only users write.
- **Read-only users are told, never asked to act**, and an information-only email failing never blocks the action.
