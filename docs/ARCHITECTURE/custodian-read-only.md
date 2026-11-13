# Custodian read-only access

Custodian users with the `CUSTODIAN_READ_ONLY` role can see everything their Custodian can see, but can't change data. This page explains how the role is stored, where it's enforced and how the feature flag affects it. To add a new write route, see [Adding a write route](../CONVENTIONS/write-routes.md).

## The role

- **Custodian roles** are rows in `custodian_user_has_permissions` that point to `permissions`. There are three: `CUSTODIAN_ADMIN`, `CUSTODIAN_APPROVER` and `CUSTODIAN_READ_ONLY`. They're listed in `App\Enums\CustodianRole`.
- **Checking a role:** `CustodianUser::hasPermission($name)` and `CustodianUser::isReadOnly()`. `isReadOnly()` ignores the feature flag, deliberately.
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
2. **The `notReadOnly` gate** (`AuthServiceProvider`). Where a write has no other authorisation check, the controller method starts with `Gate::allows('notReadOnly')`. The gate refuses read-only users and allows everyone else.

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

The deployment step `2026_11_12_120000_reseed_email_templates_custodian_read_only` adds the template to existing databases.

Changing an existing user's role sends no email, the same as any role change.

## Tests

| Test | Covers |
|---|---|
| `CustodianReadOnlyTest` | Policy refusals, what read-only users can still do, the invite email, bulk store |
| `CustodianReadOnlyGateTest` | The `notReadOnly` gate, one route per controller: a read-only user is refused and an approver succeeds |
| `CustodianRoleExclusivityTest` | The 422s, the observer, `KEYCARD_*` unaffected, read-only wins with bad data |
| `CustodianReadOnlyFeatureFlagTest` | The flag's seeder entry, migration and rollback; writes blocked while off; nobody becomes read-only while off |
| `CustodianReadOnlyRouteCoverageTest` | Every write method in covered controllers is classified |
