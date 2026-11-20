# Registration affiliation

A researcher (`user_group = USERS`) is **fully signed up** only once they have a current affiliation on which they've done everything they can. Until then, the web app sends them to an affiliation step instead of their profile. This applies to new and existing researchers. Organisation, custodian and admin users are always fully signed up.

This is an **onboarding step, not access control**. The API reports whether a researcher is signed up, and the web app redirects on it. The API doesn't refuse requests from researchers who aren't.

## The rule

`User::hasCurrentAffiliation()` (`app/Models/User.php`) is true when the user's registry has an affiliation that is:

1. **current**, as defined by `Affiliation::scopeCurrent()` in `app/Models/Affiliation.php`; and
2. **in one of `Affiliation::RESEARCHER_DONE_STATES`**, the constant defined in [`app/Models/Affiliation.php`](../../app/Models/Affiliation.php).

`Affiliation::RESEARCHER_DONE_STATES` in [`app/Models/Affiliation.php`](../../app/Models/Affiliation.php) is the single source of truth for which states count; check it there rather than relying on a list here.
- **The idea:** a state counts when the researcher has done everything they can and the next action is the organisation's.
- **Not counted:** states still waiting on the researcher (not filled in, or email not yet verified), and rejected or ended affiliations.
- **It's an allowlist,** so a new state doesn't count until it's added to the constant.

The data-provider test in `tests/Feature/RegistrationAffiliationTest.php` shows one example of each kind of outcome.

What follows from the rule:

- **Existing researchers** without a qualifying affiliation are sent to the step at their next login.
- **Researchers can be sent back** after signing up. That happens when their last qualifying affiliation ends (through the leaver flow or its end date passing) or is rejected. They then add their current employer.
- **Invited researchers** start with the affiliation the invite created, in `affiliation_info_required`. They complete that row; they don't create a second one.

## `/auth/me`

`GET /api/auth/me` returns the user's existing fields plus two more:

- **`registration_complete`** (bool): `hasCurrentAffiliation()` for researchers, always `true` for everyone else.
- **`registration_affiliation`**: the affiliation the step should act on, or `null`.

`registration_affiliation` comes from `User::registrationAffiliation()`, which chooses in this order:
1. the newest current affiliation in `affiliation_email_verify` (the web app shows a check-your-email view);
2. otherwise the oldest current affiliation in `affiliation_info_required` (the web app prefills the form from it).

It's a deliberately small summary, so nothing else about the affiliation is exposed:

```json
{
  "id": 42,
  "state": "affiliation_info_required",
  "organisation_id": 7,
  "organisation_name": "Example University",
  "email": "researcher@university.example.com"
}
```

- The fields are added in `AuthController@me`, not in `User::$appends`, so other endpoints that return users don't run the extra queries. Non-researchers skip the queries entirely.
- The data is read on the server from the caller's own registry. For invited researchers, the email is the address they were invited on. It's stored on the affiliation, so it survives `putEmailByInvite` replacing `users.email` with their login email.

## Endpoints the step uses

There are no new routes. The step reuses the existing affiliation routes, defined in the `// --- AFFILIATIONS ---` group of [`routes/api.php`](../../routes/api.php). They're handled by these methods in [`app/Http/Controllers/Api/V1/AffiliationController.php`](../../app/Http/Controllers/Api/V1/AffiliationController.php):

- **`storeByRegistryId`:** adds a new affiliation.
- **`update`:** completes an invited affiliation. It keeps an email that's already set, so the invited address can't be swapped.
- **`resendVerificationEmail`:** resends the verification email.
- **`verifyEmail`:** verifies the professional email. See below.

## Email verification

The verification link goes to the affiliation's professional email. The researcher clicks it while logged in with their own account, whatever address they log in with.

`verifyEmail` matches the code only against affiliations on the **caller's own registry**. The check is on who owns the affiliation, not on any email address:
- a different login email from the professional email is the normal case, and works;
- someone else's account using the code is refused, and the affiliation stays unverified.

A caller with no registry (an organisation, custodian or admin user) never matches.

When nothing matches, it returns **404**. That covers an unknown, expired, already used or someone else's code. The web app treats any failed response the same way, so it doesn't depend on the status code.

## Deploying

- **Deploy the API before the web app.** The web app treats a missing `registration_complete` as complete, so deploying in the other order doesn't lock anyone out. It just means the step doesn't appear until the API is live.
- **Before release, confirm "Verify email" is on in the Keycloak realm.** An affiliation whose email matches the login email skips verification and counts straight away. That's only safe if Keycloak has verified the login email.
- **Tell researchers before the release.** Existing researchers without a qualifying affiliation will see the step at their next login.

## Known follow-ups

- **Resend has no rate limit of its own.** Only the global API limit applies (`RATE_LIMIT`, 10,000 a minute by default). A per-route throttle on the resend route would stop repeated emails to one address.
- **The organisation on an invited affiliation isn't locked by the API.** The web app shows it read-only, but `AffiliationController::update` accepts a different `organisation_id` while the affiliation is still `affiliation_info_required`.
