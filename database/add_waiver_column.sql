-- P-15: Add waiver acceptance tracking to comp_participants
ALTER TABLE comp_participants
  ADD COLUMN waiver_accepted_at DATETIME NULL DEFAULT NULL;
