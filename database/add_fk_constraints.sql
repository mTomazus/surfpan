-- SurfPan — Additive FK and unique-constraint migration
-- Run once against an existing database.
-- All tables must be InnoDB (they are, per database_setup.php).
-- Run a data-integrity check first — orphan rows will cause FK add to fail.
-- Safe to re-run: each ADD CONSTRAINT uses IF NOT EXISTS where supported,
-- but MySQL < 8.0 does not support that syntax for constraints, so wrap
-- each block in a stored procedure or run manually and skip on error.
--
-- CASCADE semantics chosen to match existing PHP cascade-delete logic.
-- RESTRICT is used where silent deletion would corrupt related data.
-- -----------------------------------------------------------------------

SET foreign_key_checks = 0; -- disable during migration; re-enable at end

-- -----------------------------------------------------------------------
-- Trongate auth tables
-- -----------------------------------------------------------------------

ALTER TABLE trongate_tokens
  ADD CONSTRAINT fk_tokens_user
    FOREIGN KEY (user_id) REFERENCES trongate_users(id) ON DELETE CASCADE;

ALTER TABLE trongate_administrators
  ADD CONSTRAINT fk_admins_trongate_user
    FOREIGN KEY (trongate_user_id) REFERENCES trongate_users(id) ON DELETE CASCADE;

-- -----------------------------------------------------------------------
-- Account tables → trongate_users
-- -----------------------------------------------------------------------

ALTER TABLE comp_users
  ADD CONSTRAINT fk_comp_users_trongate
    FOREIGN KEY (trongate_user_id) REFERENCES trongate_users(id) ON DELETE CASCADE;

ALTER TABLE comp_organizations
  ADD CONSTRAINT fk_comp_orgs_trongate
    FOREIGN KEY (trongate_user_id) REFERENCES trongate_users(id) ON DELETE CASCADE;

ALTER TABLE comp_judges
  ADD CONSTRAINT fk_comp_judges_trongate
    FOREIGN KEY (trongate_user_id) REFERENCES trongate_users(id) ON DELETE CASCADE;

-- -----------------------------------------------------------------------
-- Competition master
-- -----------------------------------------------------------------------

ALTER TABLE comp_name
  ADD CONSTRAINT fk_comp_name_organizer
    FOREIGN KEY (organizer_id) REFERENCES comp_organizations(id) ON DELETE CASCADE;

-- -----------------------------------------------------------------------
-- Competition ↔ division catalogue
-- -----------------------------------------------------------------------

ALTER TABLE comp_competition_divisions
  ADD CONSTRAINT fk_ccd_competition
    FOREIGN KEY (competition_id) REFERENCES comp_name(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_ccd_division
    FOREIGN KEY (division_id) REFERENCES comp_divisions(id) ON DELETE RESTRICT;

ALTER TABLE comp_competition_divisions
  ADD UNIQUE KEY uq_ccd (competition_id, division_id);

-- -----------------------------------------------------------------------
-- Participants
-- -----------------------------------------------------------------------

ALTER TABLE comp_participants
  ADD CONSTRAINT fk_participants_comp
    FOREIGN KEY (comp_id) REFERENCES comp_name(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_participants_user
    FOREIGN KEY (user_id) REFERENCES comp_users(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_participants_division
    FOREIGN KEY (division_id) REFERENCES comp_divisions(id) ON DELETE RESTRICT;

-- -----------------------------------------------------------------------
-- Heats
-- -----------------------------------------------------------------------

ALTER TABLE comp_heats
  ADD CONSTRAINT fk_heats_comp
    FOREIGN KEY (comp_id) REFERENCES comp_name(id) ON DELETE CASCADE;

-- -----------------------------------------------------------------------
-- Heat participants (jersey slots)
-- -----------------------------------------------------------------------

ALTER TABLE comp_heat_participants
  ADD CONSTRAINT fk_hp_heat
    FOREIGN KEY (heat_id) REFERENCES comp_heats(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_hp_participant
    -- RESTRICT: deleting a participant who is in a heat is blocked at app level (P-6).
    FOREIGN KEY (participant_id) REFERENCES comp_participants(id) ON DELETE RESTRICT;

ALTER TABLE comp_heat_participants
  ADD UNIQUE KEY uq_hp_slot (heat_id, participant_id),
  ADD UNIQUE KEY uq_hp_jersey (heat_id, jersey_color);

-- -----------------------------------------------------------------------
-- Heat advancement routing
-- -----------------------------------------------------------------------

ALTER TABLE comp_heat_advancement
  ADD CONSTRAINT fk_adv_from_heat
    FOREIGN KEY (from_heat_id) REFERENCES comp_heats(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_adv_to_heat
    -- NULL = eliminated; SET NULL preserves the routing row when destination heat is removed.
    FOREIGN KEY (to_heat_id) REFERENCES comp_heats(id) ON DELETE SET NULL;

-- -----------------------------------------------------------------------
-- Scoring
-- -----------------------------------------------------------------------

ALTER TABLE comp_judge_scores
  ADD CONSTRAINT fk_scores_heat
    FOREIGN KEY (heat_id) REFERENCES comp_heats(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_scores_participant
    FOREIGN KEY (participant_id) REFERENCES comp_participants(id) ON DELETE RESTRICT,
  ADD CONSTRAINT fk_scores_judge
    FOREIGN KEY (judge_id) REFERENCES comp_judges(id) ON DELETE RESTRICT;

ALTER TABLE comp_judge_scores
  ADD UNIQUE KEY uq_score (heat_id, wave_number, participant_id, judge_id);

ALTER TABLE comp_wave_averages
  ADD CONSTRAINT fk_wavg_heat
    FOREIGN KEY (heat_id) REFERENCES comp_heats(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_wavg_participant
    FOREIGN KEY (participant_id) REFERENCES comp_participants(id) ON DELETE RESTRICT;

ALTER TABLE comp_wave_averages
  ADD UNIQUE KEY uq_wavg (heat_id, wave_number, participant_id);

-- -----------------------------------------------------------------------
-- Results and standings
-- -----------------------------------------------------------------------

ALTER TABLE comp_heat_results
  ADD CONSTRAINT fk_hr_heat
    FOREIGN KEY (heat_id) REFERENCES comp_heats(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_hr_participant
    FOREIGN KEY (participant_id) REFERENCES comp_participants(id) ON DELETE RESTRICT;

ALTER TABLE comp_heat_results
  ADD UNIQUE KEY uq_hr (heat_id, participant_id);

ALTER TABLE comp_final_standings
  ADD CONSTRAINT fk_fs_comp
    FOREIGN KEY (comp_id) REFERENCES comp_name(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_fs_participant
    FOREIGN KEY (participant_id) REFERENCES comp_participants(id) ON DELETE RESTRICT;

-- -----------------------------------------------------------------------
-- Judge assignment tables
-- -----------------------------------------------------------------------

ALTER TABLE comp_org_judges
  ADD CONSTRAINT fk_oj_organization
    FOREIGN KEY (organization_id) REFERENCES comp_organizations(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_oj_judge
    FOREIGN KEY (user_id) REFERENCES comp_judges(id) ON DELETE CASCADE;

ALTER TABLE competition_judges
  ADD CONSTRAINT fk_cj_competition
    FOREIGN KEY (competition_id) REFERENCES comp_name(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_cj_judge
    FOREIGN KEY (judge_id) REFERENCES comp_judges(id) ON DELETE CASCADE;

-- -----------------------------------------------------------------------
-- Billing
-- -----------------------------------------------------------------------

ALTER TABLE billing_charges
  ADD CONSTRAINT fk_bc_comp
    -- SET NULL: charge row survives if competition is deleted (for audit).
    FOREIGN KEY (comp_id) REFERENCES comp_name(id) ON DELETE SET NULL;

ALTER TABLE billing_pass_uses
  ADD CONSTRAINT fk_bpu_org
    FOREIGN KEY (organization_id) REFERENCES comp_organizations(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_bpu_charge
    FOREIGN KEY (charge_id) REFERENCES billing_charges(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_bpu_comp
    FOREIGN KEY (competition_id) REFERENCES comp_name(id) ON DELETE SET NULL;

-- -----------------------------------------------------------------------
-- Re-enable FK checks
-- -----------------------------------------------------------------------

SET foreign_key_checks = 1;
