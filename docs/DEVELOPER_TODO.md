# Developer TODO And Refactor Plan

Last reviewed: 2026-05-24

This is the practical improvement backlog for SurfPan based on code inspection. It is prioritized by risk to competition integrity, security, and maintainability.

## Priority 0: Production Safety

| Task | Why It Matters | Suggested Scope |
|---|---|---|
| Rotate and remove hardcoded secrets | `config/config.php` and `config/database.php` contain live-looking credentials. Exposure risk is immediate. | Move DB/API/payment/email credentials to environment variables or deployment secrets, rotate existing values, and commit only example config. |
| Guard schedule mutation endpoints | `Schedules.php` can clear/reorder/auto-schedule/set times without visible auth/ownership checks. | Add organizer-area guard plus `comp_name.organizer_id` ownership check to every mutating schedule method. |
| Add database schema/migrations | No SurfPan domain schema dump or migrations are checked in. New developers cannot reliably install locally. | Create versioned migrations or an initial schema SQL with indexes and foreign keys. |
| Audit payment unlock flow | Heat generation can depend on billing status/event pass credits. Helper bugs and idempotency gaps can block or incorrectly allow generation. | Fix billing helper method names/signatures, make EveryPay result handling idempotent, and test paid/free/event-pass paths. |

## Priority 1: Competition Integrity

| Task | Why It Matters | Suggested Scope |
|---|---|---|
| Extract heat generation into a testable service | Current bracket logic is large, nested, and hard to verify. | Move bracket-plan creation out of controller; write unit tests for 2-36 participants and all implemented formats. |
| Fix regeneration cleanup | `_wipe_generation()` leaves `comp_judge_scores` and `comp_wave_averages` behind. | Delete or archive scores/averages when heats are wiped; make cleanup transactional. |
| Define and implement tie-break rules | Heat ranking and final standings have no explicit tie handling. | Confirm official rule order, implement deterministic sorting, show tie-break basis in results. |
| Fix wave numbering model | Wave numbers are per judge, which can misalign after missed submissions. | Add a global wave table/model or central wave creation endpoint before judge scores are attached. |
| Clamp adjusted scores after adjustment | Current score adjustment can create values below 0 or above 10. | Apply `max(0, min(10, round(score + adjustment, 2)))` and add tests. |
| Add score edit audit log | Head-judge edits affect competition outcomes but are not independently audited. | Store old/new score, editor id, reason, timestamp, and affected average/result recalculation state. |

## Priority 2: Auth And Authorization Cleanup

| Task | Why It Matters | Suggested Scope |
|---|---|---|
| Centralize role constants | User levels are scattered magic numbers. | Define admin/organizer/judge/participant constants or enum-like class. |
| Split participant-only from authenticated-user guard | `participants area` currently allows judges and organizers. | Rename broad guard or add a true participant-only scenario. |
| Enforce assignment checks on scoring | User level alone should not permit scoring any heat. | Check `competition_judges` assignment for the heat competition on every scoring endpoint. |
| Convert GET mutations to POST | Several action routes mutate state from URL-style calls. | Require POST plus CSRF token for deletes, confirms, schedule changes, judge assignment, and heat generation actions. |
| Review generated API endpoints | Module `assets/api.json` files may expose generic CRUD routes. | Confirm whether enabled; remove unused generated APIs or lock them down. |

## Priority 3: Scheduling And Time

| Task | Why It Matters | Suggested Scope |
|---|---|---|
| Standardize UTC storage | Scheduling has UTC conversion helpers, but older code also uses competition/organization timezone in mixed ways. | Store all timestamps in UTC; convert only at input/output boundaries. |
| Document heat status transitions | Status values include `pending`, `scheduled`, `running`, `finished`, and competition statuses include more variants. | Create status constants and a transition map. |
| Fix `Schedules::auto_schedule()` undefined variables | Code references `$p['breaks']` while the params array appears commented/undefined. | Restore params parsing or remove break support until implemented. |
| Extend schedule round rank map | Reorder logic may reject valid generated round labels such as `Repechage`, `Quarter Final`, or `Semi Final`. | Derive ordering from `comp_heats.order_index`/round number instead of hardcoded labels. |

## Priority 4: Developer Experience

| Task | Why It Matters | Suggested Scope |
|---|---|---|
| Add `.env.example` and install docs | New developers need local config without seeing live secrets. | Document XAMPP/PHP/MySQL setup, base URL, DB import, and test accounts. |
| Add seed data | The repo has starter Trongate tables only, not SurfPan competition fixtures. | Add local seed competition, organization, judges, participants, generated heats, and sample scores. |
| Remove backup files from active repo | `*.bak.php`, `.bak`, and `.DS_Store` files add noise. | Move historical backups out of the app tree and update `.gitignore`. |
| Add automated checks | No Composer/test setup was found. | Add PHP syntax check script, smoke tests, and service-level unit tests once logic is extracted. |
| Normalize route/base URL generation | Hardcoded `/surfpan` URLs break other deployments. | Use Trongate base URL helpers or inject `BASE_URL` into JavaScript. |

## Specific Bugs To Verify

| Area | File/Method | Issue |
|---|---|---|
| Billing | `Billings::_plan_limit()` | Calls `normalize_key()` but the method is named `_normalize_key()`. |
| Billing | `Billings::_plan_limit()` | Calls `_infer_limit_from_synonyms()` but observed helper appears named without underscore. |
| Billing | `Billings::infer_limit_from_synonyms()` | Calls `array_get_dot()` while helper appears named `_array_get_dot()`. |
| Billing | `Billings::_consume_event_pass_credit()` | Calls `$this->model->insert('billing_pass_uses', [...])`, which appears to reverse the expected Trongate insert argument order. |
| Billing | `Billings::_get_event_pass_credits()` | Looks for `product_id = 'event_pass'`, while other code uses numeric/product-code forms. |
| Scheduling | `Schedules::auto_schedule()` | References `$p['breaks']` while `$p` appears undefined/commented. |
| Scheduling | `Schedules::reflow()` | Similar `$p['breaks']` issue. |
| Scheduling | `Schedules::reorder()` | Hardcoded round rank map does not cover every generated round label. |
| Competition delete | `Competitions::submit_delete_comp()` | Strict comparison may fail when ids are string vs int. |
| Heat wipe | `Heats::_remove_all_heat_data()` | References undefined `$comp` and overrides input parameter from URL segment. |
| Heat wipe | `Heats::_wipe_generation()` | Does not delete judge scores or wave averages. |
| Single elimination | `Heats::_generate_single_elimination_bracket()` | Final appears to insert only one no-target route for position 1. |
| Scoring | `Judges::submit_score()` | Adjustment can push score outside `0..10`. |
| Legacy timing | `Competitions::heat_time()` | Finds any running heat globally, not scoped by competition/judge. |
| Participant create | `Competitions::submit_create_participant()` | Validates `division_id` but may not persist it in manual-create data. |
| Dashboard | `users/views/dashboard.php` | Assumes a first heat exists in `$user_heats[0]`. |
| Email confirmation | `Users::email_confirm()` / `Organizations::email_confirm()` | Deletes reset rows by id using a user id value. Needs confirmation. |
| Views | Welcome contact modal views | Attribute `mx-build_modal` appears typo-like compared with `mx-build-modal`. |

## Refactor Roadmap

### Phase 1: Stabilize

1. Move secrets to environment variables and rotate exposed values.
2. Add guards to schedule mutation routes.
3. Fix fatal billing helper bugs.
4. Add an initial database schema export/migration.
5. Add smoke-test checklist for login, create competition, register participant, generate heats, score heat, publish results.

### Phase 2: Protect Competition Results

1. Extract scoring calculations into a service with tests.
2. Extract heat generation into a service with tests.
3. Implement deterministic tie-breaks.
4. Add score edit audit log.
5. Make heat regeneration transactional and complete.

### Phase 3: Normalize Platform Architecture

1. Centralize permissions and route guards.
2. Normalize time handling to UTC storage and org-local display.
3. Remove legacy/deprecated scoring routes.
4. Replace hardcoded base paths with config helpers.
5. Convert GET mutations to POST plus CSRF.

### Phase 4: Product Completeness

1. Decide whether round robin should be implemented or removed.
2. Add bracket preview before writing to DB.
3. Add seeded draw support if SurfPan needs ranking-based seeding.
4. Add better live-score polling/websocket strategy if live updates are required.
5. Improve organizer audit history for schedule changes, judge assignment, and generation.

## Suggested Test Coverage

| Test Type | Cases |
|---|---|
| Heat generation unit tests | Participant counts 2-36 for single, double, second chance. |
| Advancement tests | Each finishing position routes to expected target heat or elimination. |
| Scoring tests | Numeric scores, missed waves, partial judges, all missed, best two waves, ties. |
| Auth tests | Unauthorized schedule mutation, judge scoring unassigned heat, participant withdraw ownership. |
| Billing tests | Free tier, paid tier, event pass credit, repeated EveryPay callback. |
| Time tests | Local input converts to UTC, DST boundary, schedule reflow. |
| Regression tests | Regeneration removes all dependent heat data or archives it intentionally. |

## Open Questions

| Question | Why It Matters |
|---|---|
| Are heats limited to four surfers? | Jersey color and final route logic assumes four colors/places. |
| Is random seeding acceptable? | Current generation shuffles participants. |
| What is the official tie-break rule? | Rankings and advancement need deterministic outcomes. |
| Should live scores be public while heats run? | Affects permissions and UI behavior. |
| Should organizers be able to act as judges? | Current guards allow organizer level in judge area. |
| Should round robin be supported? | UI and helper references exist, but generator is missing. |
| What are the canonical competition and heat statuses? | Code references more statuses than the main lifecycle uses. |
| What PHP/MySQL versions are production? | README/config do not state exact required versions. |

## Definition Of Done For A New Change

A change to heat generation, scoring, billing, auth, or scheduling should be considered done only when:

1. The relevant controller method has an explicit guard.
2. Organizer/judge/participant ownership is checked where applicable.
3. Database writes are parameterized and transactional where multiple tables must stay consistent.
4. Existing generated heats/scores are not orphaned.
5. UTC/local timezone behavior is documented for any time fields touched.
6. At least one focused regression test or reproducible manual test checklist is added.
7. Public docs in `docs/` are updated if behavior changes.
