<?php
    class Judges extends Trongate {

        // GET /judges/dashboard
        public function index() {
            // Access control
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            // Current user → judge
            $this->module('trongate_tokens');
            $tg_user_id = $this->trongate_tokens->_get_user_id();

            // Fetch judge row (adjust table/cols if your schema differs)
            $sql = "SELECT id, name, email, phone, username, trongate_user_id
                    FROM comp_judges
                    WHERE trongate_user_id = :tg_user_id
                    LIMIT 1";
            $judge = $this->model->query_bind($sql, ['tg_user_id' => $tg_user_id], 'object');

            if (!$judge) {
                // Optional: auto-create a minimal judge profile or show error
                $data['error'] = 'Judge profile was not found for your account.';
                redirect('users');
            }

            $judge_id = $judge[0]->id;

            // Organizations the judge belongs to
            $sql = "SELECT 
                    coj.id              AS link_id,
                    co.id               AS org_id,
                    co.organization     AS organization,
                    coj.role            AS org_role,
                    coj.status          AS org_status,
                    coj.created_at
                    FROM comp_org_judges coj
                    JOIN comp_organizations co ON co.id = coj.organization_id
                    WHERE coj.user_id = :judge_id
                    ORDER BY coj.created_at DESC";
            $orgs = $this->model->query_bind($sql, ['judge_id' => $judge_id], 'array');

            // Competition links (assigned or invited)
            // NOTE: Table name/cols assume: ompetition_judges(judge_id, competition_id, role, status)
            // Competitions stored in comp_name (id, name, start_date, end_date, location, status, year)
            $sql = "SELECT 
                    ccj.id                     AS link_id,
                    c.id                       AS comp_id,
                    c.name,
                    c.year,
                    c.location,
                    c.status                   AS comp_status,
                    c.start_date,
                    c.end_date,
                    ccj.role                   AS judge_role,
                    ccj.status                 AS judge_status
                    FROM competition_judges ccj
                    JOIN comp_name c ON c.id = ccj.competition_id
                    WHERE ccj.judge_id = :judge_id
                    ORDER BY 
                    FIELD(c.status, 'running','open','generated','closed','scheduled','finished') DESC,
                    c.start_date DESC";
            $comps = $this->model->query_bind($sql, ['judge_id' => $judge_id], 'array');

            // Split by status for UI
            $pending_invites = array_values(array_filter($comps, fn($r) => ($r['judge_status'] ?? '') === 'pending invite'));
            $active_assigned = array_values(array_filter($comps, fn($r) => in_array(($r['judge_status'] ?? ''), ['active','accepted'])));

            $data = [
            'judge'           => $judge[0],
            'orgs'            => $orgs,
            'pending_invites' => $pending_invites,
            'active_assigned' => $active_assigned
            ];

            $data['view_file'] = 'dashboard';
            $this->template('judges_area', $data);

        }

        // POST /judges/accept_invite/{link_id}
        public function accept_invite() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $link_id = (int)segment(3);

            $this->module("competitions");
            $judge_id = $this->competitions->_get_judge_id(); // custom function to ensure judge is active
            $link_record = $this->model->get_one_where('id', $link_id, 'competition_judges');

            $link_judge_id = $link_record->judge_id ?? null;

            if ((int)$link_judge_id !== (int)$judge_id) {
                // Optional: log unauthorized attempt
                redirect('judges');
            }

            // Minimal action; add your own CSRF checks, ownership checks, logging
            $sql = "UPDATE competition_judges SET status = 'accepted' WHERE id = ?";
            $this->model->query_bind($sql, [$link_id]);

            redirect('judges');
        }

        // POST /judges/decline_invite/{link_id}
        public function decline_invite() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $link_id = (int)segment(3);

            $this->module("competitions");
            $judge_id = $this->competitions->_get_judge_id(); // custom function to ensure judge is active
            $link_record = $this->model->get_one_where('id', $link_id, 'competition_judges');

            $link_judge_id = $link_record->judge_id ?? null;

            if ((int)$link_judge_id !== (int)$judge_id) {
                // Optional: log unauthorized attempt
                redirect('judges');
            }

            $sql = "UPDATE competition_judges SET status = 'declined' WHERE id = ?";
            $this->model->query_bind($sql, [$link_id]);

            redirect('judges');
        }

        // GET /judges/enter/{comp_id}
        public function enter() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            // Ensure judge has access to this competition
            $comp_id = (int)segment(3);

            $this->module("competitions");
            $judge_id = $this->competitions->_get_judge_id(); // custom function to ensure judge is active
            $sql = "SELECT COUNT(*) AS count
                    FROM competition_judges
                    WHERE competition_id = :comp_id
                    AND judge_id = :judge_id
                    AND status IN ('active','accepted');";
            $res = $this->model->query_bind($sql, ['comp_id' => $comp_id, 'judge_id' => $judge_id], 'object');

            if ($res[0]->count == 0) {
                // Unauthorized attempt failed
                redirect('judges');
            }

            // Then redirect to your judging UI route
            redirect('competitions'); // adapt to your judging module route
        }

        public function organizer() {
            // Access control
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            //$data['view_file'] = 'organizer';$this->template('users_area', $data);
            $this->view('organizer2');
        }

        function assign_judges() {
            // Access control
            $this->module('trongate_security');
            $this->module('competitions');

            $this->trongate_security->_make_sure_allowed('judges area');

            $org_id = $this->competitions->_get_organizer_user_id();
            $comp_id = (int)segment(3);

            $data['comp_judges'] = $this->_get_comp_judges($comp_id);
            // organization judges not already assigned to this competition
            $data['org_judges'] = $this->_get_org_judges($org_id, $comp_id);
            $data['comp_id'] = $comp_id;
            $data['org_id'] = $org_id;

            if (from_trongate_mx()) {
                $this->view('assign_judges', $data);
            } else {
                $data['view_file'] = 'assign_judges'; 
                $this->template('judges_area', $data);
            }
        }

        function submit_assign() {
            // Access control
            $this->module('trongate_security');
            $this->module('competitions');
            $this->trongate_security->_make_sure_allowed('judges area');

            $organizer_id = $this->competitions->_get_organizer_user_id();
            $competition_id = (int)segment(3);
            $judge_id = (int)segment(4);

            // Verify judge belongs to organizer and competition belongs to organizer

            $is_valid = $this->_validate_organizer($organizer_id, $competition_id, $judge_id);

            if (!$is_valid) {
                // Unauthorized attempt
                redirect('judges/assign_judges');
            }
            
            $sql = "SELECT role 
                    FROM comp_org_judges 
                    WHERE user_id = :judge_id 
                    AND organization_id = :organizer_id 
                    LIMIT 1;";
            $judge_obj = $this->model->query_bind($sql, ['judge_id' => $judge_id, 'organizer_id' => $organizer_id], 'object');
            $role = $judge_obj[0]->role;

            // Check if judge is already assigned to this competition
            $sql = "SELECT COUNT(*) AS count
                    FROM competition_judges
                    WHERE judge_id = :judge_id
                    AND competition_id = :competition_id;";
            $judge_count = $this->model->query_bind($sql, ['judge_id' => $judge_id, 'competition_id' => $competition_id], 'object');
            // If already assigned, do nothing (or optionally update role/status)
            // If not assigned, insert new record

            if ($judge_count[0]->count > 0) {
                // Judge already assigned
                echo '<p>Judge is already assigned to this competition.</p>';
                return;
            }
            // Assign judge to competition
            $data = [
                'judge_id' => $judge_id,
                'competition_id' => $competition_id,
                'role' => $role,
                'status' => 'pending invite'
            ];

            $this->model->insert($data, 'competition_judges');
            echo '<p style="padding:0.5rem;background: mediumseagreen;color: white;">Successfully assigned judge to competition.</p>';
            // Optionally: send email notification to judge
        }

        function remove_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $comp_id = (int)segment(3);
            $judge_id = (int)segment(4);

            $data['comp_id'] = $comp_id;
            $data['judge_id'] = $judge_id;

            $this->view('remove_judge_modal', $data);
        }

        function submit_remove_judge() {
            $this->module('trongate_security');
            $this->module('competitions');
            $this->trongate_security->_make_sure_allowed('judges area');
            $this->validation->run();

            $organizer_id = $this->competitions->_get_organizer_user_id();
            $competition_id = (int)segment(3);
            $judge_id = (int)segment(4);

            $sql = "SELECT COUNT(*) AS count
                    FROM competition_judges
                    WHERE judge_id = :judge_id
                    AND competition_id = :competition_id;";
            $judge_count = $this->model->query_bind($sql, ['judge_id' => $judge_id, 'competition_id' => $competition_id], 'object');
            if ($judge_count[0]->count == 0) {
                echo '<p>Judge not assigned to this competition</p>';
                redirect('judges/assign_judges');
            }

            // Verify judge belongs to organizer and competition belongs to organizer
            $is_valid = $this->_validate_organizer($organizer_id, $competition_id, $judge_id);
            if (!$is_valid) {
                // Unauthorized attempt
                redirect('judges/assign_judges');
            }

            $sql = "DELETE FROM competition_judges
                    WHERE judge_id = :judge_id
                    AND competition_id = :competition_id;";
            $this->model->query_bind($sql, ['judge_id' => $judge_id, 'competition_id' => $competition_id]);
            echo '<p style="padding:0.5rem;background: indianred;color: white;;">Successfully removed judge from competition.</p>';
        }

        public function _validate_organizer($org_id, $comp_id, $judge_id) {

            $sql = "SELECT COUNT(*) AS count
                    FROM comp_org_judges
                    WHERE user_id = :judge_id
                    AND organization_id = :organizer_id;";
            $judge_count = $this->model->query_bind($sql, ['judge_id' => $judge_id, 'organizer_id' => $org_id], 'object');

            $sql2 = "SELECT COUNT(*) AS count
                    FROM comp_name
                    WHERE id = :competition_id
                    AND organizer_id = :organizer_id;";
            $comp_count = $this->model->query_bind($sql2, ['competition_id' => $comp_id, 'organizer_id' => $org_id], 'object');
            
            if ($judge_count[0]->count == 0 || $comp_count[0]->count == 0) {
                return false;
            }
            return true;
        }

        public function _get_org_judges($org_id, $comp_id) {
            $sql = "SELECT cj.id, cj.name, cj.email, coj.status, coj.role, coj.organization_id
                    FROM comp_judges cj
                    JOIN comp_org_judges coj ON coj.user_id = cj.id
                    WHERE coj.organization_id = :org_id
                    AND NOT EXISTS (
                                    SELECT 1
                                    FROM competition_judges ccj
                                    WHERE ccj.judge_id = cj.id
                                    AND ccj.competition_id = :comp_id
                                    )
                    ORDER BY cj.name ASC;";
            $rows = $this->model->query_bind($sql, ['org_id' => $org_id, 'comp_id' => $comp_id], 'object');
            return $rows;
        }

        public function _get_comp_judges($comp_id) {
            $sql = "SELECT 
                    cj.id,
                    cj.name,
                    cj.email,
                    ccj.status AS assignment_status,
                    ccj.role AS assignment_role,
                    ccj.competition_id
                    FROM comp_judges cj
                    JOIN competition_judges ccj ON ccj.judge_id = cj.id
                    JOIN comp_org_judges coj ON coj.user_id = cj.id
                    JOIN comp_name cn ON ccj.competition_id = cn.id
                    WHERE ccj.competition_id = :comp_id
                    AND coj.organization_id = cn.organizer_id
                    ORDER BY cj.name ASC;";
            $rows = $this->model->query_bind($sql, ['comp_id' => $comp_id], 'object');
            return $rows;
        }

        public function leave_organization() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $org_id = (int)segment(3);

            $this->module("competitions");
            $judge_id = $this->competitions->_get_judge_id(); // custom function to ensure judge is active

            // Verify judge belongs to organization
            $sql = "SELECT COUNT(*) AS count
                    FROM comp_org_judges
                    WHERE user_id = :judge_id
                    AND organization_id = :org_id;";
            $res = $this->model->query_bind($sql, ['judge_id' => $judge_id, 'org_id' => $org_id], 'object');

            if ($res[0]->count == 0) {
                // Unauthorized attempt
                redirect('judges');
            }

            // Remove judge from organization
            $sql = "DELETE FROM comp_org_judges
                    WHERE user_id = :judge_id
                    AND organization_id = :org_id;";
            $this->model->query_bind($sql, ['judge_id' => $judge_id, 'org_id' => $org_id]);

            redirect('judges');
        }

        public function score_heat() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $this->module("competitions");
            // changing time zones to UTC from org settings
            // $timezone = $this->competitions->_get_org_timezone();
            // date_default_timezone_set($timezone);

            // Get judge info from comp_judges table
            $judge_info = $this->competitions->_get_judge_info();
            $judge_id = (int)$judge_info->id;
            $data['user'] = $judge_info;

            // Find running heat and update status if ended, and start next scheduled heat
            $heat = $this->competitions->_advance_and_get_heat_for_judge($judge_id);

            if (!isset($heat)) {
                $data['error'] = "Heat have not started yet!";
                $sql = "SELECT ch.*, co.timezone
                        FROM comp_heats ch
                        JOIN competition_judges cj ON cj.competition_id = ch.comp_id
                        JOIN comp_name cn ON cn.id = ch.comp_id
                        JOIN comp_organizations co ON co.id = cn.organizer_id
                        WHERE cj.judge_id = ?
                        AND ch.status = 'scheduled'
                        ORDER BY ch.start_time ASC LIMIT 1";
                $data['heat'] = $this->model->query_bind($sql, [$judge_id], 'object');
                $data['view_file'] = 'live_error';
                $this->template('judges_area', $data);
                exit();
            }

            $heat_id = $heat['id'];
            $data['heat_id'] = $heat_id;

            $sql = "SELECT
                    hp.jersey_color,
                    JSON_ARRAYAGG(JSON_OBJECT('score_id', js.id, 'score', CAST(js.score AS DECIMAL(4,2))) ORDER BY js.id) AS waves
                    FROM comp_judge_scores js
                    JOIN comp_heat_participants hp
                    ON hp.heat_id = js.heat_id AND hp.participant_id = js.participant_id
                    WHERE js.heat_id = ? AND js.judge_id = ?
                    GROUP BY hp.jersey_color;";

            $rows = $this->model->query_bind($sql, [$heat_id, $judge_id], 'array');

            foreach ($rows as &$r) {
                // waves is a JSON string -> decode to PHP array
                $waves = json_decode($r['waves'] ?? '[]', true);
                if (!is_array($waves)) {
                    $waves = [];
                }

                // round to 2 decimals to avoid binary float tails
                foreach ($waves as &$w) {
                    // score_id stays int; score normalized to 2-decimal float
                    $w['score_id'] = isset($w['score_id']) ? (int)$w['score_id'] : null;
                    if (isset($w['score'])) {
                        $w['score'] = (float)round($w['score'], 2);
                    }
                }
                unset($w);

                $r['waves'] = $waves;
            }
            unset($r);

            $data['scores'] = $rows;

            // Fetch all participants in the heat
            $sql = "SELECT participant_id FROM comp_heat_participants WHERE heat_id = ?";
            $participants = $this->model->query_bind($sql, [$heat_id], 'array');

            // Assign next wave numbers for each participant
            $wave_numbers = [];
            foreach ($participants as $p) {
                $participant_id = $p['participant_id'];
                
                // Get next wave number for the participant
                $next_wave_number = $this->competitions->_get_next_wave($heat_id, $participant_id, $judge_id);

                // Store the next wave number for each participant
                $wave_numbers[$participant_id] = $next_wave_number;
            }

            $data['missing_scores'] = $this->_get_missing_score($heat_id, $judge_id);

            // Include the wave numbers array in the data
            $data['wave_numbers'] = $wave_numbers;

            // // Include the heat info array in the data
            $data['heat'] = $this->model->get_where($heat_id, 'comp_heats');

            $data['view_file'] = 'scoring';
            $this->template('judges_area', $data);
        }

        function scores_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $this->module("competitions");

            $judge_info = $this->competitions->_get_judge_info();
            $judge_id = (int)$judge_info->id;

            $heat_id = (int)segment(3);
            $data['heat_id'] = $heat_id;
            $participant = (int)segment(4);
            $data['participant_id'] = $participant;
            
            // Fetch all participants in the heat
            $sql = "SELECT participant_id FROM comp_heat_participants WHERE heat_id = ?";
            $participants = $this->model->query_bind($sql, [$heat_id], 'array');

            // Assign next wave numbers for each participant
            $wave_numbers = [];
            foreach ($participants as $p) {
                $participant_id = $p['participant_id'];
                
                // Get next wave number for the participant
                $next_wave_number = $this->competitions->_get_next_wave($heat_id, $participant_id, $judge_id);

                // Store the next wave number for each participant
                $wave_numbers[$participant_id] = $next_wave_number;
            }

            // Include the wave numbers array in the data
            $data['wave_numbers'] = $wave_numbers;

            $sql = "SELECT jersey_color FROM comp_heat_participants WHERE heat_id = ? AND participant_id = ?";
            $jersey = $this->model->query_bind($sql, [$heat_id, $participant], 'object');
            $data['jersey_color'] = $jersey[0]->jersey_color ?? '';

            // // Include the heat info array in the data
            $data['heat'] = $this->model->get_where($heat_id, 'comp_heats');

            $this->view('score_modal', $data);
        }

        function submit_score() {

            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $this->validation->run();
            $this->module("competitions");
 
            $judge_info = $this->competitions->_get_judge_info();
            $judge_id = $judge_info->id;
 
            $heat_id    = (int)post('heat_id', true);
            $participant_id = (int)post('participant_id', true);
            $adjustment = post('adjustment', true) ?? 0; // Optional: if you have score adjustments
            $raw_score  = post('score', true) ?? 0; // Numeric value OR 'M' for missed wave
 
            // Determine whether this is a missed wave
            $is_missed = (strtoupper(trim($raw_score)) === 'M');
 
            // Store NULL for missed waves so they are excluded from averages;
            // otherwise cast to float and clamp to the valid 0–10 range.
            if ($is_missed) {
                $score = null;
            } else {
                $score = max(0, min(10, (float)$raw_score)) + (float)$adjustment; // Apply any adjustments if needed
                $score = round($score, 2); // Round to 2 decimals for consistency
            }
 
            // Fetch next available wave number
            $next_wave = $this->competitions->_get_next_wave($heat_id, $participant_id, $judge_id);
 
            // Prepare data to insert into comp_judge_scores
            $data = [
                'heat_id'        => $heat_id,
                'judge_id'       => $judge_id,
                'participant_id' => $participant_id,
                'score'          => $score,   // NULL when missed
                'wave_number'    => $next_wave
            ];
 
            // Insert the score into comp_judge_scores table
            $this->module('logger');
            $update_id = $this->model->insert($data, 'comp_judge_scores');
            if (!$update_id) { $this->logger->log_message('error', 'Judges::submit_score: failed to insert comp_judge_scores'); }

            $flash_msg = $is_missed
                ? 'Wave no ' . $next_wave . ' marked as Missed (M) – excluded from average'
                : 'The score ' . $score . ' was added for wave no ' . $next_wave;
 
            // After submit to database NextWave becomes WaveNumber
            $wave_number = $next_wave;
 
            $this->competitions->_calculate_and_save_average($heat_id, $wave_number, $participant_id);
            set_flashdata($flash_msg);
 
        }

        public function _get_missing_score($heat_id, $judge_id) {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $sql = "SELECT
                hp.jersey_color,
                ex.participant_id,
                ex.wave_number,
                js_me.id AS my_score_id,
                SUM(CASE WHEN js_oth.judge_id IS NOT NULL AND js_oth.judge_id <> :judge_id THEN 1 ELSE 0 END) AS others_scored
                FROM (
                SELECT DISTINCT participant_id, heat_id, wave_number
                FROM comp_judge_scores
                WHERE heat_id = :heat_id
                ) ex
                JOIN comp_heat_participants hp
                ON hp.heat_id = ex.heat_id
                AND hp.participant_id = ex.participant_id
                LEFT JOIN comp_judge_scores js_me
                ON js_me.heat_id        = ex.heat_id
                AND js_me.participant_id = ex.participant_id
                AND js_me.wave_number    = ex.wave_number
                AND js_me.judge_id       = :judge_id
                LEFT JOIN comp_judge_scores js_oth
                ON js_oth.heat_id        = ex.heat_id
                AND js_oth.participant_id = ex.participant_id
                AND js_oth.wave_number    = ex.wave_number
                WHERE js_me.id IS NULL
                GROUP BY hp.jersey_color, ex.participant_id, ex.wave_number, js_me.id
                HAVING others_scored > 0
                ORDER BY FIELD(hp.jersey_color,'white','red','green','blue'), ex.wave_number";

                $params = ['heat_id' => (int)$heat_id, 'judge_id' => (int)$judge_id];
                $missing = $this->model->query_bind($sql, $params, 'array');

                return $missing;
        }

    }