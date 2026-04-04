<?php
    class Competitions extends Trongate {
        
        // Area for judges to login and access their competition heats scoring
        function index() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $judge = $this->_get_judge_info();
            $data['user'] = $judge;

            if(isset($judge)) {
                if ($judge->role == 'organizer') {
                    $data['view_file'] = 'judge_dash';
                    $this->template('judges_area', $data); 
                } else {
                    // invited judges see available competitions
                    $sql = "SELECT * FROM competition_judges WHERE judge_id = :judge_id AND status = 'accepted'";
                    $comp_judge = $this->model->query_bind($sql, ['judge_id' => $judge->id], 'array');

                    if ($comp_judge) {
                        $competition = $this->model->get_one_where('id', $comp_judge[0]['competition_id'], 'comp_name');
                        $data['comp_name'] = $competition->name  . ' ' . $competition->year;
                        $data['view_file'] = 'judge_dash';
                        $this->template('judges_area', $data); 
                    } else {
                        // judge has not accepted invitation yet
                        redirect('judges');
                    }
                }
            } else {
                // no judge info found, redirect to login
              $this->login();  

            }
        }

        public function heat_time() {
            // changing time to UTC from org settings
            // $timezone = $this->_get_org_timezone();
            // date_default_timezone_set($timezone);

            $heat = $this->model->get_one_where('status', 'running', 'comp_heats');
            $future_time = new DateTime($heat->end_time); // Convert string to DateTime
            $current_time = new DateTime(); // Get current time

            if ($future_time > $current_time) {
                $interval = $current_time->diff($future_time);
            } else {
                $interval = $future_time->diff($current_time);
            }
            echo $interval->format('%i:%s');  // Outputs MM:SS
        }   
    // This is a test function to reset a competition to "closed" status for testing purposes. Not for production use.
    // Delete heats, scores, results, etc. for a full reset. leave only participants and comp record for testing.
        public function restart_test_comp() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $comp_id = segment(3, 'int');
            $comp = $this->model->get_one_where('id', $comp_id, 'comp_name');
            if (!$comp || $comp->status === 'finished') {
                echo "Competition not found or finished.";
                return;
            }
            $data['status'] = 'closed';
            $this->model->update($comp_id, $data, 'comp_name');

            // Delete related heats, scores, results, etc. for a full reset
            $sql = "DELETE ch, chp, cjs, cwa, chr, cha
                    FROM comp_heats ch
                    LEFT JOIN comp_heat_participants chp ON ch.id = chp.heat_id
                    LEFT JOIN comp_judge_scores cjs ON ch.id = cjs.heat_id
                    LEFT JOIN comp_wave_averages cwa ON ch.id = cwa.heat_id
                    LEFT JOIN comp_heat_advancement cha ON ch.id = cha.from_heat_id
                    LEFT JOIN comp_heat_results chr ON ch.id = chr.heat_id
                    WHERE ch.comp_id = :comp_id";
            $this->model->query_bind($sql, ['comp_id' => $comp_id]);
            echo "Competition restarted successfully.";    
        }

//---------------------------------------------------------------
//----------------------- JUDGING COMP --------------------------
        
        function judge_scores() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            // Get judge info from comp_judges table
            $judge_info = $this->_get_judge_info();
            $data['judge'] = $judge_info;

            // Fetch currently running heat
            $heat = $this->_get_running_heat();
            
            if (!$heat) {
                // changing time zones to UTC from org settings
                // $timezone = $this->_get_org_timezone();
                // date_default_timezone_set($timezone);
                $current_time = date("Y-m-d H:i:s");
                $data['error'] = "No heat has your scores yet!";
                $data['heat'] = $this->model->get_where_custom('status', 'scheduled', '=', 'start_time ASC', 'comp_heats', 1);
                $this->view('scores_error', $data);
                die();
            }

            $data['heat_id'] = $heat->id;  
            $this->view('judge_scores', $data);
        }

        //---------- deprisiated function, now in judges module -----------
        function score_heat() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            // changing time zones to UTC from org settings
            // $timezone = $this->_get_org_timezone();
            // date_default_timezone_set($timezone);
            
            // Get judge info from comp_judges table
            $judge_info = $this->_get_judge_info();
            $judge_id = (int)$judge_info->id;
            $data['user'] = $judge_info;

            // Find running heat and update status if ended, and start next scheduled heat
            $heat = $this->_advance_and_get_heat_for_judge($judge_id);

            // If finished heat found goes to _process_heat_results() in heats module
            // $update_status = $this->update_heat_status();

            // Fetch currently running heat for assigned judge
            //$heat = $this->_get_running_heat($judge_id);

            if (!isset($heat)) {
                $data['error'] = "Heat have not started yet!";
                $sql = "SELECT ch.*
                        FROM comp_heats ch
                        JOIN competition_judges cj ON cj.competition_id = ch.comp_id
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

            // Fetch timezone for the heat's competition
            $comp = $this->model->get_one_where('id', $heat['comp_id'], 'comp_name');
            $data['timezone'] = $comp->timezone;

            // Fetch all participants in the heat
            $sql = "SELECT participant_id FROM comp_heat_participants WHERE heat_id = ?";
            $participants = $this->model->query_bind($sql, [$heat_id], 'array');

            // Assign next wave numbers for each participant
            $wave_numbers = [];
            foreach ($participants as $p) {
                $participant_id = $p['participant_id'];
                
                // Get next wave number for the participant
                $next_wave_number = $this->_get_next_wave($heat_id, $participant_id, $judge_id);
                
                // Store the next wave number for each participant
                $wave_numbers[$participant_id] = $next_wave_number;
            }

            // Include the wave numbers array in the data
            $data['wave_numbers'] = $wave_numbers;

            // // Include the heat info array in the data
            $data['heat'] = $this->model->get_where($heat_id, 'comp_heats');

            $data['view_file'] = 'score_heat';
            $this->template('judges_area', $data);
        }
        //-----------------------------------------------------------------

        //------------- Submit score for a participant in a heat -------------
        function score_submit() {
 
            $heat_id = post('heat_id', true);
            $judge_id = post('judge_id', true);
            $jersey_color = post('jersey_color', true);
            $raw_score = post('score', true); // Numeric value OR 'L' for missed wave
 
            // Determine whether this is a missed wave
            $is_missed = (strtoupper(trim($raw_score)) === 'L');
 
            // Store NULL for missed waves so they are excluded from averages;
            // otherwise cast to float and clamp to the valid 0–10 range.
            if ($is_missed) {
                $score = null;
            } else {
                $score = max(0, min(10, (float)$raw_score));
            }
 
            // Fetch participant based on jersey color
            $participant_id = $this->_get_participantId_By_JerseyColor($jersey_color, $heat_id);  
 
            // Fetch next available wave number
            $next_wave = $this->_get_next_wave($heat_id, $participant_id, $judge_id);
 
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
            if (!$update_id) { $this->logger->log_message('error', 'Competitions::submit_score: failed to insert comp_judge_scores'); }

            $flash_msg = $is_missed
                ? 'Wave no ' . $next_wave . ' marked as Missed (L) – excluded from average'
                : 'The score ' . $score . ' was added for wave no ' . $next_wave;
 
            // After submit to database NextWave becomes WaveNumber
            $wave_number = $next_wave;
 
            $this->_calculate_and_save_average($heat_id, $wave_number, $participant_id);
            set_flashdata($flash_msg);
 
        }

        // Helper function to calculate and save average score for a wave
        function _calculate_and_save_average($heat_id, $wave_number, $participant_id) {
            // Fetch all scores for the wave and participant, excluding missed waves (NULL score)
            $sql = "SELECT score FROM comp_judge_scores 
                    WHERE heat_id = ? AND wave_number = ? AND participant_id = ?
                    AND score IS NOT NULL";
            $data = [$heat_id, $wave_number, $participant_id];
            $scores = $this->model->query_bind($sql, $data, 'object');
 
            // Calculate the new average (only judges who gave a numeric score count)
            $total_score  = 0;
            $total_judges = count($scores);
 
            foreach ($scores as $score) {
                $total_score += $score->score;
            }
 
            // Avoid division by zero (e.g. all judges marked the wave as missed)
            $average_score = $total_judges > 0 ? round($total_score / $total_judges, 2) : 0;
 
            $data_avg = [
                'heat_id'        => $heat_id,
                'wave_number'    => $wave_number,
                'participant_id' => $participant_id,
                'avg_score'      => $average_score
            ];
 
            // Check if the average score already exists for this wave and participant
            $sql_existing = "SELECT id FROM comp_wave_averages WHERE heat_id = ? AND wave_number = ? AND participant_id = ?";
            $existing_data = [$heat_id, $wave_number, $participant_id];
            $existing_id = $this->model->query_bind($sql_existing, $existing_data, 'array');
 
            // Insert or update the average score in the `comp_wave_averages` table
            if ($existing_id) {
                $avg_id = $existing_id[0]['id'];
                $this->model->update($avg_id, $data_avg, 'comp_wave_averages');
            } else {
                $this->model->insert($data_avg, 'comp_wave_averages');
            }
        }  

        // Helper function to show only heats assigned to the judge
        public function _get_running_heat($judge_id) {
            // changing time zones to UTC from org settings
            // $timezone = $this->_get_org_timezone();
            // date_default_timezone_set($timezone);
            $current_time = date("Y-m-d H:i:s");
            // Get runnning heat assigned to the judge
            $sql = "SELECT ch.*
                    FROM comp_heats ch
                    JOIN competition_judges cj ON ch.comp_id = cj.competition_id
                    WHERE ch.status = 'running' 
                    AND cj.judge_id = ?
                    ORDER BY ch.id
                    ASC LIMIT 1";
            $running_heat = $this->model->query_bind($sql, [$judge_id], 'array');

            if (!isset($running_heat[0])) {
                // If no running heat, find the next scheduled heat that should start
                $sql = "SELECT ch.* 
                        FROM comp_heats ch
                        JOIN competition_judges cj ON ch.comp_id = cj.competition_id
                        WHERE ch.status = 'scheduled'
                        AND cj.judge_id = ?
                        AND ch.start_time <=  ?
                        ORDER BY ch.start_time
                        ASC LIMIT 1";
                $scheduled_heat = $this->model->query_bind($sql, [$judge_id, $current_time], 'array');

                if ($scheduled_heat) {
                    $heat_id = $scheduled_heat[0]['id'];

                    // Update heat status to "running"
                    $update_data = ['status' => 'running'];
                    $this->model->update($heat_id, $update_data, 'comp_heats');

                    // If competition is still 'generated', move it to 'running'
                    // only trigger once — on the very first heat started
                    if ($scheduled_heat[0]['status'] === 'pending' || $scheduled_heat[0]['status'] === 'scheduled') {
                        $comp = $this->model->get_where($scheduled_heat[0]['comp_id'], 'comp_name');
                        if ($comp->status === 'generated') {
                            $this->model->update($scheduled_heat[0]['comp_id'], ['status' => 'running'], 'comp_name');
                        }
                    }

                    return $this->model->get_where($heat_id, 'comp_heats');
                }
            }
            return $running_heat ? $running_heat[0] : null;
        }
        
        // Find running heat and update status if ended, and start next scheduled heat
        function update_heat_status() {
            // changing time zones to UTC from org settings
            // $timezone = $this->_get_org_timezone();
            // date_default_timezone_set($timezone);
            $current_time = date("Y-m-d H:i");

            // Step 1: Find the heat that is currently "Running" and has ended
            $sql = "SELECT * 
                    FROM comp_heats
                    WHERE status = 'running' 
                    AND end_time <= ?";
            $running_heat = $this->model->query_bind($sql, [$current_time], 'array');

            if (!empty($running_heat)) {

                $heat_id = $running_heat[0]['id'];

                // Step 2: Introduce a delay (e.g., 2 minutes) before marking as finished
                $delay_time = date("Y-m-d H:i", strtotime("+2 minutes", strtotime($running_heat[0]['end_time'])));

                if ($current_time >= $delay_time) {
 
                    // Update heat results and put status to "Finished"
                    $this->module('heats');
                    $this->heats->_process_heat_results($heat_id);

                    $data['status'] = 'finished';
                    $this->model->update($heat_id, $data, 'comp_heats');

                    // Step 3: Find the next scheduled heat and update its status to "Running"
                    $sql_next = "SELECT * FROM comp_heats WHERE status = 'scheduled' AND start_time <= ? ORDER BY start_time ASC LIMIT 1";
                    $next_heat = $this->model->query_bind($sql_next, [$current_time], 'array');

                    if (!empty($next_heat)) {
                        $next_heat_id = $next_heat[0]['id'];
                        $next_data['status'] = 'running';
                        $this->model->update($next_heat_id, $next_data, 'comp_heats');
                    }

                } 
            }
        }

        function _get_next_wave($heat_id, $participant_id, $judge_id) {

            // Get the next wave number for this participant in the heat
            $sql = "SELECT COALESCE(MAX(wave_number), 0) + 1 AS next_wave FROM comp_judge_scores WHERE heat_id = ? AND participant_id = ? AND judge_id = ?";
            $result = $this->model->query_bind($sql, [$heat_id, $participant_id, $judge_id], 'object');
            
            return $result ? (int)$result[0]->next_wave : 1;
        }

        function _get_participantId_By_JerseyColor($jersey_color, $heat_id) {

            $sql = "SELECT participant_id FROM comp_heat_participants 
                    WHERE heat_id = ? AND jersey_color = ? LIMIT 1";
            $participant = $this->model->query_bind($sql, [$heat_id, $jersey_color], 'array');
            $participant_id = $participant[0]["participant_id"];

            return $participant_id ? (int)$participant_id : null;
        }
        // UPDATED JUDGE HEAT FETCH AND ADVANCE FUNCTION
        public function _advance_and_get_heat_for_judge(int $judge_id): ?array {
            // changing time zones to UTC from org settings
            // $timezone = $this->_get_org_timezone();
            // date_default_timezone_set($timezone);

            $now = gmdate('Y-m-d H:i:s');

            $this->model->query('START TRANSACTION');

            try {
                // 1) Finish any ended running heat(s) in judge's competitions (with +2 min buffer)
                $sqlEnded = "SELECT ch.id
                            FROM comp_heats ch
                            JOIN competition_judges cj ON cj.competition_id = ch.comp_id
                            WHERE cj.judge_id = :judge_id
                            AND ch.status = 'running'
                            AND TIMESTAMPADD(MINUTE, 2, ch.end_time) <= :now
                            FOR UPDATE";
                $ended = $this->model->query_bind($sqlEnded, ['judge_id' => $judge_id, 'now' => $now], 'array');

                if (!empty($ended)) {
                    $this->module('heats');
                    foreach ($ended as $row) {
                        $heat_id = (int)$row['id'];

                        // process results once the buffer has passed
                        $this->heats->_process_heat_results($heat_id);

                        $this->model->update($heat_id, ['status' => 'finished'], 'comp_heats');
                    }
                }

                // 2) If there is a currently running heat for this judge, return it
                $sqlRunning = "SELECT ch.*
                            FROM comp_heats ch
                            JOIN competition_judges cj ON cj.competition_id = ch.comp_id
                            WHERE cj.judge_id = :judge_id
                            AND ch.status = 'running'
                            ORDER BY ch.start_time ASC, ch.id ASC
                            LIMIT 1
                            FOR UPDATE";
                $running = $this->model->query_bind($sqlRunning, ['judge_id' => $judge_id], 'array');

                if (!empty($running)) {
                    $this->model->query('COMMIT');
                    return $running[0];
                }

                // 3) Promote the next scheduled heat (that should already have started) to running
                $sqlNext = "
                    SELECT ch.*
                    FROM comp_heats ch
                    JOIN competition_judges cj ON cj.competition_id = ch.comp_id
                    WHERE cj.judge_id = :judge_id
                    AND ch.status = 'scheduled'
                    AND ch.start_time <= :now
                    ORDER BY ch.start_time ASC, ch.id ASC
                    LIMIT 1
                    FOR UPDATE";
                $next = $this->model->query_bind($sqlNext, ['judge_id' => $judge_id, 'now' => $now], 'array');

                if (!empty($next)) {
                    $heat_id = (int)$next[0]['id'];
                    $this->model->update($heat_id, ['status' => 'running'], 'comp_heats');
                    $this->model->query('COMMIT');
                    // re-read to return fresh row if your model caches/doesn't return updated columns
                    return $this->model->get_where($heat_id, 'comp_heats');
                }

                $this->model->query('COMMIT');
                return null;

            } catch (\Throwable $e) {
                $this->model->query('ROLLBACK');
                // optionally log $e
                return null;
            }
        }

//---------------------------------------------------------------
//----------------------- PARTICIPANTS --------------------------

        function create_participant() {
            $comp = $this->model->get_one_where('status', 'open', 'comp_name');
            $comp_id = $comp->id ?? 0;
            $data['rows'] = $comp;
            $sql = "SELECT 
                    cd.id AS id,
                    cd.name AS name
                    FROM comp_competition_divisions ccd
                    JOIN comp_divisions cd ON ccd.division_id = cd.id
                    WHERE ccd.competition_id = $comp_id";
            $data['divisions'] = $this->model->query($sql, 'object');
            $data['view_file'] = 'create_participant';
            $this->template('public', $data);
        }

        function edit_participant() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $record_id = (int) segment(3);
            
            $record_obj = $this->model->get_where($record_id, 'comp_participants');
            $comp_id = $record_obj->comp_id ?? 0;
            $competition = $this->model->get_one_where('id', $comp_id, 'comp_name');
            $division = $this->model->get_one_where('name', $record_obj->gender_age, 'comp_divisions');
            
            $data = (array)$record_obj;

            $sql = "SELECT 
                    cd.id AS id,
                    cd.name AS name
                    FROM comp_competition_divisions ccd
                    JOIN comp_divisions cd ON ccd.division_id = cd.id
                    WHERE ccd.competition_id = $comp_id";
            $data['divisions'] = $this->model->query($sql, 'object');

            $data['division_id'] = $division->id ?? 0;

            $data['row'] = $competition;

            $this->view('edit_participant', $data);
        }

        function delete_participant() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $record_id = (int) segment(3);

            $data['update_id'] = $record_id;
            $this->view('delete_participant_modal', $data);
        }

        function submit_delete_participant() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $this->validation->run();

            $record_id = (int)segment(3);

            $this->model->delete($record_id, 'comp_participants');
        }

        function show_participants() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            // Be consistent with get_show_only(): if it reads segment(3), keep it that way.
            $this->module("heats");
            $show_only_value = $this->heats->_get_show_only(); // expects values like '' or '2' as division_id
            $show_only = $this->model->get_one_where('name', $show_only_value, 'comp_divisions')->id ?? '';
            $comp_id_raw = segment(3);

            $comp_id = ($comp_id_raw !== '' && $comp_id_raw !== null) ? (int) $comp_id_raw : null;

            $where = [];
            $conditions = [];

            
            $org_id = $this->_get_organizer_id();
            if (!$org_id) {
                $judge_id = $this->_get_judge_info()->id;
                $sql_org = "SELECT organizer_id 
                            FROM competition_judges AS cj
                            JOIN comp_name AS cn ON cj.competition_id = cn.id
                            WHERE judge_id = :judge_id 
                            AND competition_id = :competition_id
                            AND cn.status NOT IN ('finished', 'created', 'scheduled')
                            LIMIT 1";
                $org = $this->model->query_bind($sql_org, ['judge_id' => $judge_id, 'competition_id' => $comp_id], 'object');
                $org_id = $org[0]->organizer_id ?? 0;
            }
            $where['organizer_id'] = $org_id;

            $sql = "SELECT 
                        p.id,
                        p.user_id,
                        p.division_id,
                        cd.name AS gender_age,
                        concat(p.first_name, ' ', p.last_name) AS name,
                        p.status,
                        c.id AS competition_id,
                        CONCAT(c.name, ' ', c.year) AS competition_name,
                        c.entry_type,
                        cu.name  AS user_name,
                        cu.email AS user_email
                    FROM comp_participants p
                    JOIN comp_name c ON p.comp_id = c.id
                    LEFT JOIN comp_users cu ON p.user_id = cu.id
                    JOIN comp_divisions cd ON p.division_id = cd.id
                    WHERE c.status NOT IN ('finished', 'created', 'scheduled')
                    AND c.organizer_id = :organizer_id";

            if (!is_null($comp_id)) {
                $where['comp_id'] = $comp_id;
                $conditions[] = 'p.comp_id = :comp_id';
            }

            if ($show_only !== '') {
                $where['division_id'] = $show_only;
                $conditions[] = 'p.division_id = :division_id';
            }

            if (!empty($conditions)) {
                $sql .= ' AND ' . implode(' AND ', $conditions);
            }

            // Optional: add a deterministic order
            $sql .= ' ORDER BY p.division_id ASC, p.id ASC';

            // echo $sql;die(); // For debugging: see the final SQL query

            $data['rows'] = $this->model->query_bind($sql, $where, 'object');
            $data['comp_id'] = $comp_id;
            $data['view_file'] = 'show_participants';

            $this->template('judges_area', $data);
        }

        function submit_create_participant() {

            $this->validation->set_rules('first_name', 'First Name', 'required|min_length[4]|max_length[55]');
            $this->validation->set_rules('last_name', 'Last Name', 'required|min_length[4]|max_length[55]');
            $this->validation->set_rules('email', 'Email', 'required|valid_email');
            $this->validation->set_rules('comp_id', 'comp_id', 'required|min_length[1]|integer');

            $result = $this->validation->run(); //returns true or false
            
            $update_id = segment(3, 'int');
        
            if($result === true) {
    
                // Now build up array of $data for the comp_participants record.
                $data['first_name'] = post('first_name', true);
                $data['last_name'] = post('last_name', true);
                $data['email'] = post('email', true);
                $data['comp_id'] = post('comp_id', true);

                $division_id = post('division_id', true);
                $gender_age = $this->model->get_one_where('id', $division_id, 'comp_divisions');
                $data['gender_age'] = $gender_age->name ?? '';

                if ($update_id>0) {
                    //update an existing record
                    $this->model->update($update_id, $data, 'comp_participants');
                    $flash_msg = 'The record was successfully updated';
                } else {
                    //insert the new record
                    $update_id = $this->model->insert($data, 'comp_participants');
                    $flash_msg = 'The record was successfully created';
                }

                echo '<p style="color:white;background:green;text-align: center;padding: 0.5rem;">Participant successfully registered!</p>';

            } else {

                echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">Form fields have to be filled!</p>';
            
            }
        }

        function confirm_participant() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $record_id = (int) segment(3);

            $data['status'] = 'confirmed';
            $this->model->update($record_id, $data, 'comp_participants');
        }

//----------------- PARTICIPANTS END ----------------------------
//---------------------------------------------------------------

//---------------------------------------------------------------
//------------------ COMPETITION EVENT --------------------------

        function create_comp() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $user_id = $this->_get_organizer_user_id();

            $sql = "SELECT * FROM comp_name WHERE NOT status = 'finished' AND organizer_id = $user_id ORDER BY id DESC";
            $data['rows'] = $this->model->query($sql, 'object');

            $data['num_rows'] = count($data['rows']);

            // Fetch all divisions from comp_divisions
            $data['divisions'] = $this->model->get('name ASC', 'comp_divisions');
            $this->view('create_comp', $data);
        }

        public function submit_create_comp() {
            $this->module('trongate_security');
            $this->module('billings');
            $this->trongate_security->_make_sure_allowed('organizers area');

            // Set validation rules
            $this->validation->set_rules('name', 'name', 'required|min_length[6]|max_length[55]');
            $this->validation->set_rules('year', 'year', 'required|exact_length[4]');
            $this->validation->set_rules('location', 'location', 'required|min_length[3]|max_length[55]');
            $this->validation->set_rules('entry_type', 'entry type', 'min_length[4]|max_length[15]');
            $this->validation->set_rules('entry_fee', 'entry fee', 'min_length[1]|max_length[5]|integer');
            $this->validation->set_rules('start_date', 'start date', 'required');
            $this->validation->set_rules('end_date', 'end date', 'required');

            $result = $this->validation->run(); //returns true or false
            
            $update_id = (int)segment(3);

            $user_id = $this->_get_organizer_user_id();

            if($result === true) {
                // Create new competition.
                // Now build up array of $data for the comp record.
                $data['name'] = post('name', true);
                $data['year'] = post('year', true);
                $data['location'] = post('location', true);

                if ($update_id>0) {
                    //update an existing record
                    $comp_obj = $this->model->get_where($update_id, 'comp_name');
                    if (in_array($comp_obj->status, ['created', 'scheduled','open'])) {
                        $data['status'] = post('status', true);
                        $data['entry_type'] = post('entry_type', true);
                        $data['entry_fee'] = post('entry_fee', true);
                        $data['start_date'] = post('start_date', true);
                        $data['end_date'] = post('end_date', true);
                    } elseif (in_array($comp_obj->status, ['closed', 'running', 'generated'])) {
                        // once closed, only certain fields can be updated
                        $data['status'] = post('status', true);
                        $data['end_date'] = post('end_date', true);
                    }
                    $this->model->update($update_id, $data, 'comp_name');
                    $flash_msg = 'The record was successfully updated!';
                    set_flashdata($flash_msg);
                    return;
                } else {
                    // Check if the user can create a new competition, has subscription or event passes left
                    // if ($this->billings->ensure_can_create_comp($user_id) === false) {
                    //    $flash_msg = 'You have reached your free plan limit. Please upgrade your plan or buy additional event pass.';
                    //    echo '<p style="color:black;background:orange;text-align:center;padding:0.5rem;">' . $flash_msg . '</p>';
                    //    set_flashdata($flash_msg);
                    //    return;
                    // }
                    $divisions = post('divisions', true);
                    $data['status'] = 'created';
                    $data['organizer_id'] = $user_id; // Set
                    //insert the new record
                    $this->module('logger');
                    $update_id = $this->model->insert($data, 'comp_name');
                    if (!$update_id) { $this->logger->log_message('error', 'Competitions::submit_create_competition: failed to insert comp_name'); }
                    
                    // Insert into comp_competition_divisions
                    if (is_array($divisions)) {
                        foreach ($divisions as $division_id) {
                            $link_data = [
                                'competition_id' => $update_id,
                                'division_id' => $division_id
                            ];
                            $this->model->insert($link_data, 'comp_competition_divisions');
                        }
                    }
                    $flash_msg = 'The record was successfully created!';
                    //echo '<p style="color:white;background:green;text-align: center;padding: 0.5rem;">' . $flash_msg . '</p>';
                    set_flashdata($flash_msg);
                    http_response_code(200);
                    echo 'competitions';
                }

            } else {
                $flash_msg = 'Form fields have to be filled!';
                echo '<p style="color:black;background:orange;text-align:center;padding:0.5rem;">' . $flash_msg . '</p>';
                echo validation_errors();
                set_flashdata($flash_msg);
                http_response_code(422);
            }
        }

        function edit_comp() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('organizers area');

            $record_id = (int) segment(3);
            
            $comp = $this->model->get_where($record_id, 'comp_name');
            $data = (array)$comp; 

            $this->view('edit_comp', $data);
        }

        public function submit_delete_comp() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('organizers area');
            $this->validation->run();
            $record_id = (int)segment(3);
            $object = $this->model->get_where($record_id, 'comp_name');
            $organizer_id = $this->_get_organizer_user_id();
            if (isset($object) && !in_array($object->status, ['closed','generated', 'running', 'finished']) && $object->organizer_id === $organizer_id) {
                $this->model->delete($record_id, 'comp_name');
                echo '<p style="color:white;background:green;text-align: center;padding: 0.5rem;">Competition with id ' . $record_id . ' was deleted!</p>';
            }
            echo '<p style="color:black;background:orange;text-align:center;padding:0.5rem;">Cannot delete this. Due to competition being closed, running or finished!</p>';
        }

        public function delete_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $update_id = (int) segment(3);
            $data['update_id'] = $update_id;
            $this->view('delete_modal', $data);
        }

        public function get_competitions() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $user_id = $this->_get_organizer_user_id();

            if ($user_id === 0) {
                $user = $this->_get_judge_info();
                // If the user is an admin, we can return all competitions created by same organizer
                if ($user->role !== 'head_judge') {
                    $user_id = 0;
                } else {
                    $user_id = $user->organizer_id;
                }
            }

            $comp = $this->model->get_many_where('organizer_id', $user_id, 'comp_name');
            return $comp;
        }

        public function _get_generated_competitions() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $user_id = $this->_get_organizer_user_id();

            if ($user_id === 0) {
                $user = $this->_get_judge_info();
                // If the user is an admin, we can return all competitions created by same organizer
                if ($user->role !== 'head_judge' && $user->role !== 'judge') {
                    $user_id = 0;
                } else {
                    $user_id = $user->organizer_id;
                }
            }
            $sql = "SELECT * FROM comp_name WHERE organizer_id = $user_id AND status != 'finished' ORDER BY start_date ASC LIMIT 3";
            $comp = $this->model->query($sql, 'object');
            return $comp;
        }

        public function _get_upcoming_competitions($org_id) {
            $sql = "SELECT * FROM comp_name WHERE organizer_id = $org_id AND status != 'finished' ORDER BY start_date ASC LIMIT 3";
            $competition = $this->model->query($sql, 'object');
            return $competition;
        }

//------------------ COMPETITION EVENT END-----------------------
//---------------------------------------------------------------

//---------------------------------------------------------------
//----------------------- JUDGES --------------------------------

        function create_judge() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $organizer_id = $this->_get_organizer_id();

            $sql = "SELECT cj.id, cj.name, cj.email, cj.phone, cj.username, co.organization, coj.role, coj.status, coj.created_at as date_created 
                    FROM comp_judges cj
                    JOIN comp_org_judges coj ON cj.id = coj.user_id
                    JOIN comp_organizations co ON coj.organization_id = co.id
                    WHERE coj.organization_id = ?";
            $data['rows'] = $this->model->query_bind($sql, [$organizer_id], 'object');

            //$data['rows'] = $this->model->get_many_where('organizer_id', $organizer_id, 'comp_judges');
            $data['view_file'] = 'create_judge';
            $this->template('judges_area', $data);
        }

        function judge_profile() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $user = $this->_get_judge_info();

            $data = (array)$user;
            $data['view_file'] = 'judge_profile';
            $this->template('judges_area', $data);
        }

        function delete_judge_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $update_id  = (int) segment(3);

            $judge = $this->model->get_where($update_id, 'comp_judges', 'object');

            $data['name'] = $judge->name;
            $data['update_id'] = $update_id;

            $this->view('delete_judge_modal', $data);
        }

        function edit_judge_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $update_id = (int)segment(3);
            $organizer_id = $this->_get_organizer_id();
            $sql = "SELECT cj.id, cj.name, cj.email, cj.phone, co.organization, coj.role, coj.status, coj.created_at as date_created FROM comp_judges cj
                    JOIN comp_org_judges coj ON cj.id = coj.user_id
                    JOIN comp_organizations co ON coj.organization_id = co.id
                    WHERE coj.user_id = ? AND coj.organization_id = ?";                   
            $record_obj = $this->model->query_bind($sql, [$update_id, $organizer_id], 'object');
            $data = (array)$record_obj[0];
            $this->view('edit_judge', $data);
        }

        function submit_edit_judge() {
            $this->module('trongate_security');
            $this->module('trongate_tokens');
            $this->trongate_security->_make_sure_allowed('judges area');
            $level = $this->trongate_tokens->_get_user_level();

            $judge_id = (int)segment(3);

            // ================= ORGANIZER PATH =================
            if ($level === 'organizer') {
                // We need the org to which this judge assignment applies
                $organization_id = $this->_get_organizer_id();

                $sql = "SELECT id FROM comp_org_judges WHERE user_id = :uid AND organization_id = :org LIMIT 1";
                $data = [
                    'uid' => $judge_id,
                    'org' => $organization_id
                ];
                $link = $this->model->query_bind($sql, $data, 'object');

                $record_id = $link[0]->id;

                $role   = post('role', true);
                $status = post('status', true);

                $update_data = [
                    'role'            => $role,
                    'status'          => $status
                ];
                $this->model->update($record_id, $update_data, 'comp_org_judges');

                return 'Judge assignment updated successfully.';
            }

            // ================= JUDGE PATH (self-edit) =================
            // Ownership: ensure this comp_judges row belongs to the logged-in user
            if ($level === 'judge') {
                $token_id = $this->_get_judge_id();

                if ((int)$token_id == (int)$judge_id) {
                    $judge = $this->model->get_where($judge_id, 'comp_judges');
                    // Validate the form data

                    if ($judge->email !== post('email', true)) {
                        $this->validation->set_rules('email', 'email', 'required|valid_email|max_length[255]|callback_email_unique');
                    }

                    $this->validation->set_rules('name', 'name', 'required|min_length[2]|max_length[100]');
                    $this->validation->set_rules('phone', 'phone', 'min_length[8]|max_length[20]');

                    $result = $this->validation->run(); //returns true or false

                    if ($result === true) {
                        // Now build up array of $data for the judges record.
                        $data = [
                            'name'     => post('name', true),
                            'email'    => post('email', true),
                            'phone'    => post('phone', true),
                        ];

                        $this->model->update($judge_id, $data, 'comp_judges');

                        echo '<p style="color: black;background: rgb(92, 142, 141);text-align: center;padding: 0.5rem;">Profile updated succesfully!</p>';
                        return;

                    } else {
                        echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">Form fields have to be filled!</p>';
                        return;
                    }
                }
            }
        }

        function submit_edit_organizer() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $record_id = (int)segment(3);
            $user_info = $this->_get_judge_info();

            $organizer_id = $user_info->organizer_id ?? 0;
            if((int)$record_id !== (int)$organizer_id) {
                http_response_code(403);
                echo '<p style="color:black;background:orange;text-align:center;padding:0.5rem;">You do not have permission to perform this action.</p>';
                return; // stop further execution
            }

            if($user_info->email !== post('email', true)) {
                // Email has changed, so check for uniqueness
                $this->validation->set_rules('email', 'email', 'required|min_length[6]|max_length[55]|callback_email_unique');
            }

            $this->validation->set_rules('name', 'name', 'required|min_length[6]|max_length[55]');
            $this->validation->set_rules('organization', 'organization', 'required|min_length[5]|max_length[20]');
            $this->validation->set_rules('phone', 'phone', 'required|min_length[8]|max_length[15]');

            $result = $this->validation->run(); //returns true or false

            if($result === true) {

                // Now build up array of $data for the judges record.
                $data['name'] = post('name', true);
                $data['phone'] = post('phone', true);
                $data['email'] = post('email', true);
                $data['organization'] = post('organization', true);
                $this->model->update($record_id, $data, 'comp_organizations');
                echo '<p style="color: black;text-align: center;padding: 0.5rem;">Judge updated succesfully!</p>';

            } else {

                echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">Form fields have to be filled!</p>';

            }
        }

        function submit_delete_judge() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $this->validation->run();

            $update_id = (int) segment(3);
            $organizer_id = $this->_get_organizer_id();
            $sql = "SELECT * FROM comp_org_judges WHERE user_id = ? and organization_id = ?";
            $params = [$update_id, $organizer_id];
            $judge_obj = $this->model->query_bind($sql, $params, 'object');

            if (!$judge_obj) {
                http_response_code(403);
                echo '<p style="color:black;background:orange;text-align:center;padding:0.5rem;">You do not have permission to perform this action.</p>';
                return; // stop further execution
            }

            $record_id = $judge_obj[0]->id;

            $this->model->delete($record_id, 'comp_org_judges');
            http_response_code(200);
            echo '<p>Judge record deleted successfully!</p>';
            set_flashdata('<p>Judge record deleted successfully!</p>');
            return;
            
        }

        function login() {
            $data['username'] = post('username');
            $data['view_file'] = 'judges_login';
            $this->template('public', $data);
        }

        function logout() {
            $this->module('trongate_tokens');
            $this->trongate_tokens->_destroy();
            redirect('users/login');
        }

        function submit_create_judge() {

            $this->validation->set_rules('name', 'name', 'required|min_length[6]|max_length[55]');
            $this->validation->set_rules('email', 'email', 'required|min_length[6]|max_length[55]|callback_email_unique');
            $this->validation->set_rules('phone', 'username', 'required|min_length[6]|max_length[55]|callback_username_unique');
            $this->validation->set_rules('password', 'password', 'required|min_length[6]|max_length[55]');
            $this->validation->set_rules('role', 'role', 'required|min_length[5]|max_length[11]');
            $this->validation->set_rules('repeat_password', 'repeat password', 'required|matches[password]');

            $result = $this->validation->run(); //returns true or false
        
            if($result === true) {
                // Create new judge account.
                // Start by creating a new record on Trongate users.
                $this->module('logger');
                $trongate_user_data['code'] = make_rand_str(32);
                $trongate_user_data['user_level_id'] = 3; // judge and head judge id.
                $trongate_user_id = $this->model->insert($trongate_user_data, 'trongate_users');
                if (!$trongate_user_id) { $this->logger->log_message('error', 'Competitions::submit_create_judge: failed to insert trongate_users'); }

                // Now build up array of $data for the judges record.
                $data['name'] = post('name', true);
                $data['email'] = post('email', true);
                $data['phone'] = post('phone', true);
                $password = post('password');
                $data['password'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 11]);
                $data['trongate_user_id'] = $trongate_user_id;
                $user_id = $this->model->insert($data, 'comp_judges');
                if (!$user_id) { $this->logger->log_message('error', 'Competitions::submit_create_judge: failed to insert comp_judges'); }

                $data_comp['role'] = post('role', true); // judge role
                $data_comp['user_id'] = $user_id;
                $organizer_id = $this->_get_organizer_id();
                $data_comp['organization_id']= $organizer_id;
                $data_comp['invited_by']= $organizer_id;

                // Insert judge role into comp_user_roles
                $r = $this->model->insert($data_comp, 'comp_org_judges');
                if (!$r) { $this->logger->log_message('error', 'Competitions::submit_create_judge: failed to insert comp_org_judges'); }

                echo '<p style="color: black;background: rgb(92, 142, 141);text-align: center;padding: 0.5rem;font-size: initial;flex: 1;"> Congrats... Judge created succesfully!</p>';
                return; // Stop further execution
                    
            } else {
                // Check if judge already exists by email
                $user_obj = $this->model->get_one_where('email', post('email', true), 'comp_judges');
                if ($user_obj !== false) {
                    // Judge already exists, just link to organization
                    $organizer_id = $this->_get_organizer_id();
                    $user_id = $user_obj->id;

                    $data_comp['role'] = post('role', true); // judge role
                    $data_comp['user_id'] = $user_id;
                    $data_comp['organization_id']= $organizer_id;
                    $data_comp['invited_by']= $organizer_id;
                    
                    // Insert judge role into comp_user_roles
                    $this->model->insert($data_comp, 'comp_org_judges');
                    $message = '<p style="color: black;background: rgb(92, 142, 141);text-align: center;padding: 0.5rem;font-size: initial;flex: 1;"> Congrats... Judge succesfully linked!</p>';
                    echo $message;
                    set_flashdata($message);
                    return; // Stop further execution
    
                }
            }

            echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">Form fields have to be filled!</p>';

        }

        function submit_login() {
            $this->validation->set_rules('username', 'username', 'required|callback_login_check');
            $this->validation->set_rules('password', 'password', 'required');
        
            $result = $this->validation->run(); // Returns true or false.
        
            if($result === false) {
                $this->login();
            } else {
                $username = post('username');
                $remember = (int) post('remember');
                $this->_in_you_go($username, $remember);
            }
        }

        function _get_user_data($username) {
            // Get user data from comp_judges or comp_organizations table.
            $tables = ['comp_judges', 'comp_organizations'];
            $member_obj = false;

            foreach ($tables as $table) {
                $member_obj = $this->model->get_one_where('username', $username, $table);
                if ($member_obj !== false) {
                    break;
                }
            }

            return $member_obj;
        }

        public function _get_organizer_id() {
            $this->module('trongate_tokens');
            $trongate_user_id = $this->trongate_tokens->_get_user_id();
            $user_obj = $this->model->get_one_where('trongate_user_id', $trongate_user_id, 'comp_organizations');
            return $user_obj->id ?? 0;
        }

        function _in_you_go($username, $remember) {
            // Get trongate_user_id for this user.
            $member_obj = $this->_get_user_data($username);

            $trongate_user_id = $member_obj->trongate_user_id;

            // Create trongate token using token model.
            $this->module('trongate_tokens');
            $trongate_token_data['user_id'] = $trongate_user_id;

            if($remember === 1) {
                $trongate_token_data['set_cookie'] = true;
            }

            $this->trongate_tokens->_generate_token($trongate_token_data);

            redirect('competitions');

        }

        function username_unique($username) {
            $member_obj = $this->model->get_one_where('username', $username, 'comp_judges');
            if($member_obj === false) {
                return true; // username is available
            } else {
                $error_msg = 'Username is not available!';
                return $error_msg;
            }
        }

        function email_unique($email) {
            $member_obj = $this->model->get_one_where('email', $email, 'comp_judges');
            if($member_obj === false) {
                return true; // email is available
            } else {
                $error_msg = 'Email is not available!';
                return $error_msg;
            }
        }

        function login_check($username) {

            $error_msg = 'Your username and/or password was not correct!';

            // Make sure this username exist on members table.
            $member_obj = $this->_get_user_data($username);

            if ($member_obj === false) {
                return $error_msg; // Username does not exist.
            }
            // Check to see if password is valid
            $password = post('password');

            $stored_password = $member_obj->password;
            $is_password_valid = password_verify($password, $stored_password);

            if($is_password_valid === true) {
                return true;
            } else {
                return $error_msg;
            }
        }

        function _make_sure_allowed() {
            //Make sure the user is loged in as a judge
            $this->module('trongate_tokens');
            $token = $this->trongate_tokens->_attempt_get_valid_token([3,4]);
       
            if($token === false) {
                redirect('users/login');
            } else {
                return $token;
            }
        }

        public function _get_judge_info() {
            $this->module('trongate_tokens');
            $trongate_user_id = $this->trongate_tokens->_get_user_id();

            $sql = "SELECT
                    cj.id,
                    cj.name,
                    cj.email,
                    cj.phone,
                    co.organization,
                    coj.role,
                    coj.status,
                    coj.created_at AS date_created,
                    co.id AS organizer_id
                    FROM comp_judges cj
                    JOIN comp_org_judges    coj ON cj.id = coj.user_id
                    JOIN comp_organizations co  ON co.id = coj.organization_id
                    JOIN competition_judges cmj  ON cmj.judge_id = cj.id
                    JOIN comp_name  c    ON c.id = cmj.competition_id
                                                AND c.organizer_id = co.id
                    WHERE cj.trongate_user_id = :tui

                    UNION ALL

                    -- returns organizer rows (when the same TG user is an organizer)
                    SELECT
                    co.id,
                    co.name,
                    co.email,
                    co.phone,
                    co.organization,
                    'organizer' AS role,
                    'active'    AS status,
                    co.date_created,
                    co.id AS organizer_id
                    FROM comp_organizations co
                    WHERE co.trongate_user_id = :tui";
            $data = ['tui' => $trongate_user_id];
            $info = $this->model->query_bind($sql, $data, 'object');
            $judge_info = $info[0] ?? false;
            return $judge_info;
        }

        public function _get_judge_id() {
            $this->module('trongate_tokens');
            $trongate_user_id = $this->trongate_tokens->_get_user_id();

            $judge_info = $this->model->get_one_where('trongate_user_id', $trongate_user_id, 'comp_judges');

            return (int)$judge_info->id;
        }

        public function _get_organizer_user_id() {
            $user = $this->_get_judge_info();
            return ($user && $user->role === 'organizer') ? $user->id : 0;
        }

        public function _is_comp_judge() {
            $this->module('trongate_tokens');
            $trongate_user_id = $this->trongate_tokens->_get_user_id();

            $judge_info = $this->model->get_one_where('trongate_user_id', $trongate_user_id, 'comp_judges');

        }

        public function _get_org_timezone() {
            $judge = $this->_get_judge_info();
            if ($judge->role === 'organizer') {
                $org = $this->model->get_where($judge->id, 'comp_organizations');
                return $org->timezone;
            }
            $sql = "SELECT co.timezone, co.id as organizer
                    FROM competition_judges cj
                    JOIN comp_name cn ON cn.id = cj.competition_id
                    JOIN comp_organizations co ON co.id = cn.organizer_id
                    WHERE cj.judge_id = $judge->id AND cj.status = 'accepted';";
            $org_timezone = $this->model->query($sql, 'array');

            return $org_timezone[0]['timezone'];
        }
        
        public function _get_comp_timezone($comp_id) {
            $sql = "SELECT co.timezone, co.id as organizer
                    FROM comp_organizations co
                    JOIN comp_name cn ON cn.organizer_id = co.id
                    WHERE cn.id = $comp_id;";
            $org_timezone = $this->model->query($sql, 'array');

            return $org_timezone[0]['timezone'];
        }

//----------------------- JUDGES END ----------------------------
//---------------------------------------------------------------

//---------------------------------------------------------------
//--------------------- RESULTS BEGIN ---------------------------

        function get_final_results($heat_id) {
            // Step 1: Get average scores per wave
            $sql = "SELECT participant_id, wave_number, AVG(score) AS avg_wave_score
                    FROM comp_judge_scores
                    WHERE heat_id = ?
                    GROUP BY participant_id, wave_number";
            $data = [$heat_id];
            $wave_scores = $this->model->query_bind($sql, $data, 'array');

            // Step 2: Organize scores by participant
            $participant_scores = [];
            foreach ($wave_scores as $row) {
                $participant_scores[$row['participant_id']][] = $row['avg_wave_score'];
            }

            // Step 3: Get the sum of the top 2 waves per participant
            $final_results = [];
            foreach ($participant_scores as $participant_id => $scores) {
                rsort($scores); // Sort in descending order
                $top_2_waves = array_slice($scores, 0, 2); // Get top 2 waves
                $final_results[] = [
                    'participant_id' => $participant_id,
                    'final_score' => array_sum($top_2_waves),
                ];
            }

            // Step 4: Rank participants by final score (highest to lowest)
            usort($final_results, function ($a, $b) {
                return $b['final_score'] <=> $a['final_score'];
            });

            return $final_results;
        }

        function adjust_scores_for_outliers($heat_id) {
            // Fetch average score and standard deviation per wave
            $sql = "SELECT wave_number, AVG(score) AS avg_score, STDDEV(score) AS std_dev
                    FROM comp_judge_scores
                    WHERE heat_id = ?
                    GROUP BY wave_number";
            $data = [$heat_id];
            $wave_stats = $this->model->query_bind($sql, $data, 'array');

            $adjusted_scores = [];

            foreach ($wave_stats as $wave) {
                $wave_number = $wave['wave_number'];
                $mean = $wave['avg_score'];
                $std_dev = $wave['std_dev'];
                $threshold = $std_dev * 1.5; // Set outlier threshold (1.5x SD)

                // Fetch individual scores for this wave
                $sql = "SELECT id, participant_id, score FROM comp_judge_scores WHERE heat_id = ? AND wave_number = ?";
                $data = [$heat_id, $wave_number];
                $scores = $this->model->query_bind($sql, $data, 'array');

                foreach ($scores as $score) {
                    $score_id = $score['id'];
                    $participant_id = $score['participant_id'];
                    $raw_score = $score['score'];

                    // Adjust scores if they are outliers
                    if (abs($raw_score - $mean) > $threshold) {
                        $adjusted_score = ($raw_score + $mean) / 2; // Bring it closer to the average
                    } else {
                        $adjusted_score = $raw_score;
                    }

                    // Save adjusted scores
                    $adjusted_scores[] = [
                        'id' => $score_id,
                        'participant_id' => $participant_id,
                        'wave_number' => $wave_number,
                        'original_score' => $raw_score,
                        'adjusted_score' => round($adjusted_score, 2)
                    ];
                }
            }

            return $adjusted_scores;
        }

        function adjust_scores() {
            $heat_id = post('heat_id');
            $wave_number = post('wave_number');

            // Check if scores exist for this wave
            $sql = "SELECT COUNT(*) AS count FROM comp_judge_scores WHERE heat_id = ? AND wave_number = ?";
            $score_count = $this->model->query_bind($sql, [$heat_id, $wave_number], 'single')->count;

            $this->_adjust_and_save_scores($heat_id, $wave_number);
            redirect('admin/view_scores');
        }

        function _adjust_and_save_scores($heat_id, $wave_number) {
            // Get average and standard deviation
            $sql = "SELECT participant_id, AVG(score) AS avg_score, STDDEV(score) AS std_dev 
                    FROM comp_judge_scores WHERE heat_id = ? AND wave_number = ? GROUP BY participant_id";
            $wave_stats = $this->model->query_bind($sql, [$heat_id, $wave_number], 'array');

            foreach ($wave_stats as $wave) {
                $participant_id = $wave['participant_id'];
                $mean = $wave['avg_score'];
                $std_dev = $wave['std_dev'];
                $threshold = $std_dev * 1.5;

                // Fetch scores for adjustment
                $sql = "SELECT id, score FROM comp_judge_scores WHERE heat_id = ? AND wave_number = ? AND participant_id = ?";
                $scores = $this->model->query_bind($sql, [$heat_id, $wave_number, $participant_id], 'array');

                $adjusted_scores = [];
                foreach ($scores as $score) {
                    $score_id = $score['id'];
                    $raw_score = $score['score'];

                    // Adjust outliers
                    if (abs($raw_score - $mean) > $threshold) {
                        $adjusted_score = ($raw_score + $mean) / 2;
                    } else {
                        $adjusted_score = $raw_score;
                    }

                    // Update in `comp_judge_scores`
                    $update_sql = "UPDATE comp_judge_scores SET score = ? WHERE id = ?";
                    $this->model->query_bind($update_sql, [round($adjusted_score, 2), $score_id]);

                    $adjusted_scores[] = $adjusted_score;
                }

                // Compute final average
                $final_avg = round(array_sum($adjusted_scores) / count($adjusted_scores), 2);

                // Save to `comp_wave_averages`
                $insert_sql = "INSERT INTO comp_wave_averages (heat_id, wave_number, participant_id, avg_score)
                            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE avg_score = ?";
                $this->model->query_bind($insert_sql, [$heat_id, $wave_number, $participant_id, $final_avg, $final_avg]);
            }
        }

//----------------------- RESULTS END ---------------------------
//---------------------------------------------------------------

//---------------------------------------------------------------
//-------------------- EDITING JUDGE SCORES ---------------------
        // show statistic scores for all running finished heats
        function all_scores() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $judge_id = $this->_get_judge_id();

            // Get all available heats for dropdown
            $sql = "SELECT ch.id, division, round, heat_number, ch.status, cn.name 
                    FROM comp_heats ch 
                    JOIN comp_name cn ON ch.comp_id = cn.id
                    JOIN competition_judges cj ON cj.competition_id = ch.comp_id
                    WHERE cn.status = 'running' AND cj.judge_id = ? 
                    AND cj.role = 'head_judge' 
                    AND (ch.status = 'running' OR ch.status = 'finished') 
                    ORDER BY division ASC, round ASC, heat_number ASC;";
            $all_heats = $this->model->query_bind($sql, [$judge_id], 'array');
            $data['all_heats'] = $all_heats;
            $heat_id = (int) segment(3);

            if (isset($heat_id)) {

                // Get all scores for this heat
                $sql = "SELECT 
                            t.participant_id,
                            t.jersey_color,
                            t.wave_number,

                            /* Judge scores */
                            COALESCE(MAX(CASE WHEN t.rn = 1 THEN t.score END), 0) AS J1,
                            COALESCE(MAX(CASE WHEN t.rn = 2 THEN t.score END), 0) AS J2,
                            COALESCE(MAX(CASE WHEN t.rn = 3 THEN t.score END), 0) AS J3,
                            COALESCE(MAX(CASE WHEN t.rn = 4 THEN t.score END), 0) AS J4,
                            COALESCE(MAX(CASE WHEN t.rn = 5 THEN t.score END), 0) AS J5,

                            /* Judge score IDs */
                            MAX(CASE WHEN t.rn = 1 THEN t.score_id END) AS J1_id,
                            MAX(CASE WHEN t.rn = 2 THEN t.score_id END) AS J2_id,
                            MAX(CASE WHEN t.rn = 3 THEN t.score_id END) AS J3_id,
                            MAX(CASE WHEN t.rn = 4 THEN t.score_id END) AS J4_id,
                            MAX(CASE WHEN t.rn = 5 THEN t.score_id END) AS J5_id,

                            ROUND(AVG(t.score), 2)                AS avg_score,
                            ROUND(MAX(t.score) - MIN(t.score), 2) AS spread

                        FROM (
                            SELECT
                                s.id AS score_id,
                                s.participant_id,
                                h.jersey_color,
                                s.wave_number,
                                s.score,
                                ROW_NUMBER() OVER (
                                    PARTITION BY s.participant_id, s.wave_number
                                    ORDER BY s.judge_id
                                ) AS rn
                            FROM comp_judge_scores s
                            JOIN comp_heat_participants h
                                ON h.participant_id = s.participant_id
                            AND h.heat_id = s.heat_id
                            WHERE s.heat_id = ?
                        ) AS t
                        GROUP BY
                            t.participant_id,
                            t.jersey_color,
                            t.wave_number
                        ORDER BY
                            t.participant_id,
                            t.wave_number;";

                $scores = $this->model->query_bind($sql, [$heat_id], 'array');

                $data['heat_id'] = $heat_id;
                $data['scores'] = $scores;

            }

            $data['view_file'] = 'all_scores';
            $this->template('judges_area', $data);

        }

        function edit_scores(){
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $judge_id = $this->_get_judge_id();

            // Get all available heats for dropdown
            $sql = "SELECT ch.id, division, round, heat_number, ch.status, cn.name 
                    FROM comp_heats ch 
                    JOIN comp_name cn ON ch.comp_id = cn.id
                    JOIN competition_judges cj ON cj.judge_id = ? 
                    WHERE cn.status IN ('running', 'generated')
                    AND cj.role = 'head_judge'
                    AND (ch.status = 'running' OR ch.status = 'finished') 
                    ORDER BY division ASC, round ASC, heat_number ASC;";
            $all_heats = $this->model->query_bind($sql, [$judge_id], 'array');
            $data['all_heats'] = $all_heats;
            $heat_id = (int) segment(3);

            if (isset($heat_id)) {

                // Get all scores for this heat
                $sql = "SELECT
                            js.id                AS score_id,
                            hp.jersey_color,
                            js.wave_number,
                            j.id              AS judge_name,
                            js.score
                        FROM comp_judge_scores js
                        JOIN comp_heat_participants hp ON hp.heat_id = js.heat_id AND hp.participant_id = js.participant_id
                        LEFT JOIN comp_users u         ON u.id = js.participant_id            -- if you store names in comp_users
                        LEFT JOIN comp_participants p  ON p.user_id = js.participant_id       -- fallback if older data
                        LEFT JOIN comp_judges j        ON j.id = js.judge_id
                        WHERE js.heat_id = ?
                        ORDER BY hp.jersey_color, js.wave_number, j.name";

                $scores = $this->model->query_bind($sql, [$heat_id], 'array');

                $data['heat_id'] = $heat_id;
                $data['scores'] = $scores;

            }

            $data['view_file'] = 'edit_scores';
            $this->template('judges_area', $data);
        }

        function edit_score_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $score_id = (int) segment(3);

            // Fetch score record
            $sql = "SELECT s.id, j.name AS judge_name, s.wave_number, s.score,
                    COALESCE(
                    NULLIF(CONCAT(TRIM(p.first_name), ' ', TRIM(p.last_name)), ' '), 
                    u.name) AS participant_name
                    FROM comp_judge_scores s
                    JOIN comp_participants p ON s.participant_id = p.id
                    LEFT JOIN comp_users u ON p.user_id = u.id
                    JOIN comp_judges j ON s.judge_id = j.id
                    JOIN comp_heat_participants h ON h.participant_id = p.id AND h.heat_id = s.heat_id
                    WHERE s.id = ?";
            $score_record = $this->model->query_bind($sql, [$score_id], 'object');

            if (!$score_record) {
                echo "Score record not found.";
                return;
            }
            $data['score_record'] = $score_record[0];
            $this->view('edit_score_modal', $data);
        }

        function submit_edit_score() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $score_id = (int) segment(3);
            $this->validation->set_rules('score', 'score', 'required|numeric|greater_than[-1]|less_than[11]');
            $result = $this->validation->run(); //returns true or false
            if($result === false) {
                echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;margin-block-end: 0;">Please enter a valid score between 0 and 10.</p>';
                return;
            }
            $new_score = post('score');
            $this->model->update($score_id, ['score' => $new_score], 'comp_judge_scores');


            // Get heat_id and wave_number from the score record
            $score_record = $this->model->get_where($score_id, 'comp_judge_scores');
            if (!$score_record) {
                echo "Score record not found.";
                return;
            }
            $heat_id = $score_record->heat_id;
            $wave_number = $score_record->wave_number;
            $participant_id = $score_record->participant_id;

            $this->_calculate_and_save_average($heat_id, $wave_number, $participant_id);

            echo '<p style="padding: 5px;text-align: center;margin-block-end: 0;">Score updated to ' . $new_score . ' successfully.</p>';
            
            // Reprocess heat results to decide placement if needed
            // This requires the heats module to be loaded in this controller
            // THIS MARKS HEAT AS FINISHED!
            // $this->module('heats');
            // $this->heats->_process_heat_results($heat_id);

        }

        function edit_running_scores() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            // Fetch currently running heat
            $heat = $this->_get_running_heat();

            if (!isset($heat)) {

                echo "No running heats available.";
                $data['error'] = "No running heats for edit yet!";
                $data['view_file'] = 'heat_edit_error';
                $this->template('judges_area', $data);
                exit();
            }

            $heat_id = $heat->id;

            // Get all scores for this heat
            $sql = "SELECT s.id, s.judge_id, s.participant_id, s.wave_number, s.score, 
                        p.first_name, p.last_name, h.jersey_color 
                    FROM comp_judge_scores s
                    JOIN comp_participants p ON s.participant_id = p.id
                    JOIN comp_heat_participants h ON h.participant_id = p.id AND h.heat_id = s.heat_id
                    WHERE s.heat_id = ?
                    ORDER BY s.participant_id, s.wave_number";

            $scores = $this->model->query_bind($sql, [$heat_id], 'array');

            $data['heat_id'] = $heat_id;
            $data['all_heats'] = $all_heats;
            $data['scores'] = $scores;
            $data['view_file'] = 'edit_scores';
            $this->template('judges_area', $data);
        }

        function update_score() {
            $score_id = (int) segment(3);
            $new_score = post('score');

            if (is_numeric($new_score)) {
                $this->model->update($score_id, ['score' => $new_score], 'comp_judge_scores');
            } else {
                echo "Invalid score value.";
                return;
            }

            // Get heat_id and wave_number from the score record
            $score_record = $this->model->get_where($score_id, 'comp_judge_scores');
            if (!$score_record) {
                echo "Score record not found.";
                return;
            }
            $heat_id = $score_record->heat_id;
            $wave_number = $score_record->wave_number;
            $participant_id = $score_record->participant_id;

            $this->_calculate_and_save_average($heat_id, $wave_number, $participant_id);

            echo '<p style="padding: 5px;text-align: center;">Score updated to ' . $new_score . ' successfully.</p>';

        }  

    }
?>