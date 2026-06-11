-- P-6 follow-up: the soft-delete withdraw flow writes status = 'withdrawn',
-- but the enum never included that value, so the update silently failed
-- and registrations could not be withdrawn.
-- Run against the production database, then refresh database/schema.sql.

ALTER TABLE `comp_participants`
  MODIFY COLUMN `status` ENUM('confirmed','paid','pending','withdrawn') NOT NULL DEFAULT 'pending';
