# Authentication And Permissions

Last reviewed: 2026-05-24

This document describes login, tokens, user levels, route guards, ownership checks, and security issues found in the checked-in code.

No production user list or server session configuration was inspected. Anything that depends on live deployment configuration needs confirmation.

## Primary Source Files

| File | Responsibility |
|---|---|
| `modules/users/controllers/Users.php` | Participant signup/login, unified login, profile, password reset, participant access guard. |
| `modules/organizations/controllers/Organizations.php` | Organizer signup, profile, email confirmation, organizer access guard. |
| `modules/competitions/controllers/Competitions.php` | Judge account creation, legacy judge login, judge/organizer access guard. |
| `modules/judges/controllers/Judges.php` | Judge dashboard, invitations, assignment checks, scoring authorization. |
| `modules/trongate_security/controllers/Trongate_security.php` | Scenario-based guard dispatcher. |
| `modules/trongate_tokens/controllers/Trongate_tokens.php` | Token generation, cookie/session/header lookup, token validation. |
| `modules/trongate_administrators/controllers/Trongate_administrators.php` | Admin login, dashboard, impersonation. |
| `config/custom_routing.php` | Login/admin route aliases. |
| `config/config.php` | Cookie/session/runtime config and hardcoded integrations. |

## User Levels

Observed user levels:

| Level | Role | Backing Table(s) | Notes |
|---|---|---|---|
| `1` | Admin | `trongate_users`, `trongate_administrators` | Platform admin panel. |
| `3` | Judge/head judge | `trongate_users`, `comp_judges`, `comp_org_judges`, `competition_judges` | Head judge is a role string, not a separate user level. |
| `4` | Organizer | `trongate_users`, `comp_organizations`, `comp_org_accounts` | Owns competitions. |
| `5` | Participant | `trongate_users`, `comp_users`, `comp_users_profiles` | Athlete account. |

Needs confirmation: whether user level `2` is intentionally unused and whether there are legacy users with other levels.

## Login Entry Points

| Route | Controller | Purpose |
|---|---|---|
| `/login` | `Users::login()` | Main public login form. |
| `/users/submit_login` | `Users::submit_login()` | Unified login for participants, organizers, and judges. |
| `/tg-admin` | Admin route alias | Admin login. |
| `/tg-admin/submit_login` | Admin route alias | Admin login submit. |
| `/competitions/login` and related legacy methods | `Competitions.php` | Legacy judge/organizer login by username. Needs confirmation if still used. |

## Unified Login Flow

`Users::submit_login()` searches multiple account tables by email:

1. `comp_users`
2. `comp_organizations`
3. `comp_judges`

If a password matches, `_in_you_go()` generates a Trongate token and session state, then redirects based on role.

Observed redirect behavior:

| Account Type | Expected Redirect |
|---|---|
| Organizer | `/competitions` |
| Judge | `/judges` |
| Participant | `/users` |

Implementation warning: the unified login code appears to set some `comp_judges` users as `organizer` internally before checking role. If the `comp_judges` row does not have a `role` field in the expected shape, redirects may rely on undefined or inconsistent values. Needs confirmation from runtime behavior.

## Token Model

`Trongate_tokens` is the central auth token controller.

Token lookup sources:

| Source | Notes |
|---|---|
| HTTP Authorization header | `Bearer` token style supported by framework helper. |
| Session | Used by normal web login. |
| Cookie | Remember-me behavior. |

Important behavior:

| Method | Behavior |
|---|---|
| `_generate_token($user_id)` | Creates token row in `trongate_tokens`. |
| `_attempt_get_valid_token($user_levels)` | Returns token data only if token exists, is not expired, and user level is allowed. |
| `_destroy()` | Deletes token and clears auth state. |
| `_fetch_token_obj()` | Finds token by header/cookie/session. |

Token lifespan appears to default to 86,400 seconds for normal auth. Admin remember-me behavior uses a longer cookie lifetime.

Needs confirmation: production cookie flags (`Secure`, `HttpOnly`, `SameSite`) and HTTPS-only enforcement.

## Scenario-Based Guards

`Trongate_security::_make_sure_allowed($scenario)` dispatches to role-specific guard methods.

| Scenario | Guard Called | Allowed Levels Found |
|---|---|---|
| `participants area` | `Users::_make_sure_allowed()` | `[3, 4, 5]` |
| `judges area` | `Competitions::_make_sure_allowed()` | `[3, 4]` |
| `organizers area` | `Organizations::_make_sure_allowed()` | `[4]` |
| default/admin paths | Admin guard | Admin only. |

Important issue: `participants area` allows judges and organizers as well as participants. This may be intentional for preview/admin convenience, but the name does not match the access rule.

## Organizer Ownership Checks

Organizer-owned resources are usually tied through `comp_name.organizer_id`.

Observed ownership checks:

| Location | Behavior |
|---|---|
| `Heats::_check_organizer_permission($comp_id)` | Confirms the logged-in organizer owns the competition. Used by wipe/regenerate paths. |
| `Judges::_validate_organizer($org_id, $comp_id, $judge_id)` | Confirms judge belongs to organizer and competition belongs to organizer. |
| `Organizations::submit_update()` | Confirms the route org id matches the logged-in organization. |
| `Competitions::submit_delete_comp()` | Checks competition organizer id against logged-in organizer id. |

Implementation warning: `Competitions::submit_delete_comp()` uses strict comparison (`!==`) on values that may be string/int depending on model return type. That can block valid deletes or behave inconsistently.

## Judge Access Rules

Judge access depends on:

| Table | Meaning |
|---|---|
| `comp_judges` | Judge account. |
| `comp_org_judges` | Judge belongs to an organization and has org-level role/status. |
| `competition_judges` | Judge assigned to a specific competition, with role such as `judge` or `head_judge`. |

Important checks:

| Location | Behavior |
|---|---|
| `Judges::enter($comp_id)` | Lets a judge enter a competition context if invited/assigned. |
| `Judges::score_heat()` | Requires judges-area auth and asks `Competitions::_advance_and_get_heat_for_judge()` for current heat. |
| `Competitions::all_scores()` | Checks the logged-in judge has `competition_judges.role = 'head_judge'` for the heat competition. |
| `Competitions::submit_edit_score()` | Applies the same head-judge style access before editing score. |

Needs confirmation: whether organizers should be allowed to score as judges through the `[3,4]` judges-area guard, or only manage judging.

## Participant Access Rules

Participant routes generally use `Users::_make_sure_allowed()`.

Observed ownership checks:

| Location | Behavior |
|---|---|
| `Users::confirm_withdraw($participant_id)` | Confirms `comp_participants.user_id` matches current user. |
| `Users::dashboard` | Loads participant registrations for current user. |
| `Users::profile_info()` and update methods | Use current logged-in user id. |

Public participant pages such as athlete directories and profiles are intentionally accessible without login.

## Admin Access And Impersonation

Admin behavior lives in `modules/trongate_administrators/controllers/Trongate_administrators.php`.

Observed capabilities:

| Capability | Notes |
|---|---|
| Organization list/detail | Admin can inspect organization records. |
| User search | Admin can search users. |
| Competition overview | Admin can inspect event status and billing. |
| Billing overview | Admin can inspect charges/subscriptions/free passes. |
| Impersonation | Admin can impersonate organizations and stop impersonation. |
| Audit log | Impersonation writes to `admin_audit_log`. |

Needs confirmation: admin audit schema, retention policy, and whether all privileged admin changes are logged.

## Public Routes

The following route groups appear intentionally public:

| Route Group | Purpose |
|---|---|
| `/` and `/welcome/*` | Landing, contact, legal pages. |
| `/organizations/show/{slug}` | Public organization profile. |
| `/users/athletes`, `/users/profile/{slug}` | Public athlete directory/profile. |
| `/results/show/{comp_id}` and `/results/search` | Public results/search. |
| `/heats/show_heats_draw/{comp_id}` | Public heat draw. |
| `/heats/heat_scores/{heat_id}` | Public heat score view. |

Needs confirmation: whether live heat scores should be public during a running heat or only after completion.

## Security Findings

| Severity | Finding | Detail |
|---|---|---|
| Critical | Hardcoded secrets | `config/config.php` and `config/database.php` contain live-looking database/API credentials. Rotate and move to environment variables. |
| Critical | Schedule mutation endpoints lack visible guards | `modules/heats/schedules/controllers/Schedules.php` methods such as `clear_schedule`, `reorder`, `auto_schedule`, `set_time`, and `reflow` do not show access checks before mutating schedules. |
| High | Mixed raw SQL and interpolation | Many queries use `query_bind()`, but some route/user ids are interpolated directly after partial casting or helper lookup. Standardize bound parameters. |
| High | Broad participants-area guard | `participants area` allows levels `[3,4,5]`. The name implies participant-only. |
| High | Head-judge edits lack audit trail | Score edits can affect results but only update the score row/average. |
| High | Billing helper bugs can affect authorization | Event-pass and subscription gate logic has method-name/signature issues. |
| Medium | CSRF coverage unclear | Some mutations are triggered by Trongate MX buttons or fetch calls. Confirm CSRF behavior for all POST/mutation endpoints. |
| Medium | Confirmation/reset tokens stored plainly | Password reset/confirmation tokens appear stored directly in `comp_password_resets`. Hashing would reduce leak impact. |
| Medium | Public debug/test routes | Methods such as `restart_test_comp()` and `results/show_all` need confirmation before production exposure. |
| Medium | Hardcoded base path | JavaScript uses `/surfpan/...`, which can break deployments and leaks local path assumptions. |

## Recommended Permission Refactor

1. Create a central role/permission service with named constants for admin, organizer, judge, head judge, and participant.
2. Require every mutating controller method to call a guard at the top.
3. Add organizer ownership checks to all schedule endpoints.
4. Add judge assignment checks to all judging endpoints, not only general user-level checks.
5. Split participant-only access from "authenticated app user" access.
6. Add CSRF checks for all state-changing POST endpoints.
7. Add audit logs for score edits, admin grants, billing overrides, impersonation, schedule changes, and heat regeneration.
8. Move secrets to environment variables and rotate the exposed values.
9. Add feature tests for unauthorized access to high-risk routes.
