# Adding a write route

A write route is any non-GET route behind `auth:api`. Custodian users can hold the `CUSTODIAN_READ_ONLY` role (see [Custodian read-only access](../ARCHITECTURE/custodian-read-only.md)), so every new write route has to decide how read-only users are treated.

## Checklist

1. **Decide how read-only custodian users are treated.** A write route does one of these:
   - **Refuses read-only users.** This is the default for anything that changes data on behalf of a Custodian.
   - **Is unavailable to custodian users.** An existing check already refuses all of them, for example the `admin` gate or an organisation-only policy.
   - **Is deliberately allowed.** This is only for the user's own profile, notifications and checklist, or for a POST that only searches or reads.
2. **Add the check before anything is written.**
   - If the route has a policy, refuse read-only users in the policy ability:
     ```php
     if ($user->custodian_user?->isReadOnly() === true) {
         return false;
     }
     ```
   - If the route has no other authorisation check, use the `notReadOnly` gate as the first statement in the method:
     ```php
     if (! Gate::allows('notReadOnly')) {
         return $this->ForbiddenResponse();
     }
     ```
   - Never base the check on the `CustodianReadOnlyEnabled` feature flag. Read-only users are refused whatever the flag says.
3. **Classify the method in `tests/Feature/CustodianReadOnlyRouteCoverageTest.php`.**
   - If the controller is on `COVERED_CONTROLLERS`, add `ShortController@method` to `REFUSES_READ_ONLY`, `CUSTODIANS_CANNOT_USE` or `ALLOWED`. CI fails until you do.
   - **New controller with write routes:** once every write method in it fits one of those lists, add the controller to `COVERED_CONTROLLERS`. The test can't detect new controllers, so this step relies on you and the reviewer.
4. **Test the behaviour.**
   - For a route that refuses read-only users, send the same request twice. A read-only user gets a 403 and nothing is written. A `CUSTODIAN_APPROVER` in the same Custodian succeeds.
   - The approver half shows that the 403 comes from the read-only check, not from validation. `tests/Feature/CustodianReadOnlyGateTest.php` has a helper for this, `assertRefusedThenAllowed`.

## Signing in within tests

`TestCase` disables middleware, which changes how authentication behaves in tests:

- **`actingAsKeycloakUser()` on its own signs nobody in.** Chain `->actingAs($user)` as well. Without it the request runs anonymously, and permission checks refuse it, or pass by accident because of state left by earlier tests.
  ```php
  $this->actingAsKeycloakUser($user, $this->getMockedKeycloakPayload())
      ->actingAs($user)
      ->json('PUT', $url, $payload);
  ```
- **Endpoints that read the Keycloak token themselves**, such as `GET /api/auth/me`, need `Auth::forgetGuards()` after `actingAsKeycloakUser()`.
  - The `api` guard is built during `setUp()`, before the test sets its token, and it only reads the token when it's built.
  - Set the token's `sub` to the user's `keycloak_id`.
- Use `$this->enableObservers()` in tests that rely on model observers, such as the custodian role exclusivity check. `TestCase` disables observers by default.
