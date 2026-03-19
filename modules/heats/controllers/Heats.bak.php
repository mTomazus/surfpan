<?php
    class Heats extends Trongate {

        function index() {

            $competitions = $this->model->get('id', 'comp_name');
            $data = [
                "competitions" => $competitions,
                "view_file" => "show_comps"
            ];
            $this->module('trongate_tokens');
            $user = $this->trongate_tokens->_get_user_obj();

            if ($user) {
                $this->template('users_area', $data);
            } else {
                $this->template('public', $data);
            }
        }

        function generate_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $comp_id = segment(3);
            $data['comp_id'] = $comp_id;

            $data['view_file'] = 'generate_modal';
            $this->template('judges_area', $data);
        }

        public function _generate_all_heats($comp_id) {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('organizers area');

            // Fetch competition name
            $competition = $this->model->get_one_where('id', $comp_id, 'comp_name');

            if ($competition->status != 'closed') {
                return redirect('heats/show_heats_draw/' . $comp_id);
            }

            $comp_name = $competition->name . ' ' . $competition->year;

            $sql = "SELECT d.name
                        FROM comp_competition_divisions cd
                        JOIN comp_divisions d ON cd.division_id = d.id
                        WHERE cd.competition_id = ?";

            $div = $this->model->query($sql, [$comp_id], 'array');

            $divisions = array_column($div, 'name');

            foreach ($divisions as $division) {
                // Get Confirmed participants in this competition and division
                $sql = "SELECT id FROM comp_participants WHERE status = 'confirmed' AND comp_id = :comp_id AND gender_age = :division";
                $data = [
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $participants = $this->model->query_bind($sql, $data, 'array');

                // Shuffle participants for fair random seeding
                shuffle($participants);

                // Count total participants in this division
                $total_participants = count($participants);

                // Maximum participants per heat
                if ($total_participants <= 4) {

                    echo "<h3>Started Generating heats = 4 for " . out($division);
                    $four = $this->generate_four($comp_id, $participants, $total_participants, $division);
                    echo "<h3>Heats Generated Successfully for " . out($division);

                } elseif ($total_participants <= 6) {

                    echo "<h3>Started Generating heats <= 6 for " . out($division);
                    $six = $this->generate_six($comp_id, $participants, $total_participants, $division);
                    echo "<h3>Heats Generated Successfully for " . out($division);
                
                } elseif ($total_participants <= 8) {

                    echo "<h3>Started Generating heats <= 8 for " . out($division);
                    $six = $this->generate_eight($comp_id, $participants, $total_participants, $division);
                    echo "<h3>Heats Generated Successfully for " . out($division);
                
                } elseif ($total_participants <= 9) {

                    echo "<h3>Started Generating heats <= 9 for " . out($division);
                    $nine = $this->generate_nine($comp_id, $participants, $total_participants, $division);
                    echo "<h3>Heats Generated Successfully for " . out($division);

                } elseif ($total_participants <= 12) {

                    echo "<h3>Started Generating heats <=12 for " . out($division);
                    $twelve = $this->generate_twelve($comp_id, $participants, $total_participants, $division);
                    echo "<h3>Heats Generated Successfully for " . out($division);

                } elseif ($total_participants <= 16) {

                    echo "<h3>Started Generating heats <=16 for " . out($division);
                    $nine = $this->generate_sixteen($comp_id, $participants, $total_participants, $division);
                    echo "<h3>Heats Generated Successfully for " . out($division);

                } 
                
            }

            echo "<h2>Heats Generated Successfully for " . out($comp_name);
            $gen_data['status'] = 'generated';
            $this->model->update($comp_id, $gen_data, 'comp_name'); 
            redirect('heats/show_heats_draw/' . $comp_id);
        }

        function heat_generation_page() {
            $this->module('trongate_security');
            $this->module('competitions');
            $this->trongate_security->_make_sure_allowed('judges area');

            $org_id = $this->competitions->_get_organizer_id();

            $sql = "SELECT
                        c.id,
                        c.name,
                        c.status,
                        c.year,
                        COALESCE(tp.total_participants, 0) AS total_participants,
                        COALESCE(divs.divisions, JSON_ARRAY()) AS divisions
            FROM comp_name c
            LEFT JOIN (
            SELECT p.comp_id, COUNT(*) AS total_participants
            FROM comp_participants p
            WHERE p.status = 'confirmed'
            GROUP BY p.comp_id
            ) tp
            ON tp.comp_id = c.id
            LEFT JOIN (
            /* Pre-aggregate per division, then JSONify */
            SELECT
                x.competition_id,
                JSON_ARRAYAGG(
                JSON_OBJECT(
                    'id',        x.division_id,
                    'name',      x.division_name,
                    'confirmed', x.confirmed,
                    'total',     x.total,
                    'elimination', x.elimination_format
                )
                ORDER BY x.division_name
                ) AS divisions
            FROM (
                SELECT
                ccd.elimination_format AS elimination_format,
                ccd.competition_id,
                d.id   AS division_id,
                d.name AS division_name,
                SUM(CASE WHEN p.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                COUNT(p.id) AS total
                FROM comp_competition_divisions ccd
                JOIN comp_divisions d
                ON d.id = ccd.division_id
                LEFT JOIN comp_participants p
                ON p.comp_id     = ccd.competition_id
                AND p.division_id = ccd.division_id
                GROUP BY ccd.competition_id, d.id, d.name
            ) AS x
            GROUP BY x.competition_id
            ) divs
            ON divs.competition_id = c.id
            WHERE c.organizer_id = ?
            AND c.status IN ('open','running','generated','closed')
            ORDER BY c.year DESC, c.name ASC;";

            $rows = $this->model->query_bind($sql, [$org_id], 'array');

            foreach ($rows as &$row) {
               $row['divisions'] = json_decode($row['divisions'], true);
            }
            unset($row);

            $data['competitions'] = $rows;

            if (from_trongate_mx()) {
                $this->view('heats_generate', $data);
            } else {
                $data['view_file'] = 'heats_generate';
                $this->template('judges_area', $data);
            }
        }

        public function heats_for_double(int $n): int {
            // Your mapping (easy to edit later)
            $rules = [
                6  => 4,
                8  => 6,
                12 => 8,
                16 => 11,
            ];

            foreach ($rules as $max => $heats) {
                if ($n <= $max) return $heats;
            }

            // fallback for bigger fields (pick whatever logic you prefer)
            return (int)ceil($n / 2);
        }

        public function heats_for_single(int $n, int $heat_size = 4, int $advance = 2): int {
            // Surf-style: heats of 4, top 2 advance; repeat until final (<=4)
            if ($n <= $heat_size) return 1;

            $total = 0;
            while ($n > $heat_size) {
                $heats = (int)ceil($n / $heat_size);
                $total += $heats;
                $n = $heats * $advance; // advancers become next round field size
            }

            return $total + 1; // final
        }

        public function heats_for_round_robin(int $n, int $heat_size = 4, int $rounds_per_pool = 2): int {
            // Simple + practical: split into pools, run X “rounds” per pool
            // Example: rounds_per_pool=2 => everyone surfs twice in their pool
            $pools = (int)ceil($n / $heat_size);
            return $pools * $rounds_per_pool;
        }

        public function _get_elim_plan(string $method, int $confirmed): array {
            $m = strtolower(trim($method));

            return match ($m) {
                'double' => ['Double Elimination', $this->heats_for_double($confirmed)],
                'single' => ['Single Elimination', $this->heats_for_single($confirmed)],
                'robin' => ['Round Robin', $this->heats_for_round_robin($confirmed)],
                default => ['Double Elimination', $this->heats_for_double($confirmed)],
            };

        }

        function save_division_elimination() {
            $comp_id = segment(3);
            $division_id = segment(4);

            $elimination_format = post('elimination_format');

            $sql = "SELECT id FROM comp_competition_divisions WHERE competition_id = ? and division_id = ?";
            $rows = $this->model->query_bind($sql, [$comp_id, $division_id], 'array');

            $update_data['elimination_format'] = $elimination_format;
            $this->model->update($rows[0]['id'], $update_data, 'comp_competition_divisions');

            set_flashdata("Elimination format updated to " . ucfirst($elimination_format));
            return;
        }

        // ----------- pay and generate heats --------------
        // -------------------------------------------------
        public function generate_heats() {
            $this->module('billings');
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $comp_id = (int)segment(3);

            // 1) Get number of confirmed participants - $confirmed
            $sql = "SELECT COUNT(*) AS confirmed
                    FROM comp_participants
                    WHERE comp_id = ? 
                    AND status = 'confirmed'";
            $row = $this->model->query_bind($sql, [$comp_id], 'object');

            $confirmed = (int) $row[0]->confirmed;

            // 2) Compute pricing array (amount, tier ) from function in billings module
            $pricing = $this->billings->_get_comp_price($confirmed); // function from above

            // 3) Load competition row from comp_name table to find out billing_status
            $comp = $this->model->get_where($comp_id, 'comp_name');

            // Free tier → mark as paid/not required and proceed
            if ($pricing['amount'] === 0) {
                $this->_generate_all_heats($comp_id);
                if ($comp->billing_status !== 'paid') {
                    $update = [
                        'billing_status'             => 'paid',
                        'billing_tier'               => $pricing['tier'],
                        'billing_participants_locked'=> $confirmed
                    ];
                    $this->model->update($comp_id, $update, 'comp_name');
                }
                // echo "<h3>Free Tier</h3>";
                set_flashdata("Heats generated successfully.");
                return redirect("heats/heat_generation_page/");
            }

            // If in Paid tiers: check if already paid
            if ($comp->billing_status === 'paid') {
                $this->_generate_all_heats($comp_id);
                set_flashdata("Heats generated successfully.");
                return redirect("heats/heat_generation_page/");
            }

            // Not paid yet → redirect to payment
            // (create billing_charges row + redirect to EveryPay checkout)
            return redirect("billings/process_order/$comp_id");
        }

        //heat schedule page for judges to set time for each heat
        // -------------------------------------------------
        
        function heat_schedule_page() {
            $this->module('trongate_security');
            $this->module('competitions');
            $this->trongate_security->_make_sure_allowed('judges area');

            $organization = $this->competitions->_get_organizer_id();
            $judge_id = $this->competitions->_get_judge_id();

            $sql = "SELECT ch.*, CONCAT(cn.name, ' ', cn.year) AS name,
                    COUNT(*) OVER (PARTITION BY ch.comp_id) AS total_heats,
                    SUM(ch.status <> 'finished') OVER (PARTITION BY ch.comp_id) AS heats_not_finished
                    FROM comp_heats ch
                    JOIN comp_name cn
                    ON cn.id = ch.comp_id
                    WHERE ch.status IN ('pending', 'scheduled', 'finished')
                    AND EXISTS (
                        SELECT 1
                        FROM competition_judges cj
                        WHERE cj.competition_id = ch.comp_id
                        AND (cn.organizer_id = $organization OR cj.judge_id = $judge_id)
                    )
                    ORDER BY ch.sort_order, ch.id, ch.heat_number;";

            $all_heats = $this->model->query($sql, 'object');

            $comp_id = $all_heats[0]->comp_id ?? null;

            $data = [
                'comp_id' => $comp_id,
                'heats' => $all_heats,
                'timezone' => $this->competitions->_get_org_timezone(),
                "view_file" => "heat_schedule_form"
            ];
            $this->template('judges_area', $data);
        }

        function _json($data, $code = 200) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
            exit;
        }

        function update_heat_schedule() {

            // Validate time inputs
            $this->validation->set_rules('start_time', 'start_time', 'required');
            $this->validation->set_rules('heat-length', 'heat-length', 'required');
            $this->module('competitions');
            $this->module('organizations');

            $orgTz = $this->competitions->_get_org_timezone();

            $result = $this->validation->run();
            
            if ($result == true) {

                $start_time = $this->organizations->_to_utc(post('start_time'), $orgTz);
                // $start_time = post('start_time');
                $length = post('heat-length');
                $heat_id = post('heat_id');

                $end_time = date('Y-m-d H:i', strtotime($start_time . " +$length minutes"));
            
                // Prepare data for update
                $data = [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'status' => 'scheduled'
                ];
            
                // Update the heat record in the database
                $update_success = $this->model->update($heat_id, $data, 'comp_heats');
            
                return $update_success ? "Heat updated successfully!" : "Error updating heat.";
            }
            echo "<p style='color:red'>Validation error</p>";
        }

        function show_heats_draw() {
            // Get competition ID from URL
            $comp_id = segment(3) !== '' ? segment(3) : null;

            // Get the filter for showing only certain divisions
            $show_only_division = $this->_get_show_only();

            $show_only = $this->model->get_one_where('name', $show_only_division, 'comp_divisions')->id ?? '';
        
            // Fetch all heats
            $heats = $this->_get_all_heats($comp_id, $show_only);
            $comp_name = $this->_get_comp_name($comp_id);
            // organization data
            $sql = "SELECT co.organization, co.slug 
                    FROM comp_organizations co
                    JOIN comp_name cn ON co.id = cn.organizer_id
                    WHERE cn.id = ?";

            $org = $this->model->query_bind($sql, [$comp_id], 'array');

            //Fetch unique divisions
            $unique_divisions = $this->_get_unique_divisions($comp_id);

            // Fetch heat results from comp_heat_results
            $sql = "SELECT * FROM comp_heat_results WHERE heat_id IN 
            (SELECT id FROM comp_heats WHERE comp_id = ?)";
            $heat_results = $this->model->query_bind($sql, [$comp_id], 'array');

            // Organize results by heat_id and participant_id for easier access
            $formatted_results = [];
            foreach ($heat_results as $result) {
                $heat_id        = $result['heat_id'];
                $participant_id = $result['participant_id'];
                $formatted_results[$heat_id][$participant_id] = $result;
            }

            // Loop through heats and fetch participants + results
            foreach ($heats as &$heat) { // Use reference to modify array

                $heat['participants'] = $this->_get_heat_participants($heat['id']) ?? [];

                foreach ($heat['participants'] as &$participant) {
                    $participant_id = (int)$participant['participant_id'];

                    // Assign result safely, avoiding undefined index error
                    $participant['result'] = isset($formatted_results[$heat['id']][$participant_id]) 
                        ? $formatted_results[$heat['id']][$participant_id] 
                        : null;
                }
            }
  
            // Get timezone
            $this->module('competitions');
            $timezone = $this->competitions->_get_comp_timezone($comp_id);

            // Pass data to view
            $data = [
                'comp_name' => $comp_name,
                'comp_id' => $comp_id,
                'org' => $org[0],
                'heats' => $heats,
                'timezone' => $timezone,
                'view_file' => 'show_draw'
            ];

            $this->template('public', $data);
        }

        function show_heats() {
            // Get competition ID from URL
            $comp_id = segment(3) !== '' ? segment(3) : null;

            $this->module('competitions');
            $judge = $this->competitions->_get_judge_info();

            // Get the filter for showing only certain divisions
            $show_only_value = $this->_get_show_only(); // expects values like '' or '2' as division_id
            $show_only = $this->model->get_one_where('name', $show_only_value, 'comp_divisions')->id ?? '';

        
            // Fetch all heats
            $heats = $this->_get_all_heats($comp_id, $show_only);
            $comp_name = $this->_get_comp_name($comp_id);

            //Fetch unique divisions
            $unique_divisions = $this->_get_unique_divisions($comp_id);

            // Loop through heats and fetch participants
            foreach ($heats as &$heat) { // Use reference to modify array

                $heat['participants'] = $this->_get_heat_participants($heat['id']);
            
            }
            
            // Pass data to view
            $data = [
                'comp_name' => $comp_name,
                'user' => $judge,
                'heats' => $heats,
                'view_file' => 'show_heats',
            ];

            $this->template('judges_area', $data);
        }

        public function _get_comp_name($comp_id) {
            // Fetch competition name
            $competition = $this->model->get_one_where('id', $comp_id, 'comp_name');
            $comp_name = $competition->name . ' ' . $competition->year;
            return $comp_name;
        }

        public function _get_comp_data($comp_id) {
            // Fetch competition data
            $competition = $this->model->get_one_where('id', $comp_id, 'comp_name');
            return $competition;
        }

        public function _get_all_heats($comp_id, $show_only) {

            // Base SQL query
            $sql = "SELECT * FROM comp_heats WHERE comp_id = ?";

            // If $show_only is not empty, add the division filter
            $params = [$comp_id];
            
            if (!empty($show_only)) {
                $division = $this->model->get_one_where('id', $show_only, 'comp_divisions');
                $sql .= " AND division = ?";
                $params[] = $division->name;
            }
            $sql .= " ORDER BY id, heat_number";
        
            // Execute the query with bound parameters
            $heats = $this->model->query_bind($sql, $params, 'array');

            return $heats;
        }

        public function _get_heat_participants($heat_id){
            // FIXED: to fetch name from comp_users table not first_name and last_name from comp_participants table    
            
            // Build the SQL query (using placeholders).
            $sql = "SELECT
                    p.id      AS participant_id,
                    p.user_id AS user_id,
                    COALESCE(
                        NULLIF(TRIM(u.name), ''),
                        NULLIF(TRIM(CONCAT_WS(' ', NULLIF(TRIM(p.first_name), ''), NULLIF(TRIM(p.last_name), ''))), '')
                    ) AS name,
                    hp.jersey_color
                    FROM comp_heat_participants hp
                    JOIN comp_participants p ON hp.participant_id = p.id
                    LEFT JOIN comp_users u     ON p.user_id = u.id
                    WHERE hp.heat_id = $heat_id
                    ORDER BY FIELD(hp.jersey_color, 'white', 'red', 'green', 'blue')";

            // Execute the query using the unnamed parameters.
            $heat_participants = $this->model->query($sql, 'array');

            return $heat_participants;
        }

        public function _get_unique_divisions($comp_id) {

            $sql = "SELECT DISTINCT division FROM comp_heats WHERE comp_id = ?";
            $divisions = $this->model->query_bind($sql, [$comp_id], 'array');

            return array_column($divisions, 'division');
        }

//-------------------------------------------------------------
//------------------   FILTER by DIVISIONS   ------------------
//-------------------------------------------------------------

        public function _get_divisions_comp($comp_id){
            //$comp_id = segment(3) !== '' ? segment(3) : null;
            $sql = "SELECT d.id, d.name, CONCAT(d.gender, '_', d.age) AS division
                    FROM comp_competition_divisions ccd
                    JOIN comp_divisions d ON d.id = ccd.division_id
                    WHERE ccd.competition_id = ?
                    AND EXISTS (
                        SELECT 1
                        FROM comp_participants p
                        WHERE p.comp_id = ccd.competition_id
                        AND p.division_id = ccd.division_id
                    )
                    ORDER BY d.gender, d.age, d.name;";
            $divisions = $this->model->query_bind($sql, [$comp_id], 'array');

            if (!empty($divisions[1])) {
                $result = ['ALL' => ''];
            }

            foreach ($divisions as $item) {
                $result[$item['name']] = $item['division'];
                // Extract gender and age from the name
                // preg_match('/(Male|Female)\s+U(\d+)/i', $item['name'], $matches);
                // if (!empty($matches)) {
                //    $gender = strtoupper($matches[1][0]); // "F" or "M"
                //    $age    = $matches[2];               // "12", "15", etc.
                //    $key    = "U-{$age} {$gender}";
                //    $result[$key] = $item['division'];
                // }
            } 

            return $result;
        }

        public function _get_show_only() {
            $show_only_val = (string)segment(4); // PEISTI i 4 kad veiktu su www
            if ($show_only_val === '' || $show_only_val === null) {
                return '';
            }

            // Replace underscores with spaces to match division naming
            $replaced = str_replace('_', ' ', $show_only_val);

            // Capitalize gender, make age uppercase (e.g. u15 -> U15)
            $division_name = ucwords($replaced);

            // $division = $this->model->get_one_where('name', $division_name, 'comp_divisions');
            // $show_only = $division->id;
            return $division_name;
        }

//-------------------------------------------------------------
//------------------   FINAL SCORES   -------------------------
//-------------------------------------------------------------
        public function _process_heat_results($heat_id) {

            // Get sorted final scores (top 2 waves summed per participant)
            $final_scores = $this->_get_final_scores($heat_id);

            // Get division & heat info
            $heat_info = $this->model->get_where($heat_id, 'comp_heats');
            $division = $heat_info->division;
            $current_round = $heat_info->round;

            if ($heat_info->status !== 'finished') {

                // Get total participants in division by competition
                $division_size = $this->_get_division_size($heat_id);
                
                // Determine heat results (advancing, repechage, eliminated, finalists)
                $heat_results = $this->_determine_heat_results($division_size, $current_round, $final_scores);
                
                // Process advancing participants
                $this->_assign_next_round($heat_results['advancing'], $current_round, $division, $division_size);
                
                // Process repechage participants
                $this->_assign_repechage($heat_results['repechage'], $current_round, $division);
            }
            
            // Save heat results to standings
            $this->_save_heat_results($heat_id, $final_scores);
            
            // Mark heat as finished
            $this->model->update($heat_id, ['status' => 'finished'], 'comp_heats');
        }

        public function _get_division_size($heat_id) {
            $sql = "SELECT COUNT(*) AS total
                    FROM comp_participants p
                    JOIN (SELECT comp_id, division FROM comp_heats WHERE id = ?) h
                    ON p.comp_id = h.comp_id
                    AND p.gender_age = h.division";
            $division_total = $this->model->query_bind($sql, [$heat_id], 'array');
            return (int)$division_total[0]['total'];
        }
        
        // Double elimination system - advancing logic
        public function _determine_heat_results($division_size, $current_round, $final_scores) {
            $advancing = [];
            $repechage = [];
            $eliminated = [];
            $finalists = [];
        
            if ($division_size <= 4) {
                $finalists = $final_scores;

            } elseif ($division_size <= 6) {
                if ($current_round == 'Round 1') {
                    $advancing[] = $final_scores[0];
                    $repechage = array_slice($final_scores, 1, 2);
                } elseif ($current_round == 'Repechage 1') {
                    $advancing[] = array_slice($final_scores, 0, 2);
                    $eliminated = array_slice($final_scores, 2, 2);
                }

            } elseif ($division_size <= 8) {
                if ($current_round == 'Round 1') {
                    $advancing = array_slice($final_scores, 0, 2);
                    $repechage = array_slice($final_scores, 2, 2);
                } elseif ($current_round == 'Repechage 1') {
                    $repechage = array_slice($final_scores, 0, 2);
                    $eliminated = array_slice($final_scores, 2, 2);
                } elseif ($current_round == 'Round 2') {
                    $advancing = array_slice($final_scores, 0, 2);
                    $repechage = array_slice($final_scores, 2, 2);
                } elseif ($current_round == 'Repechage 2') {
                    $advancing = array_slice($final_scores, 0, 2);
                    $eliminated = array_slice($final_scores, 2, 2);
                }

            } elseif ($division_size == 9) {
                if ($current_round == 'Round 1') {
                    $advancing[] = $final_scores[0];
                    $repechage = array_slice($final_scores, 1, 2);
                } elseif ($current_round == 'Repechage 1') {
                    $repechage[] = $final_scores[0];
                    $eliminated = array_slice($final_scores, 1, 2);
                } elseif ($current_round == 'Round 2') {
                    $advancing = array_slice($final_scores, 0, 2);
                    $repechage = array_slice($final_scores, 2, 2);
                } elseif ($current_round == 'Repechage 2') {
                    $advancing = array_slice($final_scores, 0, 2);
                    $eliminated = array_slice($final_scores, 2);
                }

            } elseif ($division_size <= 12) {
                if ($current_round == 'Round 1') {
                    $advancing[] = array_slice($final_scores, 0, 2);
                    $repechage = array_slice($final_scores, 2, 2);
                } elseif ($current_round == 'Repechage 1') {
                    $repechage[] = $final_scores[0];
                    $eliminated = array_slice($final_scores, 1, 2);
                } elseif ($current_round == 'Round 2') {
                    $advancing[] = $final_scores[0];
                    $repechage = array_slice($final_scores, 1, 2);
                } elseif ($current_round == 'Repechage 2') {
                    $advancing[] = $final_scores[0];
                    $eliminated = array_slice($final_scores, 1, 2);
                }

            } elseif ($division_size <= 16) {
                if ($current_round == 'Round 1') {
                    $advancing = array_slice($final_scores, 0, 2);
                    $repechage = array_slice($final_scores, 2, 2);
                } elseif ($current_round == 'Repechage 1') {
                    $repechage[] = $final_scores[0];
                    $eliminated = array_slice($final_scores, 1);
                } elseif ($current_round == 'Round 2') {
                    $advancing[] = $final_scores[0];
                    $repechage = array_slice($final_scores, 1);
                } elseif ($current_round == 'Repechage 2') {
                    $advancing[] = $final_scores[0];
                    $eliminated = array_slice($final_scores, 1);
                }
            }
        
            return [
                'advancing' => $advancing,
                'repechage' => $repechage,
                'eliminated' => $eliminated,
                'finalists' => $finalists
            ];
        }

        public function _assign_next_round($advancing, $current_round, $division, $division_size) {
            foreach ($advancing as $participant) {

                $participant_id = $participant['participant_id'];
                if ($division_size <= 6) {
                    $next_round = 'Final';
                } else {
                    $next_round = ($current_round === 'Round 1') ? 'Round 2' : 'Final';
                }
                
                $was_current_round = $current_round;

                $this->_seed_next_round($participant_id, $was_current_round, $next_round, $division);
            }
        }
        
        public function _assign_repechage($repechage, $current_round, $division) {

            if (!empty($repechage)) {
                foreach ($repechage as $participant) {
                    $participant_id = $participant['participant_id'];
                    $next_round = ($current_round === 'Round 1') ? 'Repechage 1' : 'Repechage 2';
                    $was_current_round = $current_round;
                    $this->_seed_next_round($participant_id, $was_current_round, $next_round, $division);
                }
            }
            
        }
        
        public function _save_heat_results($heat_id, $final_scores) {
            foreach ($final_scores as $position => $data) {
                $rank = $position + 1;
                $save_data = [
                    'heat_id' => $heat_id,
                    'participant_id' => $data['participant_id'],
                    'total_score' => $data['total_score'],
                    'rank' => $rank
                ];
                $inserted = $this->model->insert($save_data, 'comp_heat_results');
                if (!$inserted) {
                    error_log("Failed to insert heat result for participant: " . $data['participant_id']);
                }
            }
        }

        function _get_final_scores($heat_id) {
            $sql = "SELECT participant_id, avg_score
                    FROM comp_wave_averages 
                    WHERE heat_id = ? 
                    ORDER BY participant_id, avg_score DESC";
            $results = $this->model->query_bind($sql, [$heat_id], 'array');

            $scores = [];
            foreach ($results as $row) {
                $id = $row['participant_id'];
                if (!isset($scores[$id])) $scores[$id] = [];
                if (count($scores[$id]) < 2) $scores[$id][] = $row['avg_score']; // Store top 2 waves
            }

            $final_scores = [];
            foreach ($scores as $id => $waves) {
                $final_scores[] = [
                    'participant_id' => $id,
                    'total_score' => array_sum($waves)  // Sum top 2 waves
                ];
            }
        
            usort($final_scores, fn($a, $b) => $b['total_score'] <=> $a['total_score']); // Sort DESC
            
            return $final_scores;
        }
        
        function _seed_next_round($participant_id, $was_current_round, $next_round, $division) {
            
            // Find all heats in next round for this division
            $sql = "SELECT id FROM comp_heats 
                    WHERE round = ? AND division = ?
                    ORDER BY id ASC";
            $data = [$next_round, $division];

            $next_heats = $this->model->query_bind($sql, $data, 'array');

            // Assign participants alternately into Heat 1 or Heat 2
            static $assignment_counter = 0;  // Tracks alternating heat assignments
            $heat_id = $next_heats[$assignment_counter % count($next_heats)]['id'];
            $assignment_counter++;
        
            // Prevent duplicate participant entries
            $check_sql = "SELECT COUNT(*) AS count FROM comp_heat_participants 
                          WHERE heat_id = ? AND participant_id = ?";
            $exists = $this->model->query_bind($check_sql, [$heat_id, $participant_id], 'array');
        
            if ($exists[0]['count'] == 0) { // Only insert if participant is not already seeded
                // Assign jersey color based on existing participants in this heat
                $colors = ['white', 'red', 'green', 'blue'];
        
                // Check which colors are already used in this heat
                $sql = "SELECT jersey_color FROM comp_heat_participants WHERE heat_id = ?";
                $used_colors = $this->model->query_bind($sql, [$heat_id], 'array');
                $used_colors = array_column($used_colors, 'jersey_color');
        
                // Get available jersey colors
                $available_colors = array_diff($colors, $used_colors);
                $jersey_color = !empty($available_colors) ? array_values($available_colors)[0] : null;

                if ($jersey_color) {
                    $data = [
                        'heat_id' => $heat_id,
                        'participant_id' => $participant_id,
                        'jersey_color' => $jersey_color,
                        'seeded_from' => $was_current_round
                    ];

                    // Insert participant into heat
                     $this->model->insert($data, 'comp_heat_participants');
                }
            }
        }        

        function calculate_final_standings($division_id) {
            
            // $this->module('database'); // strange module
        
            // Get all surfers in this division
            $sql = "SELECT DISTINCT participant_id FROM comp_heats WHERE division_id = ?";
            $participants = $this->model->query_bind($sql, [$division_id], 'array');
        
            $final_scores = [];
        
            foreach ($participants as $participant) {
                $participant_id = $participant['participant_id'];
        
                // Get top 2 wave scores from EACH heat
                $sql = "SELECT AVG(score) as avg_score FROM comp_judge_scores 
                        WHERE participant_id = ? 
                        GROUP BY heat_id, wave_number 
                        ORDER BY avg_score DESC 
                        LIMIT 4";  // 2 best waves per heat = 4 best scores total
        
                $top_scores = $this->model->query_bind($sql, [$participant_id], 'array');
        
                // Sum the best 4 scores for final ranking
                $total_score = array_sum(array_column($top_scores, 'avg_score'));
        
                $final_scores[] = [
                    'participant_id' => $participant_id,
                    'total_score' => $total_score
                ];
            }
        
            // Sort surfers by highest total_score
            usort($final_scores, function ($a, $b) {
                return $b['total_score'] <=> $a['total_score']; // Descending order
            });
        
            // Save standings in `comp_final_standings`
            foreach ($final_scores as $position => $data) {
                $rank = $position + 1; // 1st place, 2nd place, etc.
        
                $save_data = [
                    'division_id' => $division_id,
                    'participant_id' => $data['participant_id'],
                    'final_score' => $data['total_score'],
                    'rank' => $rank
                ];
        
                $this->model->insert($save_data, 'comp_final_standings');
            }
        
            return $final_scores;
        }

        function heat_scores() {
            $heat_id = segment(3) !== '' ? segment(3) : null;

            // Fetch heat info
            $heat_info = $this->model->get_where($heat_id, 'comp_heats');
            // FIXED: to fetch name from comp_users table not first_name and last_name from comp_participants table    
            // Fetch participants with first name, last name, and jersey color
            $sql = "SELECT
                    hp.participant_id,
                    hp.jersey_color,
                    COALESCE(
                        NULLIF(TRIM(u.name), ''),
                        NULLIF(TRIM(CONCAT_WS(' ',
                        NULLIF(TRIM(p.first_name), ''),
                        NULLIF(TRIM(p.last_name), '')
                        )), '')
                    ) AS name
                    FROM comp_heat_participants hp
                    JOIN comp_participants p ON hp.participant_id = p.id
                    LEFT JOIN comp_users u    ON p.user_id = u.id
                    WHERE hp.heat_id = ?
                    ORDER BY FIELD(hp.jersey_color, 'white','red','green','blue')";
            $participants = $this->model->query_bind($sql, [$heat_id], 'array');

            // Fetch wave scores
            $sql = "SELECT participant_id, wave_number, avg_score 
                    FROM comp_wave_averages 
                    WHERE heat_id = ? 
                    GROUP BY participant_id, wave_number";
            $wave_scores = $this->model->query_bind($sql, [$heat_id], 'array');

            // Organize wave scores by participant_id
            $formatted_scores = [];
            foreach ($wave_scores as $score) {
                $formatted_scores[$score['participant_id']][] = [
                    'wave_number' => $score['wave_number'],
                    'avg_score' => $score['avg_score']
                ];
            }
        
            // Attach scores to participants
            foreach ($participants as &$participant) {
                $participant_id = $participant['participant_id'];
                $participant['scores'] = $formatted_scores[$participant_id] ?? [];
            }

            // Get timezone
            $this->module('competitions');
            $timezone = $this->competitions->_get_comp_timezone($heat_info->comp_id);
            // Pass data to view
            $data = [
                'heat_info' => $heat_info,
                'participants' => $participants,
                'timezone' => $timezone
            ];
        
            $this->view('heat_scores', $data);
        }

//-------------------------------------------------------------
//------------------   HEAT GENERATION!  ----------------------
//-------------------------------------------------------------
        
    //-------------------------------------------------------------
    //------------------MAX 4 PARTICIPANTS-------------------------
    //-------------------------------------------------------------
        private function generate_four($comp_id, $participants, $total_participants, $division) {
            
            $jersey_colors = ["white", "red", "green", "blue"];
    
            for ($i = 0; $i < 2; $i++) {  // Loop for two heats
                $heat_data = [
                    'round' => 'Round 1',
                    'heat_number' => $i + 1, // Heat numbers start from 1
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $heat_id = $this->model->insert($heat_data, 'comp_heats');

                // Assign the same participants to both heats
                for ($j = 0; $j < $total_participants; $j++) {

                    $participant_id = $participants[$j]['id'];

                    $color = $jersey_colors[$j % count($jersey_colors)]; // Assign jersey color

                    // Assign participant to the heat
                    $part_data = [
                        'heat_id' => $heat_id,
                        'participant_id' => $participant_id,
                        'jersey_color' => $color
                    ];

                    $heat_part_id = $this->model->insert($part_data, 'comp_heat_participants');

                }
            }
        }
    //-------------------------------------------------------------
    //------------------MAX 6 PARTICIPANTS-------------------------
    //-------------------------------------------------------------
        private function generate_six($comp_id, $participants, $total_participants, $division) {

            $jersey_colors = ["white", "red", "green", "blue"];
            
            for ($i = 0; $i < 2; $i++) { // Loop for two heats
                $heat_data = [
                    'round' => 'Round 1',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $heat_id = $this->model->insert($heat_data, 'comp_heats');
                
                // Assign up to 3 participants to this heat
                for ($j = 0; $j < 3 && ($i * 3 + $j) < $total_participants; $j++) {
                    
                    $participant_id = $participants[$i * 3 + $j]['id'];

                    $color = $jersey_colors[$j % count($jersey_colors)];

                    // Assign participant to the heat
                    $part_data = [
                        'heat_id' => $heat_id,
                        'participant_id' => $participant_id,
                        'jersey_color' => $color
                    ];
                    $heats_part_id = $this->model->insert($part_data, 'comp_heat_participants');

                }

            }
            //insert REPECHAGE heats to heats table
            $rep_heat_data = [
                'round' => 'Repechage 1',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $rep_heat_id = $this->model->insert($rep_heat_data, 'comp_heats');
            //insert FINAL to heats table
            $final_heat_data = [
                'round' => 'Final',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $final_heat_id = $this->model->insert($final_heat_data, 'comp_heats');
        }
    //-------------------------------------------------------------
    //------------------MAX 8 PARTICIPANTS-------------------------
    //-------------------------------------------------------------
        private function generate_eight($comp_id, $participants, $total_participants, $division) {
            
            $jersey_colors = ["white", "red", "green", "blue"];

            //------ Round 1 - 2 heats ----------------
            for ($i = 0; $i < 2; $i++) {
                $heat_data = [
                    'round' => 'Round 1',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $heat_id = $this->model->insert($heat_data, 'comp_heats');
                
                // Assign up to max 4 participants to this heat
                for ($j = 0; $j < 4 && ($i * 4 + $j) < $total_participants; $j++) {
                    
                    $participant_id = $participants[$i * 4 + $j]['id'];

                    $color = $jersey_colors[$j % count($jersey_colors)];

                    // Assign participant to the heat
                    $part_data = [
                        'heat_id' => $heat_id,
                        'participant_id' => $participant_id,
                        'jersey_color' => $color
                    ];

                    $heats_part_id = $this->model->insert($part_data, 'comp_heat_participants');
                }
            }

            //insert REPECHAGE heats to heats table
            $rep_heat_data = [
                'round' => 'Repechage 1',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $rep_heat_id = $this->model->insert($rep_heat_data, 'comp_heats');

            //insert Round 2 to heats table
            $round_heat_data = [
                'round' => 'Round 2',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $r2_heat_id = $this->model->insert($round_heat_data, 'comp_heats');
            
            //insert Repechage 2 to heats table
            $rep2_heat_data = [
                'round' => 'Repechage 2',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $rep2_heat_id = $this->model->insert($rep2_heat_data, 'comp_heats');
           
            //insert FINAL to heats table
            $final_heat_data = [
                'round' => 'Final',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $final_heat_id = $this->model->insert($final_heat_data, 'comp_heats');
        }
    //-------------------------------------------------------------
    //------------------MAX 9 PARTICIPANTS-------------------------
    //-------------------------------------------------------------
        private function generate_nine($comp_id, $participants, $total_participants, $division) {
            
            $jersey_colors = ["white", "red", "green", "blue"];

            //------ Round 1 - 3 heats ----------------
            for ($i = 0; $i < 3; $i++) {
                $heat_data = [
                    'round' => 'Round 1',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $heat_id = $this->model->insert($heat_data, 'comp_heats');
                
                // Assign up to max 3 participants to this heat
                for ($j = 0; $j < 3 && ($i * 3 + $j) < $total_participants; $j++) {
                    
                    $participant_id = $participants[$i * 3 + $j]['id'];

                    $color = $jersey_colors[$j % count($jersey_colors)];

                    // Assign participant to the heat
                    $part_data = [
                        'heat_id' => $heat_id,
                        'participant_id' => $participant_id,
                        'jersey_color' => $color
                    ];

                    $heats_part_id = $this->model->insert($part_data, 'comp_heat_participants');
                }
            }

            //insert 2 REPECHAGE heats to heats table
            for ($i = 0; $i < 2; $i++) {
                $rep_heat_data = [
                    'round' => 'Repechage 1',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $rep_heat_id = $this->model->insert($rep_heat_data, 'comp_heats');
            }

            //insert Round 2 to heats table
            $round_heat_data = [
                'round' => 'Round 2',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $r2_heat_id = $this->model->insert($round_heat_data, 'comp_heats');
            
            //insert Repechage 2 to heats table
            $rep2_heat_data = [
                'round' => 'Repechage 2',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $rep2_heat_id = $this->model->insert($rep2_heat_data, 'comp_heats');
           
            //insert FINAL to heats table
            $final_heat_data = [
                'round' => 'Final',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $final_heat_id = $this->model->insert($final_heat_data, 'comp_heats');
        }
    //-------------------------------------------------------------
    //------------------MAX 12 PARTICIPANTS------------------------
    //-------------------------------------------------------------
        private function generate_twelve($comp_id, $participants, $total_participants, $division) {
            
            $jersey_colors = ["white", "red", "green", "blue"];

            //------ Round 1 - 3 heats ----------------
            for ($i = 0; $i < 3; $i++) {
                $heat_data = [
                    'round' => 'Round 1',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $heat_id = $this->model->insert($heat_data, 'comp_heats');
                
                // Assign up to max 4 participants to this heat
                for ($j = 0; $j < 4 && ($i * 4 + $j) < $total_participants; $j++) {
                    
                    $participant_id = $participants[$i * 4 + $j]['id'];

                    $color = $jersey_colors[$j % count($jersey_colors)];

                    // Assign participant to the heat
                    $part_data = [
                        'heat_id' => $heat_id,
                        'participant_id' => $participant_id,
                        'jersey_color' => $color
                    ];

                    $heats_part_id = $this->model->insert($part_data, 'comp_heat_participants');
                }
            }

            //insert REPECHAGE heats to heats table
            for ($i = 0; $i < 2; $i++) {
                $rep_heat_data = [
                    'round' => 'Repechage 1',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $rep_heat_id = $this->model->insert($rep_heat_data, 'comp_heats');
            }

            //insert Round 2 to heats table
            $round_heat_data = [
                'round' => 'Round 2',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $r2_heat_id = $this->model->insert($round_heat_data, 'comp_heats');
            
            //insert Repechage 2 to heats table
            $rep2_heat_data = [
                'round' => 'Repechage 2',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $rep2_heat_id = $this->model->insert($rep2_heat_data, 'comp_heats');
           
            //insert FINAL to heats table
            $final_heat_data = [
                'round' => 'Final',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $final_heat_id = $this->model->insert($final_heat_data, 'comp_heats');
        }
    //-------------------------------------------------------------
    //------------------MAX 16 PARTICIPANTS------------------------
    //-------------------------------------------------------------
        private function generate_sixteen($comp_id, $participants, $total_participants, $division) {
            
            $jersey_colors = ["white", "red", "green", "blue"];
            
            //------ Round 1 - 4 heats ----------------
            // Calculate number of heats (max 4 per heat, but spread evenly)
            $num_heats = 4;
            $heats = [];
            for ($i = 0; $i < $num_heats; $i++) {
                $heat_data = [
                    'round' => 'Round 1',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $heats[] = $this->model->insert($heat_data, 'comp_heats');
            }

            // Distribute participants round-robin
            foreach ($participants as $idx => $participant) {
                $heat_idx = $idx % $num_heats;
                $color = $jersey_colors[($idx / $num_heats) % count($jersey_colors)];
                $part_data = [
                    'heat_id' => $heats[$heat_idx],
                    'participant_id' => $participant['id'],
                    'jersey_color' => $color
                ];
                $this->model->insert($part_data, 'comp_heat_participants');
            }

            //insert REPECHAGE two heats to heats table
            for ($i = 0; $i < 2; $i++) {
                $rep_heat_data = [
                    'round' => 'Repechage 1',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $rep_heat_id = $this->model->insert($rep_heat_data, 'comp_heats');
            }

            //insert ROUND 2 two heats to heats table
            for ($i = 0; $i < 2; $i++) {
                $round_heat_data = [
                    'round' => 'Round 2',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $r2_heat_id = $this->model->insert($round_heat_data, 'comp_heats');
            }

            //insert REPECHAGE 2 two heats to heats table
            for ($i = 0; $i < 2; $i++) {
                $rep2_heat_data = [
                    'round' => 'Repechage 2',
                    'heat_number' => $i + 1,
                    'comp_id' => $comp_id,
                    'division' => $division
                ];
                $rep2_heat_id = $this->model->insert($rep2_heat_data, 'comp_heats');
            }

            //insert FINAL to heats table
            $final_heat_data = [
                'round' => 'Final',
                'heat_number' => 1,
                'comp_id' => $comp_id,
                'division' => $division
            ];
            $final_heat_id = $this->model->insert($final_heat_data, 'comp_heats');
        }

    }
?>