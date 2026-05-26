# API And Route Endpoints

Last reviewed: 2026-05-24

This document lists important SurfPan routes found in controllers, views, custom routing, JavaScript, and module API configs.

Trongate routes usually map to `/{module}/{method}/{param1}/{param2}`. Some links in views are hardcoded with `/surfpan/...`, which reflects the local/subdirectory deployment and should be made configurable.

## Custom Routes

Source: `config/custom_routing.php`.

| Route | Target | Purpose |
|---|---|---|
| `/login` | `users/login` | Public login page. |
| `/pass_reset` | `users/password_reset` | Password reset route alias. |
| `/tg-admin` | Admin login target | Admin login page. |
| `/tg-admin/submit_login` | Admin submit target | Admin login POST. |

Needs confirmation: exact custom-route target strings should be verified in `config/custom_routing.php` before changing route aliases.

## Authentication And User Routes

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/login` | `Users::login()` | Login form. | Public. |
| `/users/submit_login` | `Users::submit_login()` | Unified login for participants, organizers, judges. | Public POST. |
| `/users/logout` | `Users::logout()` | Destroys auth token/session. | Authenticated. |
| `/users/submit_create_account` | `Users::submit_create_account()` | Participant signup. | Public POST. |
| `/users/email_confirm?token=...` | `Users::email_confirm()` | Participant email confirmation. | Public token. |
| `/users/password_reset?token=...` | `Users::password_reset()` | Password reset form. | Public token. |
| `/users/submit_reset` | `Users::submit_reset()` | Password reset submit. | Public token/POST. |
| `/users/profile_info` | `Users::profile_info()` | Participant profile form/details. | Participant area. |
| `/users/submit_update_profile` | `Users::submit_update_profile()` | Update participant profile. | Participant area POST. |
| `/users/upload_avatar` | `Users::upload_avatar()` | Upload participant avatar. | Participant area POST. |
| `/users/athletes` | `Users::athletes()` | Public athlete listing. | Public. |
| `/users/profile/{slug}` | `Users::profile()` | Public athlete profile. | Public. |
| `/users/search?q=...` | `Users::search()` | AJAX user search. | Needs confirmation. |

## Participant Competition Routes

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/users/competition/{comp_id}` | `Users::competition()` | Participant-facing competition detail/registration context. | Participant area/public mix needs confirmation. |
| `/users/join/{comp_id}` | `Users::join()` | Join a competition/division. | Participant area POST/action. |
| `/users/withdraw/{participant_id}` | `Users::withdraw()` | Withdraw modal/page. | Participant area. |
| `/users/confirm_withdraw/{participant_id}` | `Users::confirm_withdraw()` | Confirms participant withdrawal. | Participant owner. |
| `/users/request_modal` | `Users::request_modal()` | Modal for an athlete/profile-related request. | Needs confirmation. |
| `/users/submit_request` | `Users::submit_request()` | Submit athlete/profile request. | Needs confirmation. |

## Organization Routes

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/organizations` | `Organizations::index()` | Organization area entry/signup/dashboard redirect. | Mixed. |
| `/organizations/submit_create` | `Organizations::submit_create()` | Organization signup. | Public POST. |
| `/organizations/confirmation_sent` | `Organizations::confirmation_sent()` | Signup confirmation message. | Public. |
| `/organizations/email_confirm?token=...` | `Organizations::email_confirm()` | Organizer email confirmation. | Public token. |
| `/organizations/show/{slug}` | `Organizations::show()` | Public organization profile. | Public. |
| `/organizations/dashboard` | `Organizations::dashboard()` | Organizer dashboard. | Organizer. |
| `/organizations/profile` | `Organizations::profile()` | Organizer profile/settings. | Organizer. |
| `/organizations/submit_update/{id}` | `Organizations::submit_update()` | Update organization. | Organizer owner POST. |
| `/organizations/upload_logo` | `Organizations::upload_logo()` | Upload organization logo. | Organizer POST. |
| `/organizations/change_pass` | `Organizations::change_pass()` | Change password form. | Organizer. |
| `/organizations/submit_change_pass` | `Organizations::submit_change_pass()` | Change password submit. | Organizer POST. |

## Competition Organizer Routes

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/competitions` | `Competitions::index()` | Organizer competition list/dashboard. | Organizer. |
| `/competitions/create_comp` | `Competitions::create_comp()` | Create competition form. | Organizer. |
| `/competitions/submit_create_comp` | `Competitions::submit_create_comp()` | Create competition submit. | Organizer POST. |
| `/competitions/edit_comp/{id}` | `Competitions::edit_comp()` | Edit competition form. | Organizer owner. |
| `/competitions/submit_delete_comp/{id}` | `Competitions::submit_delete_comp()` | Delete competition. | Organizer owner POST/action. |
| `/competitions/delete_modal/{id}` | `Competitions::delete_modal()` | Delete confirmation modal. | Organizer owner. |
| `/competitions/show_participants/{comp_id}/{division?}` | `Competitions::show_participants()` | Participant management by division. | Organizer owner. |
| `/competitions/create_participant/{comp_id?}` | `Competitions::create_participant()` | Manual participant create form. | Organizer. |
| `/competitions/submit_create_participant/{id?}` | `Competitions::submit_create_participant()` | Manual participant create/update. | Organizer POST. |
| `/competitions/confirm_participant/{participant_id}` | `Competitions::confirm_participant()` | Confirm participant entry. | Organizer owner needs confirmation. |

## Judge Management Routes

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/competitions/create_judge` | `Competitions::create_judge()` | Create judge form. | Organizer. |
| `/competitions/submit_create_judge` | `Competitions::submit_create_judge()` | Create judge submit. | Organizer POST. |
| `/competitions/edit_judge_modal/{judge_id}` | `Competitions::edit_judge_modal()` | Edit judge modal. | Organizer. |
| `/competitions/submit_edit_judge/{judge_id}` | `Competitions::submit_edit_judge()` | Update judge. | Organizer POST. |
| `/competitions/delete_judge_modal/{judge_id}` | `Competitions::delete_judge_modal()` | Delete judge modal. | Organizer. |
| `/competitions/submit_delete_judge/{judge_id}` | `Competitions::submit_delete_judge()` | Delete judge. | Organizer POST/action. |
| `/judges` | `Judges::index()` | Judge dashboard. | Judge/organizer depending guard. |
| `/judges/accept_invite/{link_id}` | `Judges::accept_invite()` | Accept judge invite. | Token/link. |
| `/judges/decline_invite/{link_id}` | `Judges::decline_invite()` | Decline judge invite. | Token/link. |
| `/judges/enter/{comp_id}` | `Judges::enter()` | Enter competition judging context. | Assigned judge. |
| `/judges/assign_judges/{comp_id}` | `Judges::assign_judges()` | Assign judge to competition. | Organizer owner. |
| `/judges/submit_assign/{comp_id}/{judge_id}` | `Judges::submit_assign()` | Save assignment. | Organizer owner POST/action. |
| `/judges/remove_modal/{comp_id}/{judge_id}` | `Judges::remove_modal()` | Remove judge modal. | Organizer owner. |
| `/judges/submit_remove_judge/{comp_id}/{judge_id}` | `Judges::submit_remove_judge()` | Remove judge assignment. | Organizer owner POST/action. |
| `/judges/leave_organization/{org_id}` | `Judges::leave_organization()` | Judge leaves organization. | Judge. |

## Judging And Score Routes

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/judges/score_heat` | `Judges::score_heat()` | Active scoring page for current heat. | Judge area. |
| `/judges/scores_modal/{heat_id}/{participant_id}` | `Judges::scores_modal()` | Score modal for participant's next/missing wave. | Judge area. |
| `/judges/submit_score` | `Judges::submit_score()` | Insert judge score and recalculate average. | Judge area POST. |
| `/competitions/all_scores/{heat_id?}` | `Competitions::all_scores()` | Head judge score review. | Head judge. |
| `/competitions/edit_scores/{heat_id?}` | `Competitions::edit_scores()` | Score edit table. | Head judge. |
| `/competitions/edit_score_modal/{score_id}` | `Competitions::edit_score_modal()` | Edit one score modal. | Head judge. |
| `/competitions/submit_edit_score/{score_id}` | `Competitions::submit_edit_score()` | Update one score and recalculate average. | Head judge POST. |
| `/competitions/update_score/{score_id}` | `Competitions::update_score()` | Alternate/legacy score update. | Needs confirmation. |
| `/competitions/heat_time` | `Competitions::heat_time()` | JSON-ish running heat timing endpoint. | Needs confirmation; currently global running heat lookup. |

## Heat Generation And Draw Routes

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/heats` | `Heats::index()` | Heat area entry. | Organizer/judge context needs confirmation. |
| `/heats/heat_generation_page` | `Heats::heat_generation_page()` | Choose elimination format/generate heats page. | Organizer. |
| `/heats/save_division_elimination/{comp_id}/{division_id}` | `Heats::save_division_elimination()` | Save elimination format for one division. | Organizer owner POST/action. |
| `/heats/generate_heats/{comp_id}` | `Heats::generate_heats()` | Billing gate and heat generation. | Organizer owner. |
| `/heats/delete_modal/{comp_id}` | `Heats::delete_modal()` | Delete generated heats modal. | Organizer owner. |
| `/heats/regenerate_modal/{comp_id}` | `Heats::regenerate_modal()` | Regenerate heats modal. | Organizer owner. |
| `/heats/delete_generation/{comp_id}` | `Heats::delete_generation()` | Wipe generated heats. | Organizer owner. |
| `/heats/regenerate/{comp_id}` | `Heats::regenerate()` | Wipe and regenerate. | Organizer owner. |
| `/heats/show_heats/{comp_id}/{division?}` | `Heats::show_heats()` | Organizer heat draw view. | Organizer/judge needs confirmation. |
| `/heats/show_heats_draw/{comp_id}/{division?}` | `Heats::show_heats_draw()` | Public heat draw. | Public. |
| `/heats/heat_scores/{heat_id}` | `Heats::heat_scores()` | Public/live heat scores. | Public. |

## Scheduling Routes

The schedule controller lives in nested module `modules/heats/schedules/controllers/Schedules.php`. Views reference routes with the `heats-schedules` prefix.

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/heats/heat_schedule_page` | `Heats::heat_schedule_page()` | Organizer schedule page. | Organizer. |
| `/heats/update_heat_schedule` | `Heats::update_heat_schedule()` | Update schedule. | Needs confirmation. |
| `/heats-schedules/clear_schedule/{comp_id}` | `Schedules::clear_schedule()` | Clear scheduled times. | Missing visible guard. |
| `/heats-schedules/reorder/{comp_id}` | `Schedules::reorder()` | AJAX drag/drop heat ordering. | Missing visible guard. |
| `/heats-schedules/auto_schedule_modal/{comp_id}` | `Schedules::auto_schedule_modal()` | Auto-schedule modal. | Missing visible guard. |
| `/heats-schedules/auto_schedule/{comp_id}` | `Schedules::auto_schedule()` | Generate schedule times. | Missing visible guard. |
| `/heats-schedules/set_time_modal/{comp_id}/{heat_id}` | `Schedules::set_time_modal()` | Set one heat time modal. | Missing visible guard. |
| `/heats-schedules/set_time/{comp_id}/{heat_id}` | `Schedules::set_time()` | Save one heat time. | Missing visible guard. |
| `/heats-schedules/reflow/{comp_id}/{heat_id}` | `Schedules::reflow()` | Reflow following heat times. | Missing visible guard. |

High-priority security issue: these schedule mutation endpoints should enforce organizer ownership before mutating any heat data.

## Results Routes

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/results/show/{comp_id}/{division?}` | `Results::show()` | Public final standings/results. | Public. |
| `/results/search?q=...` | `Results::search()` | Search results/organizations. | Public AJAX. |
| `/results/show_all` | `Results::show_all()` | Appears debug/demo with hardcoded competition id. | Needs confirmation before production exposure. |

## Billing Routes

| Route | Method/Controller | Purpose | Access |
|---|---|---|---|
| `/billings/pricing` | `Billings::pricing()` | Pricing page. | Public or organizer; needs confirmation. |
| `/billings/subscriptions` | `Billings::subscriptions()` | Subscription view. | Organizer. |
| `/billings/payment_modal` | `Billings::payment_modal()` | Payment modal. | Organizer. |
| `/billings/entry_pay_modal/{comp_id}` | `Billings::entry_pay_modal()` | Entry/generation payment modal. | Organizer. |
| `/billings/process_order/{comp_id}` | `Billings::process_order()` | Create EveryPay order/payment link. | Organizer POST/action. |
| `/billings/payment_result?payment_reference=...` | `Billings::payment_result()` | EveryPay return handler. | Public return URL with reference. |
| `/billings/payment` | `Billings::payment()` | Payment view/legacy route. | Needs confirmation. |

## Admin Routes

Admin routes are under `trongate_administrators/*`, with `/tg-admin` aliases for login.

| Route | Purpose |
|---|---|
| `/trongate_administrators/dashboard` | Admin dashboard. |
| `/trongate_administrators/organization_list` | Organization list. |
| `/trongate_administrators/org_detail/{org_id}` | Organization details. |
| `/trongate_administrators/toggle_org_status/{org_id}` | Enable/disable organization. |
| `/trongate_administrators/grant_free_event_pass/{org_id}` | Grant free event pass. |
| `/trongate_administrators/user_search` | Admin user search. |
| `/trongate_administrators/competitions_overview` | Competition overview. |
| `/trongate_administrators/billing_overview` | Billing overview. |
| `/trongate_administrators/impersonate/{org_id}` | Start org impersonation. |
| `/trongate_administrators/stop_impersonating` | Stop impersonation. |
| `/trongate_administrators/manage` | Admin user management. |
| `/trongate_administrators/create` | Create admin user. |
| `/trongate_administrators/logout` | Admin logout. |

Needs confirmation: admin route names may differ slightly depending on method names and Trongate route normalization.

## Upload Endpoints

| Route | Purpose | Storage |
|---|---|---|
| `/users/upload_avatar` | Participant avatar upload. | Needs confirmation from image upload settings. |
| `/organizations/upload_logo` | Organization logo upload. | Needs confirmation from image upload settings. |

Review required:

| Concern | Status |
|---|---|
| File type validation | Needs confirmation. |
| File size limits | Needs confirmation. |
| Storage path | Needs confirmation from controller helper config. |
| Public access rules | Needs confirmation. |

## JavaScript/AJAX Endpoints

| Caller | Endpoint | Behavior |
|---|---|---|
| `public/js/drag.js` | `/surfpan/heats-schedules/reorder/${compId}` | Sends drag/drop heat order. Hardcoded `/surfpan` base. |
| `users/views/dashboard.php` | `/surfpan/users/search?q=...` | User search fetch. Hardcoded `/surfpan` base. |
| Trongate MX attributes in views | Many modal/action endpoints | Build modals, submit forms, replace DOM fragments. |

## Generic API Config Files

Several modules include `assets/api.json`, which Trongate can use for generated REST-like endpoints.

Observed files:

| File | Notes |
|---|---|
| `modules/competitions/assets/api.json` | Generic `api/get/competitions`, `api/create/competitions`, update/delete routes. |
| `modules/heats/assets/api.json` | Generic heat API routes. One route appears to contain typo-like naming: `api/get/heats-heats`. |

Needs confirmation: whether generic Trongate API routes are enabled in production, and whether they are protected strongly enough for competition data.

## Endpoint Risk Summary

| Risk | Recommendation |
|---|---|
| Missing guards on schedule mutation routes | Add organizer ownership checks to every method in `Schedules.php`. |
| Hardcoded `/surfpan` JavaScript URLs | Inject base URL from config or use relative endpoints. |
| Legacy/debug endpoints | Remove or guard `restart_test_comp`, `show_all`, deprecated score routes. |
| Generic API configs | Audit whether generated APIs are enabled and restrict by role. |
| Mixed GET mutations | Convert destructive/mutating action routes to POST with CSRF checks. |
| Payment return handling | Make idempotent and verify EveryPay signature/status from API before unlocking generation. |
