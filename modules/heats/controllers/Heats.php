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

            $this->template('public', $data);

        }

        function generate_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');

            $comp_id = segment(3);
            $data['comp_id'] = $comp_id;

            $data['view_file'] = 'generate_modal';
            $this->template('judges_area', $data);
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

        function delete_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $comp_id = (int)segment(3);

            // Prevent deletion if competition is not in a generated state
            $competition = $this->model->get_where($comp_id, 'comp_name');

             // Prevent deletion if competition is not in a generated state
            if ($competition->status !== 'generated') {
                show_error('Cannot delete heats for a competition that is not in a generated state.');
            }
            $data['name'] = $competition->name . ' ' . $competition->year;
            $data['update_id'] = $comp_id;
            $this->view('wipe_generation_modal', $data);
        }

        function regenerate_modal() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('judges area');
            $comp_id = (int)segment(3);

            // Prevent regeneration if competition is not in a generated state
            $competition = $this->model->get_where($comp_id, 'comp_name');

             // Prevent regeneration if competition is not in a generated state
            if ($competition->status !== 'generated') {
                show_error('Cannot regenerate heats for a competition that is not in a generated state.');
            }
            $data['name'] = $competition->name . ' ' . $competition->year;
            $data['update_id'] = $comp_id;
            $this->view('re_generation_modal', $data);
        }

        public function heats_for_double(int $n): int {
            $rules = [
                4  => 2,   // R1(1) + Final(1)
                6  => 4,   // R1(2) + Rep(1) + Final(1)
                8  => 6,   // R1(2) + Rep1(1) + R2(1) + Rep(1) + Final(1)
                9  => 8,   // R1(3) + Rep1(1) + R2(2) + Rep(1) + Final(1)
                12 => 9,   // R1(3) + Rep1(2) + R2(2) + Rep(1) + Final(1)
                16 => 11,  // R1(4) + Rep1(2) + R2(2) + Semi(1) + Rep(1) + Final(1)
                20 => 17,  // R1(5) + Rep1(3) + R2(3) + Semi(2) + Rep2(2) + Rep(1) + Final(1)
                24 => 18,  // R1(6) + Rep1(3) + R2(3) + Semi(2) + Rep2(2) + Rep(1) + Final(1)
                36 => 33,  // R1(9) + Rep1(6) + R2(6) + QF(3) + Semi(2) + Rep2(3) + Rep3(2) + Rep(1) + Final(1)
            ];

            foreach ($rules as $max => $heats) {
                if ($n <= $max) return $heats;
            }

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

        public function heats_for_second_chance(int $n, int $heat_size = 4, int $rounds_per_pool = 2): int {
            $rules = [
                4  => 3,   // R1(1) + Rep(1) + Final(1)
                6  => 4,   // R1(2) + Rep(1) + Final(1)
                8  => 6,   // R1(2) + Rep(1) + R2(2) + Final(1)
                12 => 8,   // R1(3) + Rep(2) + R2(2) + Final(1)
                16 => 12,  // R1(4) + Rep(2) + R2(3) + Semi(2) + Final(1)
                24 => 17,  // R1(6) + Rep(4) + R2(4) + Semi(2) + Final(1)
                36 => 27,  // R1(9) + Rep(6) + R2(6) + QF(3) + Semi(2) + Final(1)
            ];

            foreach ($rules as $max => $heats) {
                if ($n <= $max) return $heats;
            }

            // Fallback for sizes beyond 36
            return (int)ceil($n / 2);
        }

        public function _get_elim_plan(string $method, int $confirmed): array {
            $m = strtolower(trim($method));

            return match ($m) {
                'double' => ['Double Elimination', $this->heats_for_double($confirmed)],
                'second_chance' => ['Second Chance', $this->heats_for_second_chance($confirmed)],
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

            // Check for admin-granted event pass credits before going to payment
            $org_id = $comp->organizer_id;

            $sql = "SELECT bc.id
                    FROM billing_charges bc
                    LEFT JOIN billing_pass_uses bpu ON bpu.charge_id = bc.id
                    WHERE bc.organizer_id = :oid
                      AND bc.product_id = 6
                      AND bc.status = 'paid'
                      AND bpu.id IS NULL
                    ORDER BY bc.id ASC LIMIT 1";
            $credit = $this->model->query_bind($sql, ['oid' => $org_id], 'object');

            if (is_array($credit) && !empty($credit)) {
                // Consume the credit
                $this->model->insert([
                    'organization_id' => $org_id,
                    'charge_id'       => (int)$credit[0]->id,
                    'competition_id'  => $comp_id,
                ], 'billing_pass_uses');

                // Mark comp as paid using the credit
                $this->model->update($comp_id, [
                    'billing_charge_id' => (int)$credit[0]->id,
                    'billing_status'              => 'paid',
                    'billing_tier'                => 'event_pass',
                    'billing_participants_locked' => $confirmed,
                ], 'comp_name');

                $this->_generate_all_heats($comp_id);
                set_flashdata("Heats generated successfully using an Event Pass credit.");
                return redirect("heats/heat_generation_page/");
            }

            // No credits → redirect to payment (EveryPay)
            return redirect("billings/process_order/$comp_id");
        }

        // ------------------------------------------------------------
        // HEAT schedule page for judges to set time for each heat
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
                    WHERE ch.status IN ('pending', 'scheduled', 'running','finished')
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

        // ------------------------------------------------------------
        // Show heats at judges panel , simplistic version without results, for scheduling and drawing
        // ------------------------------------------------------------
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

        // ------------------------------------------------------------
        //------------------   SHOW HEATS + DRAW   ----------------------
        // ------------------------------------------------------------
        public function show_heats_draw() {

            $comp_id = segment(3) !== '' ? (int)segment(3) : null;

            if (!$comp_id) {
                return redirect('competitions');
            }

            // --- Filter by division (optional) ---
            $show_only_division = $this->_get_show_only();
            $show_only_id = null;
            if (!empty($show_only_division)) {
                $div = $this->model->get_one_where('name', $show_only_division, 'comp_divisions');
                $show_only_id = $div ? (int)$div->id : null;
            }

            // --- Comp name & org in one query ---
            $sql = "SELECT cn.name, cn.year, co.organization, co.slug
                    FROM comp_name cn
                    JOIN comp_organizations co ON co.id = cn.organizer_id
                    WHERE cn.id = :comp_id";
            $comp_info = $this->model->query_bind($sql, ['comp_id' => $comp_id], 'array');

            if (empty($comp_info)) {
                return redirect('competitions');
            }

            // --- Fetch all heats ---
            $heats = $this->_get_all_heats($comp_id, $show_only_id);
            $heat_ids = array_column($heats, 'id');

            if (empty($heat_ids)) {
                $data = [
                    'comp_name' => $comp_info[0]['name'] . ' ' . $comp_info[0]['year'],
                    'comp_id'   => $comp_id,
                    'org'       => $comp_info[0],
                    'heats'     => [],
                    'view_file' => 'show_comps'
                ];
                return $this->template('public', $data);
            }

            $placeholders = implode(',', array_fill(0, count($heat_ids), '?'));

            // --- Fetch ALL participants + their results in one query ---
            $sql = "SELECT
                        hp.heat_id,
                        hp.participant_id,
                        hp.jersey_color,
                        hp.seeded_from,
                        COALESCE(
                            NULLIF(TRIM(u.name), ''),
                            NULLIF(TRIM(CONCAT_WS(' ',
                                NULLIF(TRIM(p.first_name), ''),
                                NULLIF(TRIM(p.last_name), '')
                            )), '')
                        ) AS name,
                        hr.rank,
                        hr.total_score
                    FROM comp_heat_participants hp
                    JOIN comp_participants p ON hp.participant_id = p.id
                    LEFT JOIN comp_users u   ON p.user_id = u.id
                    LEFT JOIN comp_heat_results hr
                        ON hr.heat_id = hp.heat_id
                        AND hr.participant_id = hp.participant_id
                    WHERE hp.heat_id IN ({$placeholders})
                    ORDER BY hp.heat_id, FIELD(hp.jersey_color,'white','red','green','blue')";

            $all_participants = $this->model->query_bind($sql, $heat_ids, 'array');

            // --- Fetch advancement routes for all heats ---
            // Each row tells us: from this heat, position X → goes to heat Y (with round name)
            $sql = "SELECT
                        cha.from_heat_id,
                        cha.finish_position,
                        cha.to_heat_id,
                        ch.round  AS to_round,
                        ch.heat_number AS to_heat_number
                    FROM comp_heat_advancement cha
                    LEFT JOIN comp_heats ch ON ch.id = cha.to_heat_id
                    WHERE cha.from_heat_id IN ({$placeholders})
                    ORDER BY cha.from_heat_id, cha.finish_position ASC";

            $all_routes = $this->model->query_bind($sql, $heat_ids, 'array');

            // --- Group participants by heat_id ---
            $participants_by_heat = [];
            foreach ($all_participants as $p) {
                $participants_by_heat[$p['heat_id']][] = $p;
            }

            // --- Group advancement routes by heat_id, keyed by finish_position ---
            // e.g. $routes_by_heat[42][1] = ['to_round' => 'Round 2', 'to_heat_number' => 1]
            //      $routes_by_heat[42][3] = ['to_round' => 'Repechage 1', ...]
            //      $routes_by_heat[42][4] = ['to_round' => null]  ← eliminated
            $routes_by_heat = [];
            foreach ($all_routes as $route) {
                $routes_by_heat[$route['from_heat_id']][$route['finish_position']] = [
                    'to_heat_id'     => $route['to_heat_id'],
                    'to_round'       => $route['to_round'],
                    'to_heat_number' => $route['to_heat_number'],
                ];
            }

            // Fetch incoming sources for unseeded heats
            $sql = "SELECT
                        cha.to_heat_id,
                        cha.from_heat_id,    
                        cha.finish_position AS from_position,
                        ch.round            AS from_round,
                        ch.heat_number      AS from_heat_number
                    FROM comp_heat_advancement cha
                    JOIN comp_heats ch ON ch.id = cha.from_heat_id
                    WHERE cha.to_heat_id IN ({$placeholders})
                    ORDER BY cha.to_heat_id, ch.round, ch.heat_number, cha.finish_position ASC";

            $all_sources = $this->model->query_bind($sql, $heat_ids, 'array');

            $sources_by_heat = [];
            foreach ($all_sources as $src) {
                $sources_by_heat[$src['to_heat_id']][] = [
                    'from_heat_id'     => $src['from_heat_id'],
                    'from_round'       => $src['from_round'],
                    'from_heat_number' => $src['from_heat_number'],
                    'from_position'    => $src['from_position'],
                ];
            }

            // --- Attach participants and routes to each heat ---
            foreach ($heats as &$heat) {
                $heat['participants'] = $participants_by_heat[$heat['id']] ?? [];
                $heat['routes']       = $routes_by_heat[$heat['id']]       ?? [];
                $heat['sources']      = $sources_by_heat[$heat['id']]      ?? []; // ← add this
            }
            unset($heat);

            // --- Timezone ---
            $this->module('competitions');
            $timezone = $this->competitions->_get_comp_timezone($comp_id);

            // --- Session messages ---
            $errors = $_SESSION['heat_generation_errors'] ?? [];
            unset($_SESSION['heat_generation_errors']);

            $divisions = $this->_get_divisions_comp($comp_id);

            $data = [
                'comp_name'        => $comp_info[0]['name'] . ' ' . $comp_info[0]['year'],
                'comp_id'          => $comp_id,
                'org'              => $comp_info[0],
                'heats'            => $heats,
                'unique_divisions' => $this->_get_unique_divisions($comp_id),
                'divisions'        => $divisions,
                'timezone'         => $timezone,
                'errors'           => $errors,
                'view_file'        => 'show_draw',
            ];

            $this->template('public', $data);
        }
   
        function old_show_heats_draw() {
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

        // -------------------------------------------------------
        // Helper: render heat status badge + time
        // -------------------------------------------------------
        public function render_heat_status(array $heat, string $timezone): string {
            $div = '<p class="heat-division">' . out($heat['division']) . '</p>';

            switch ($heat['status']) {
                case 'finished':
                    return $div . '<p class="status status--ended">ended</p>';

                case 'pending':
                    return $div . '<p class="status status--soon">soon</p>';

                case 'scheduled':
                    $dt = new DateTimeImmutable($heat['start_time'], new DateTimeZone('UTC'));
                    $time = $dt->setTimezone(new DateTimeZone($timezone))->format('H:i');
                    return $div . '<p class="status status--scheduled">' . $time . '</p>';

                case 'running':
                    $end     = new DateTimeImmutable($heat['end_time'],  new DateTimeZone('UTC'));
                    $now     = new DateTimeImmutable('now',              new DateTimeZone('UTC'));
                    if ($end > $now) {
                        $diff    = $now->diff($end);
                        $minutes = $diff->i + ($diff->h * 60) + ($diff->d * 1440);
                        $seconds = $diff->s;
                        $timer   = sprintf('%02d:%02d', $minutes, $seconds);
                        return $div . '<div class="status status--live"><span>live</span><span>' . $timer . '</span></div>';
                    }
                    return $div . '<p class="status status--ended">ended</p>';

                default:
                    return $div;
            }
        }

        // -------------------------------------------------------
        // Helper: render advancement destination label
        // -------------------------------------------------------
        public function render_destination(?array $route): string {
            if (!$route || !$route['to_round']) {
                return '<span class="dest dest--out">Out</span>';
            }
            $label = $route['to_round'] . ' H' . $route['to_heat_number'];
            return '<span class="dest dest--adv">' . out($label) . '</span>';
        }

        // -------------------------------------------------------
        // Helper: abbreviate round name for compact display
        // e.g. "Round 1" → "R1", "Repechage 1" → "Rep1", "Final" → "Final"
        // -------------------------------------------------------
        function abbr_round(string $round): string {
            return preg_replace(
                ['/Round\s+/i', '/Repechage\s+/i', '/Quarter Final/i', '/Semi Final/i'],
                ['R',           'Rep',              'QF',               'SF'],
                $round
            );
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

        public function _get_division_size($heat_id) {
            $sql = "SELECT COUNT(*) AS total
                    FROM comp_participants p
                    JOIN (SELECT comp_id, division FROM comp_heats WHERE id = ?) h
                    ON p.comp_id = h.comp_id
                    AND p.gender_age = h.division";
            $division_total = $this->model->query_bind($sql, [$heat_id], 'array');
            return (int)$division_total[0]['total'];
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

    // ------------------------------------------------------------
    //----------- DOUBLE ELIMINATION BRACKET (UP TO 36) -----------
    //-------------------------------------------------------------   
        private function _generate_double_elimination_bracket($comp_id, $participants, $total_participants, $division) {

            $jersey_colors = ['white', 'red', 'green', 'blue'];

            // -------------------------------------------------------
            // Config per participant count:
            //   w_rounds  — Winners bracket rounds in sequence
            //   r_rounds  — Repechage bracket rounds in sequence
            //                 (last r_round is always 'Rep Final')
            //   Each round: ['name', 'heats', 'advances']
            //
            // Rules:
            //   - R1 losers (positions > advances) → Rep bracket
            //   - All other W round losers         → eliminated
            //   - Last W round advances            → Final
            //   - Last Rep round advances          → Final
            //   - Final always has 4 athletes (2W + 2R)
            //
            // Exception ≤4: no Rep bracket, all 4 play R1 then all
            //               advance to Final (compete twice)
            // -------------------------------------------------------
            $configs = [
                4 => [
                    'w_rounds' => [
                        ['name' => 'Round 1', 'heats' => 1, 'advances' => 4],
                    ],
                    'r_rounds' => [],
                ],
                6 => [
                    'w_rounds' => [
                        ['name' => 'Round 1', 'heats' => 2, 'advances' => 1], // 3/heat, 1st only → Final
                    ],
                    'r_rounds' => [
                        ['name' => 'Repechage', 'heats' => 1, 'advances' => 2],
                    ],
                ],
                8 => [
                    'w_rounds' => [
                        ['name' => 'Round 1', 'heats' => 2, 'advances' => 2],
                        ['name' => 'Round 2', 'heats' => 1, 'advances' => 2],
                    ],
                    'r_rounds' => [
                        ['name' => 'Repechage 1', 'heats' => 1, 'advances' => 2],
                        ['name' => 'Repechage',   'heats' => 1, 'advances' => 2],
                    ],
                ],
                9 => [
                    'w_rounds' => [
                        ['name' => 'Round 1', 'heats' => 3, 'advances' => 2],
                        ['name' => 'Round 2', 'heats' => 2, 'advances' => 1], // 3/heat, 1st only → Final
                    ],
                    'r_rounds' => [
                        ['name' => 'Repechage 1', 'heats' => 1, 'advances' => 2], // 3 losers from R1
                        ['name' => 'Repechage',   'heats' => 2, 'advances' => 1],
                    ],
                ],
                12 => [
                    'w_rounds' => [
                        ['name' => 'Round 1', 'heats' => 4, 'advances' => 1], // 3/heat, 1st only → R2
                        ['name' => 'Round 2', 'heats' => 1, 'advances' => 2], // 1st only → Final
                    ],
                    'r_rounds' => [
                        ['name' => 'Repechage 1', 'heats' => 2, 'advances' => 2], // 4/heat from R1
                        ['name' => 'Repechage', 'heats' => 2, 'advances' => 1], // 3/heat from Repechage 1 + R2 losers
                    ],
                ],
                16 => [
                    'w_rounds' => [
                        ['name' => 'Round 1',    'heats' => 4, 'advances' => 2],
                        ['name' => 'Round 2',    'heats' => 2, 'advances' => 2],
                        ['name' => 'Semi Final', 'heats' => 1, 'advances' => 2],
                    ],
                    'r_rounds' => [
                        ['name' => 'Repechage 1', 'heats' => 2, 'advances' => 2],
                        ['name' => 'Repechage', 'heats' => 1, 'advances' => 2],
                    ],
                ],
                20 => [
                    'w_rounds' => [
                        ['name' => 'Round 1',    'heats' => 5, 'advances' => 2],
                        ['name' => 'Round 2',    'heats' => 3, 'advances' => 2],
                        ['name' => 'Semi Final', 'heats' => 2, 'advances' => 1], // 1st only → Final
                    ],
                    'r_rounds' => [
                        ['name' => 'Repechage 1',     'heats' => 3, 'advances' => 2],
                        ['name' => 'Repechage 2',     'heats' => 2, 'advances' => 2],
                        ['name' => 'Repechage', 'heats' => 1, 'advances' => 2],
                    ],
                ],
                24 => [
                    'w_rounds' => [
                        ['name' => 'Round 1',    'heats' => 6, 'advances' => 2],
                        ['name' => 'Round 2',    'heats' => 3, 'advances' => 2],
                        ['name' => 'Semi Final', 'heats' => 2, 'advances' => 1], // 1st only → Final
                    ],
                    'r_rounds' => [
                        ['name' => 'Repechage 1',     'heats' => 3, 'advances' => 2],
                        ['name' => 'Repechage 2',     'heats' => 2, 'advances' => 2],
                        ['name' => 'Repechage', 'heats' => 1, 'advances' => 2],
                    ],
                ],
                36 => [
                    'w_rounds' => [
                        ['name' => 'Round 1',       'heats' => 9, 'advances' => 2],
                        ['name' => 'Round 2',       'heats' => 6, 'advances' => 2],
                        ['name' => 'Quarter Final', 'heats' => 3, 'advances' => 2],
                        ['name' => 'Semi Final',    'heats' => 2, 'advances' => 1], // 1st only → Final
                    ],
                    'r_rounds' => [
                        ['name' => 'Repechage 1',     'heats' => 6, 'advances' => 2],
                        ['name' => 'Repechage 2',     'heats' => 3, 'advances' => 2],
                        ['name' => 'Repechage 3',    'heats' => 2, 'advances' => 2],
                        ['name' => 'Repechage', 'heats' => 1, 'advances' => 2],
                    ],
                ],
            ];

            // Find config for this participant count
            $config = null;
            foreach ($configs as $limit => $cfg) {
                if ($total_participants <= $limit) {
                    $config = $cfg;
                    break;
                }
            }
            if ($config === null) {
                log_message('error', "No double chance config for {$total_participants} participants in {$division}");
                return false;
            }

            // -------------------------------------------------------
            // Helpers
            // -------------------------------------------------------

            // Insert N heats for a round, return array of IDs
            $insert_heats = function($round_name, $count) use ($comp_id, $division) {
                $ids = [];
                for ($i = 0; $i < $count; $i++) {
                    $ids[] = $this->model->insert([
                        'round'       => $round_name,
                        'heat_number' => $i + 1,
                        'comp_id'     => $comp_id,
                        'division'    => $division,
                    ], 'comp_heats');
                }
                return $ids;
            };

            // Insert one advancement record
            $advance = function($from_heat_id, $position, $to_heat_id) {
                $this->model->insert([
                    'from_heat_id'    => $from_heat_id,
                    'finish_position' => $position,
                    'to_heat_id'      => $to_heat_id, // null = eliminated
                ], 'comp_heat_advancement');
            };

            // Cross-seed destination: spreads top finishers from different
            // heats into different next-round heats to avoid rematches.
            // e.g. heat 0 pos 1 → dest 0, heat 1 pos 1 → dest 1,
            //      heat 0 pos 2 → dest 1, heat 1 pos 2 → dest 0 (crossed)
            $cross_dest = function($heat_idx, $pos, $dest_ids) {
                $n = count($dest_ids);
                return $n > 0 ? $dest_ids[($heat_idx + $pos - 1) % $n] : null;
            };

            // -------------------------------------------------------
            // 1. Insert all heats upfront, interleaved by round index
            // so DB order reflects bracket flow:
            // R1, Rep1, R2, Rep2, ..., Final
            // rather than all W rounds then all R rounds
            // -------------------------------------------------------
            $w_ids = [];
            $r_ids = [];

            $max_rounds = max(count($config['w_rounds']), count($config['r_rounds']));

            for ($i = 0; $i < $max_rounds; $i++) {
                if (isset($config['w_rounds'][$i])) {
                    $w_ids[$i] = $insert_heats($config['w_rounds'][$i]['name'], $config['w_rounds'][$i]['heats']);
                }
                if (isset($config['r_rounds'][$i])) {
                    $r_ids[$i] = $insert_heats($config['r_rounds'][$i]['name'], $config['r_rounds'][$i]['heats']);
                }
            }

            $final_id = $insert_heats('Final', 1)[0];

            // -------------------------------------------------------
            // 2. Distribute R1 participants round-robin across heats
            // -------------------------------------------------------
            $r1_heat_ids = $w_ids[0];
            $num_r1      = count($r1_heat_ids);

            foreach ($participants as $idx => $p) {
                $heat_idx = $idx % $num_r1;
                $slot     = (int)floor($idx / $num_r1);
                $this->model->insert([
                    'heat_id'        => $r1_heat_ids[$heat_idx],
                    'participant_id' => $p['id'],
                    'jersey_color'   => $jersey_colors[$slot % count($jersey_colors)],
                ], 'comp_heat_participants');
            }

            // -------------------------------------------------------
            // 3. Wire Winners Bracket
            // Each W round's losers drop to the matching r_round
            // at the same index — R1 losers → r_ids[0],
            // R2 losers → r_ids[1], etc.
            // If no matching r_round exists, losers are eliminated.
            // -------------------------------------------------------
            $w_round_count = count($config['w_rounds']);
            $per_heat_r1   = (int)ceil($total_participants / $num_r1);

            foreach ($w_ids as $r_idx => $heat_ids) {
                $advances  = $config['w_rounds'][$r_idx]['advances'];
                $is_r1     = ($r_idx === 0);
                $is_last_w = ($r_idx === $w_round_count - 1);
                $next_ids  = $is_last_w ? [$final_id] : $w_ids[$r_idx + 1];
                $per_heat  = $is_r1 ? $per_heat_r1 : 4;
                $loser_ids = $r_ids[$r_idx] ?? []; // matching rep round for this W round

                foreach ($heat_ids as $heat_idx => $heat_id) {
                    // Advancing positions → next W round (or Final if last)
                    for ($pos = 1; $pos <= $advances; $pos++) {
                        $advance($heat_id, $pos, $cross_dest($heat_idx, $pos, $next_ids));
                    }
                    // Losing positions → matching rep round, or eliminated if none
                    for ($pos = $advances + 1; $pos <= $per_heat; $pos++) {
                        $dest = !empty($loser_ids)
                            ? $cross_dest($heat_idx, $pos - $advances, $loser_ids)
                            : null;
                        $advance($heat_id, $pos, $dest);
                    }
                }
            }

            // -------------------------------------------------------
            // 4. Wire Repechage Bracket
            // -------------------------------------------------------
            $r_round_count = count($config['r_rounds']);

            foreach ($r_ids as $r_idx => $heat_ids) {
                $advances  = $config['r_rounds'][$r_idx]['advances'];
                $is_last_r = ($r_idx === $r_round_count - 1);
                $next_ids  = $is_last_r ? [$final_id] : $r_ids[$r_idx + 1];

                foreach ($heat_ids as $heat_idx => $heat_id) {
                    for ($pos = 1; $pos <= $advances; $pos++) {
                        $advance($heat_id, $pos, $cross_dest($heat_idx, $pos, $next_ids));
                    }
                    for ($pos = $advances + 1; $pos <= 4; $pos++) {
                        $advance($heat_id, $pos, null); // eliminated
                    }
                }
            }

            // -------------------------------------------------------
            // 5. Final — no advancement out
            // -------------------------------------------------------
            for ($pos = 1; $pos <= 4; $pos++) {
                $advance($final_id, $pos, null);
            }

            return true;
        }

    // ------------------------------------------------------------
    //------- SINGLE ELIMINATION BRACKET (UP TO 36) ---------------
    //-------------------------------------------------------------
        private function _generate_single_elimination_bracket($comp_id, $participants, $total_participants, $division) {

            $jersey_colors = ['white', 'red', 'green', 'blue'];

            // -------------------------------------------------------
            // Bracket shape config per participant count
            // 'r1_heats'    = number of Round 1 heats
            // 'r1_advances' = how many advance FROM each R1 heat
            // 'rounds'      = subsequent rounds before final
            //                 each entry: ['heats' => N, 'advances' => N]
            // No repechage - every loss is elimination
            // -------------------------------------------------------
            // -------------------------------------------------------
            // r1_name     = label used for the first round of heats
            // r1_heats    = number of heats in that round
            // r1_advances = positions that advance per heat
            // rounds      = subsequent rounds before the Final
            //               each: ['name', 'heats', 'advances']
            //
            // Participant flow per bracket size:
            //  ≤4  : Final only (4 ppl, 1 heat)
            //  ≤6  : R1(2×3) → Final(4)
            //  ≤8  : R1(2×4) → Final(4)
            //  ≤12 : R1(3×4) → Semi(2×3) → Final(4)
            //  ≤16 : R1(4×4) → Semi(2×4) → Final(4)
            //  ≤24 : R1(6×4) → QF(3×4)  → Semi(2×4) → Final(4)
            //  ≤32 : R1(8×4) → QF(4×4)  → Semi(2×4) → Final(4)
            //  ≤36 : R1(9×4) → R2(5×~4) → QF(3×~4) → Semi(2×3) → Final(4)
            //         18 advance → 10 advance → 6 advance → 4 in Final
            // -------------------------------------------------------
            $configs = [
                4  => [
                    'r1_name'     => 'Final',    // only round — IS the final
                    'r1_heats'    => 1,
                    'r1_advances' => 4,
                    'rounds'      => [],
                ],
                6  => [
                    'r1_name'     => 'Round 1',
                    'r1_heats'    => 2,
                    'r1_advances' => 2,
                    'rounds'      => [],
                ],
                8  => [
                    'r1_name'     => 'Round 1',
                    'r1_heats'    => 2,
                    'r1_advances' => 2,
                    'rounds'      => [],
                ],
                12 => [
                    'r1_name'     => 'Round 1',
                    'r1_heats'    => 3,
                    'r1_advances' => 2,
                    'rounds'      => [
                        ['name' => 'Semi Final', 'heats' => 2, 'advances' => 2],
                    ],
                ],
                16 => [
                    'r1_name'     => 'Round 1',
                    'r1_heats'    => 4,
                    'r1_advances' => 2,
                    'rounds'      => [
                        ['name' => 'Semi Final', 'heats' => 2, 'advances' => 2],
                    ],
                ],
                24 => [
                    'r1_name'     => 'Round 1',
                    'r1_heats'    => 6,
                    'r1_advances' => 2,
                    'rounds'      => [
                        ['name' => 'Quarter Final', 'heats' => 3, 'advances' => 2],
                        ['name' => 'Semi Final',    'heats' => 2, 'advances' => 2],
                    ],
                ],
                32 => [
                    'r1_name'     => 'Round 1',
                    'r1_heats'    => 8,
                    'r1_advances' => 2,
                    'rounds'      => [
                        ['name' => 'Quarter Final', 'heats' => 4, 'advances' => 2],
                        ['name' => 'Semi Final',    'heats' => 2, 'advances' => 2],
                    ],
                ],
                36 => [
                    'r1_name'     => 'Round 1',
                    'r1_heats'    => 9,
                    'r1_advances' => 2,
                    'rounds'      => [
                        ['name' => 'Round 2',       'heats' => 5, 'advances' => 2],  // 18 ppl → 5 heats (~3-4 each)
                        ['name' => 'Quarter Final', 'heats' => 3, 'advances' => 2],  // 10 ppl → 3 heats (~3-4 each)
                        ['name' => 'Semi Final',    'heats' => 2, 'advances' => 2],  //  6 ppl → 2 heats of 3
                    ],
                ],
            ];

            // Find the right config for this participant count
            $config = null;
            foreach ($configs as $limit => $cfg) {
                if ($total_participants <= $limit) {
                    $config = $cfg;
                    break;
                }
            }

            if ($config === null) {
                log_message('error', "No single elimination config for {$total_participants} participants in {$division}");
                return false;
            }

            // -------------------------------------------------------
            // Helper: insert N heats for a round, return their IDs
            // -------------------------------------------------------
            $insert_heats = function($round_name, $count) use ($comp_id, $division) {
                $ids = [];
                for ($i = 0; $i < $count; $i++) {
                    $ids[] = $this->model->insert([
                        'round'       => $round_name,
                        'heat_number' => $i + 1,
                        'comp_id'     => $comp_id,
                        'division'    => $division,
                    ], 'comp_heats');
                }
                return $ids;
            };

            // -------------------------------------------------------
            // Insert Round 1 heats + distribute participants
            // For ≤4 the r1_name is 'Final' so no separate Final is inserted below
            // -------------------------------------------------------
            $r1_ids = $insert_heats($config['r1_name'], $config['r1_heats']);

            foreach ($participants as $idx => $p) {
                $heat_idx = $idx % $config['r1_heats'];
                $slot     = (int)floor($idx / $config['r1_heats']);
                $this->model->insert([
                    'heat_id'        => $r1_ids[$heat_idx],
                    'participant_id' => $p['id'],
                    'jersey_color'   => $jersey_colors[$slot % count($jersey_colors)],
                ], 'comp_heat_participants');
            }

            // -------------------------------------------------------
            // Insert subsequent rounds + Final
            // -------------------------------------------------------
            $round_ids = [];
            foreach ($config['rounds'] as $round) {
                $round_ids[] = [
                    'name' => $round['name'],
                    'ids'  => $insert_heats($round['name'], $round['heats']),
                ];
            }
            // For ≤4, r1 IS the final — skip inserting a separate Final heat
            $final_ids = ($config['r1_name'] === 'Final')
                ? $r1_ids
                : $insert_heats('Final', 1);

            // -------------------------------------------------------
            // Build advancement table
            // Cross-seeding pattern:
            //   1st from Heat 1 → opposite heat from 1st of Heat 2
            //   2nd from Heat 1 → same heat as 1st of Heat 2
            // This prevents rematches in the next round.
            // -------------------------------------------------------

            // Determine what Round 1 feeds into
            $r1_feeds_into = !empty($round_ids) ? $round_ids[0]['ids'] : $final_ids;
            $r1_num_dest   = count($r1_feeds_into);

            foreach ($r1_ids as $heat_idx => $heat_id) {
                for ($pos = 1; $pos <= $config['r1_advances']; $pos++) {
                    // Cross-seed: position 1 from heat 0 → dest heat 0
                    //             position 1 from heat 1 → dest heat 1 (if exists), else heat 0
                    //             position 2 from heat 0 → dest heat 1 (crossed)
                    //             position 2 from heat 1 → dest heat 0 (crossed)
                    $dest_idx = ($heat_idx + $pos - 1) % $r1_num_dest;

                    $this->model->insert([
                        'from_heat_id'    => $heat_id,
                        'finish_position' => $pos,
                        'to_heat_id'      => $r1_feeds_into[$dest_idx],
                    ], 'comp_heat_advancement');
                }

                // Positions below advances = eliminated
                $per_heat = (int)ceil($total_participants / $config['r1_heats']);
                for ($pos = $config['r1_advances'] + 1; $pos <= $per_heat; $pos++) {
                    $this->model->insert([
                        'from_heat_id'    => $heat_id,
                        'finish_position' => $pos,
                        'to_heat_id'      => null, // eliminated
                    ], 'comp_heat_advancement');
                }
            }

            // -------------------------------------------------------
            // Wire up intermediate rounds → next round / final
            // -------------------------------------------------------
            foreach ($round_ids as $r_idx => $round) {
                $next_ids      = isset($round_ids[$r_idx + 1]) ? $round_ids[$r_idx + 1]['ids'] : $final_ids;
                $advances      = $config['rounds'][$r_idx]['advances'];
                $num_dest      = count($next_ids);

                foreach ($round['ids'] as $heat_idx => $heat_id) {
                    for ($pos = 1; $pos <= $advances; $pos++) {
                        $dest_idx = ($heat_idx + $pos - 1) % $num_dest;
                        $this->model->insert([
                            'from_heat_id'    => $heat_id,
                            'finish_position' => $pos,
                            'to_heat_id'      => $next_ids[$dest_idx],
                        ], 'comp_heat_advancement');
                    }

                    // Losers eliminated
                    $per_heat = $advances * count($round['ids']); // rough per-heat estimate
                    for ($pos = $advances + 1; $pos <= 4; $pos++) {
                        $this->model->insert([
                            'from_heat_id'    => $heat_id,
                            'finish_position' => $pos,
                            'to_heat_id'      => null,
                        ], 'comp_heat_advancement');
                    }
                }
            }

            // Final has no advancement
            $this->model->insert([
                'from_heat_id'    => $final_ids[0],
                'finish_position' => 1,
                'to_heat_id'      => null,
            ], 'comp_heat_advancement');

            return true;
        }

    // ------------------------------------------------------------
    //------- SECOND CHANCE BRACKET (UP TO 36) -------------------
    //  - R1 losers get a second chance in a Repechage round
        private function _generate_second_chance_bracket($comp_id, $participants, $total_participants, $division) {

            $jersey_colors = ['white', 'red', 'green', 'blue'];

            // -------------------------------------------------------
            // Second Chance format:
            //   R1 winners   → R2 (or Final if no post_rounds)
            //   R1 losers    → Repechage
            //   Rep winners  → R2 (merge with W winners, max 1 advances)
            //   Rep losers   → eliminated
            //   post_rounds  → chain of rounds after merge, last feeds Final
            //   All losers after merge point → eliminated
            // -------------------------------------------------------
            $configs = [
                4 => [
                    'r1_heats'     => 1,
                    'r1_advances'  => 2,
                    'rep_heats'    => 1,
                    'rep_per_heat' => 4,
                    'rep_advances' => 2,
                    'post_rounds'  => [],  // straight to Final
                ],
                6 => [
                    'r1_heats'     => 2,
                    'r1_advances'  => 1,
                    'rep_heats'    => 1,
                    'rep_per_heat' => 4,
                    'rep_advances' => 2,
                    'post_rounds'  => [],  // straight to Final
                ],
                8 => [
                    'r1_heats'     => 2,
                    'r1_advances'  => 2,  // 4W
                    'rep_heats'    => 1,
                    'rep_per_heat' => 4,
                    'rep_advances' => 2,  // 2R → R2: 4W+2R=6
                    'post_rounds'  => [
                        ['name' => 'Round 2', 'heats' => 2, 'advances' => 2], // 6 → 2×3 → 4
                    ],
                ],
                12 => [
                    'r1_heats'     => 3,
                    'r1_advances'  => 2,  // 6W
                    'rep_heats'    => 2,
                    'rep_per_heat' => 3,
                    'rep_advances' => 1,  // 2R → R2: 6W+2R=8
                    'post_rounds'  => [
                        ['name' => 'Round 2', 'heats' => 2, 'advances' => 2], // 8 → 2×4 → 4
                    ],
                ],
                16 => [
                    'r1_heats'     => 4,
                    'r1_advances'  => 2,  // 8W
                    'rep_heats'    => 2,
                    'rep_per_heat' => 4,
                    'rep_advances' => 1,  // 2R → R2: 8W+2R=10
                    'post_rounds'  => [
                        ['name' => 'Round 2',    'heats' => 3, 'advances' => 2], // 10 → 3 heats → 6
                        ['name' => 'Semi Final', 'heats' => 2, 'advances' => 2], // 6 → 2×3 → 4
                    ],
                ],
                24 => [
                    'r1_heats'     => 6,
                    'r1_advances'  => 2,  // 12W
                    'rep_heats'    => 4,
                    'rep_per_heat' => 3,
                    'rep_advances' => 1,  // 4R → R2: 12W+4R=16
                    'post_rounds'  => [
                        ['name' => 'Round 2',    'heats' => 4, 'advances' => 2], // 16 → 4×4 → 8
                        ['name' => 'Semi Final', 'heats' => 2, 'advances' => 2], // 8 → 2×4 → 4
                    ],
                ],
                36 => [
                    'r1_heats'     => 9,
                    'r1_advances'  => 2,  // 18W
                    'rep_heats'    => 6,
                    'rep_per_heat' => 3,
                    'rep_advances' => 1,  // 6R → R2: 18W+6R=24
                    'post_rounds'  => [
                        ['name' => 'Round 2',       'heats' => 6, 'advances' => 2], // 24 → 6×4 → 12
                        ['name' => 'Quarter Final', 'heats' => 3, 'advances' => 2], // 12 → 3×4 → 6
                        ['name' => 'Semi Final',    'heats' => 2, 'advances' => 2], // 6 → 2×3 → 4
                    ],
                ],
            ];

            // Find config
            $config = null;
            foreach ($configs as $limit => $cfg) {
                if ($total_participants <= $limit) {
                    $config = $cfg;
                    break;
                }
            }
            if ($config === null) {
                log_message('error', "No second chance config for {$total_participants} participants in {$division}");
                return false;
            }

            // -------------------------------------------------------
            // Helpers
            // -------------------------------------------------------
            $insert_heats = function($round_name, $count) use ($comp_id, $division) {
                $ids = [];
                for ($i = 0; $i < $count; $i++) {
                    $ids[] = $this->model->insert([
                        'round'       => $round_name,
                        'heat_number' => $i + 1,
                        'comp_id'     => $comp_id,
                        'division'    => $division,
                    ], 'comp_heats');
                }
                return $ids;
            };

            $advance = function($from_heat_id, $position, $to_heat_id) {
                $this->model->insert([
                    'from_heat_id'    => $from_heat_id,
                    'finish_position' => $position,
                    'to_heat_id'      => $to_heat_id,
                ], 'comp_heat_advancement');
            };

            $cross_dest = function($heat_idx, $pos, $dest_ids) {
                $n = count($dest_ids);
                return $n > 0 ? $dest_ids[($heat_idx + $pos - 1) % $n] : null;
            };

            // -------------------------------------------------------
            // 1. Insert heats in bracket order:
            //    R1 → Rep → post_rounds[0] → post_rounds[1]... → Final
            // -------------------------------------------------------
            $r1_ids   = $insert_heats('Round 1',   $config['r1_heats']);
            $rep_ids  = $insert_heats('Repechage', $config['rep_heats']);

            $post_ids = [];
            foreach ($config['post_rounds'] as $round) {
                $post_ids[] = $insert_heats($round['name'], $round['heats']);
            }

            $final_id = $insert_heats('Final', 1)[0];

            // First post_round (or Final if none) is the merge point
            $merge_ids = !empty($post_ids) ? $post_ids[0] : [$final_id];

            // -------------------------------------------------------
            // 2. Distribute R1 participants round-robin
            // -------------------------------------------------------
            $num_r1   = count($r1_ids);
            $per_heat = (int)ceil($total_participants / $num_r1);

            foreach ($participants as $idx => $p) {
                $heat_idx = $idx % $num_r1;
                $slot     = (int)floor($idx / $num_r1);
                $this->model->insert([
                    'heat_id'        => $r1_ids[$heat_idx],
                    'participant_id' => $p['id'],
                    'jersey_color'   => $jersey_colors[$slot % count($jersey_colors)],
                ], 'comp_heat_participants');
            }

            // -------------------------------------------------------
            // 3. Wire Round 1
            //    advancing → merge point (R2 or Final)
            //    losers    → Repechage
            // -------------------------------------------------------
            foreach ($r1_ids as $heat_idx => $heat_id) {
                for ($pos = 1; $pos <= $config['r1_advances']; $pos++) {
                    $advance($heat_id, $pos, $cross_dest($heat_idx, $pos, $merge_ids));
                }
                for ($pos = $config['r1_advances'] + 1; $pos <= $per_heat; $pos++) {
                    $advance($heat_id, $pos, $cross_dest($heat_idx, $pos - $config['r1_advances'], $rep_ids));
                }
            }

            // -------------------------------------------------------
            // 4. Wire Repechage
            //    top $rep_advances → merge point (same as R1 winners)
            //    rest              → eliminated
            // -------------------------------------------------------
            foreach ($rep_ids as $heat_idx => $heat_id) {
                for ($pos = 1; $pos <= $config['rep_advances']; $pos++) {
                    $advance($heat_id, $pos, $cross_dest($heat_idx, $pos, $merge_ids));
                }
                for ($pos = $config['rep_advances'] + 1; $pos <= $config['rep_per_heat']; $pos++) {
                    $advance($heat_id, $pos, null); // eliminated
                }
            }

            // -------------------------------------------------------
            // 5. Wire post_rounds chain
            //    each round's winners → next round (or Final if last)
            //    losers               → eliminated
            // -------------------------------------------------------
            $post_count = count($post_ids);
            foreach ($post_ids as $p_idx => $heat_ids) {
                $advances  = $config['post_rounds'][$p_idx]['advances'];
                $is_last   = ($p_idx === $post_count - 1);
                $next_ids  = $is_last ? [$final_id] : $post_ids[$p_idx + 1];

                foreach ($heat_ids as $heat_idx => $heat_id) {
                    for ($pos = 1; $pos <= $advances; $pos++) {
                        $advance($heat_id, $pos, $cross_dest($heat_idx, $pos, $next_ids));
                    }
                    for ($pos = $advances + 1; $pos <= 4; $pos++) {
                        $advance($heat_id, $pos, null); // eliminated
                    }
                }
            }

            // -------------------------------------------------------
            // 6. Final — no advancement
            // -------------------------------------------------------
            for ($pos = 1; $pos <= 4; $pos++) {
                $advance($final_id, $pos, null);
            }

            return true;
        }

        //-------------------------------------------------------------
        //--------------- MAIN GENERATION FUNCTION --------------------
        //-------------------------------------------------------------
        public function _generate_all_heats($comp_id) {

            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('organizers area');

            // --------------------------------------------------
            // Validate competition exists and is closed
            // --------------------------------------------------
            $competition = $this->model->get_one_where('id', $comp_id, 'comp_name');

            if (!$competition) {
                log_message('error', "Heat generation failed: competition {$comp_id} not found.");
                return redirect('heats/show_heats_draw/' . $comp_id);
            }

            if ($competition->status !== 'closed') {
                return redirect('heats/show_heats_draw/' . $comp_id);
            }

            $comp_name = $competition->name . ' ' . $competition->year;

            // --------------------------------------------------
            // Fetch all divisions for this competition,
            // including their elimination format
            // --------------------------------------------------
            $sql = "SELECT d.name, cd.elimination_format
                    FROM comp_competition_divisions cd
                    JOIN comp_divisions d ON cd.division_id = d.id
                    WHERE cd.competition_id = :comp_id
                    ORDER BY d.name";

            $divisions = $this->model->query_bind($sql, ['comp_id' => $comp_id], 'array');

            if (empty($divisions)) {
                log_message('error', "Heat generation failed: no divisions found for competition {$comp_id}.");
                return redirect('heats/show_heats_draw/' . $comp_id);
            }

            $errors = [];

            foreach ($divisions as $div) {
                $division = $div['name'];
                $format   = $div['elimination_format'] ?? 'double'; // safe fallback

                $division_id = $this->model->get_one_where('name', $division, 'comp_divisions')->id;

                // --------------------------------------------------
                // Fetch confirmed participants for this division
                // --------------------------------------------------
                $sql = "SELECT id
                        FROM comp_participants
                        WHERE status = 'confirmed'
                        AND comp_id = :comp_id
                        AND division_id = :division";

                $participants = $this->model->query_bind($sql, [
                    'comp_id'  => $comp_id,
                    'division' => $division_id,
                ], 'array');

                $total = count($participants);

                if ($total < 2) {
                    $errors[] = "{$division}: not enough confirmed participants ({$total}) to generate heats.";
                    continue;
                }

                // Shuffle for fair random seeding
                shuffle($participants);

                // --------------------------------------------------
                // Dispatch to correct generator by format
                // --------------------------------------------------
                $success = false;
                
                switch ($format) {
                    case 'single':
                        $success = $this->_generate_single_elimination_bracket($comp_id, $participants, $total, $division);
                        break;

                    case 'double':
                        $success = $this->_generate_double_elimination_bracket($comp_id, $participants, $total, $division);
                        break;

                    case 'second_chance':
                    $success = $this->_generate_second_chance_bracket($comp_id, $participants, $total, $division);
                    break;

                    case 'robin':
                        // TODO: implement when round robin structure is decided
                        $errors[] = "{$division}: round robin generation not yet implemented.";
                        continue 2;

                    default:
                        $errors[] = "{$division}: unknown elimination format '{$format}' — skipped.";
                        continue 2;
                }

                if (!$success) {
                    $errors[] = "{$division}: generation failed for {$total} participants. Check logs for details.";
                }
            }
            
            // Log any errors that occurred during generation, but still mark the competition as generated
            if (!empty($errors)) {
                foreach ($errors as $err) {
                    log_message('error', "Heat generation [{$comp_id}]: {$err}");
                }
                // Store errors in session so show_heats_draw can display them
                $_SESSION['heat_generation_errors'] = $errors;
                set_flashdata($errors);
            } 

            // Mark competition as generated (even with partial errors)
            $this->model->update($comp_id, ['status' => 'generated'], 'comp_name');
            set_flashdata("Heats were generated successfully.");
            echo "Heats generated for competition {$comp_name} (ID: {$comp_id}).";
            redirect('heats/heat_generation_page');
        }

        // -------------------------------------------------------
        // MAIN ENTRY POINT
        // -------------------------------------------------------

        public function _process_heat_results($heat_id) {

            if (!$heat_id) {
                log_message('error', '_process_heat_results: no heat_id provided.');
                return false;
            }

            $heat = $this->model->get_where($heat_id, 'comp_heats');

            if (!$heat) {
                log_message('error', "_process_heat_results: heat {$heat_id} not found.");
                return false;
            }

            if ($heat->status === 'finished') {
                // Already processed — recalculate scores only
                $this->_save_heat_results($heat_id, $this->_get_final_scores($heat_id));
                return true;
            }

            // 1. Get participants ranked by their top 2 wave scores
            $final_scores = $this->_get_final_scores($heat_id);

            if (empty($final_scores)) {
                log_message('error', "_process_heat_results: no scores found for heat {$heat_id}.");
                return false;
            }

            // 2. Load advancement routes for this heat from the advancement table
            //    finish_position → to_heat_id (null = eliminated)
            $sql = "SELECT finish_position, to_heat_id
                    FROM comp_heat_advancement
                    WHERE from_heat_id = :heat_id
                    ORDER BY finish_position ASC";

            $routes = $this->model->query_bind($sql, ['heat_id' => $heat_id], 'array');

            if (empty($routes)) {
                log_message('error', "_process_heat_results: no advancement routes for heat {$heat_id}. Was the bracket generated correctly?");
                return false;
            }

            // Key routes by finish_position for easy lookup
            $route_map = [];
            foreach ($routes as $route) {
                $route_map[(int)$route['finish_position']] = $route['to_heat_id'];
            }
            // 3. Route each finisher based on their position
            foreach ($final_scores as $idx => $score) {
                $position      = $idx + 1; // 1-based
                $participant_id = $score['participant_id'];

                if (!array_key_exists($position, $route_map)) {
                    // More participants than advancement rows — treat as eliminated
                    log_message('error', "_process_heat_results: no route for position {$position} in heat {$heat_id}.");
                    continue;
                }

                $to_heat_id = $route_map[$position];

                if ($to_heat_id === null) {
                    // Eliminated — nothing to do, standings capture their final rank
                    continue;
                }

                $this->_seed_into_heat($participant_id, $to_heat_id, $heat_id);
            }

            // 4. Save ranked results to standings
            $this->_save_heat_results($heat_id, $final_scores);

            // 5. Mark heat as finished
            $this->model->update($heat_id, ['status' => 'finished'], 'comp_heats');

            // 6. Check if all heats in this competition are finished
            //    If so, mark competition as finished and calculate final standings
            $sql = "SELECT COUNT(*) AS pending
                    FROM comp_heats ch
                    WHERE ch.comp_id = :comp_id
                    AND ch.status != 'finished'
                    AND EXISTS (
                        SELECT 1 FROM comp_heat_participants
                        WHERE heat_id = ch.id
                    )";
            $result = $this->model->query_bind($sql, ['comp_id' => $heat->comp_id], 'array');

            if ((int)$result[0]['pending'] === 0) {
                $this->model->update($heat->comp_id, ['status' => 'finished'], 'comp_name');

                // Calculate final standings per division
                $sql = "SELECT DISTINCT division FROM comp_heats WHERE comp_id = :comp_id";
                $divisions = $this->model->query_bind($sql, ['comp_id' => $heat->comp_id], 'array');

                foreach ($divisions as $div) {
                    $this->calculate_final_standings($heat->comp_id, $div['division']);
                }
            }

            return true;
        }

        // -------------------------------------------------------
        // Seed a participant into their next heat.
        // Takes a direct heat ID — no round name lookups needed.
        // -------------------------------------------------------

        public function _seed_into_heat($participant_id, $to_heat_id, $from_heat_id) {

            // Guard: check participant isn't already in this heat
            $sql = "SELECT COUNT(*) AS cnt
                    FROM comp_heat_participants
                    WHERE heat_id = :heat_id
                    AND participant_id = :participant_id";

            $exists = $this->model->query_bind($sql, [
                'heat_id'        => $to_heat_id,
                'participant_id' => $participant_id,
            ], 'array');

            if ((int)$exists[0]['cnt'] > 0) {
                return; // Already seeded, nothing to do
            }

            // Pick the first unused jersey color in the target heat
            $colors = ['white', 'red', 'green', 'blue'];

            $sql = "SELECT jersey_color
                    FROM comp_heat_participants
                    WHERE heat_id = :heat_id";

            $used = $this->model->query_bind($sql, ['heat_id' => $to_heat_id], 'array');
            $used_colors = array_column($used, 'jersey_color');

            $available = array_values(array_diff($colors, $used_colors));

            if (empty($available)) {
                log_message('error', "_seed_into_heat: no jersey colors left in heat {$to_heat_id} for participant {$participant_id}. Heat may be overfull.");
                return;
            }

            $this->model->insert([
                'heat_id'        => $to_heat_id,
                'participant_id' => $participant_id,
                'jersey_color'   => $available[0],
                'seeded_from'    => $from_heat_id,
            ], 'comp_heat_participants');
        }

        // -------------------------------------------------------
        // Save ranked results to comp_heat_results.
        // Overwrites existing rows if heat is being reprocessed.
        // -------------------------------------------------------

        private function _save_heat_results($heat_id, $final_scores) {

            // Clear any previous results for this heat before re-saving
            $sql = "DELETE FROM comp_heat_results WHERE heat_id = :heat_id";
            $this->model->query_bind($sql, ['heat_id' => $heat_id]);
            
            foreach ($final_scores as $idx => $data) {
                $inserted = $this->model->insert([
                    'heat_id'        => $heat_id,
                    'participant_id' => $data['participant_id'],
                    'total_score'    => $data['total_score'],
                    'rank'           => $idx + 1,
                ], 'comp_heat_results');

                if (!$inserted) {
                    log_message('error', "_save_heat_results: failed to insert result for participant {$data['participant_id']} in heat {$heat_id}.");
                }
            }
        }

        // -------------------------------------------------------
        // Get participants ranked by sum of their top 2 wave scores.
        // -------------------------------------------------------

        private function _get_final_scores($heat_id) {

            $sql = "SELECT participant_id, avg_score
                    FROM comp_wave_averages
                    WHERE heat_id = :heat_id
                    ORDER BY participant_id ASC, avg_score DESC";

            $results = $this->model->query_bind($sql, ['heat_id' => $heat_id], 'array');

            // Group top 2 waves per participant
            $grouped = [];
            foreach ($results as $row) {
                $id = $row['participant_id'];
                if (!isset($grouped[$id])) {
                    $grouped[$id] = [];
                }
                if (count($grouped[$id]) < 2) {
                    $grouped[$id][] = (float)$row['avg_score'];
                }
            }

            // Build final scores array
            $final_scores = [];
            foreach ($grouped as $participant_id => $waves) {
                $final_scores[] = [
                    'participant_id' => $participant_id,
                    'total_score'    => array_sum($waves),
                ];
            }

            // Sort descending by total score
            usort($final_scores, fn($a, $b) => $b['total_score'] <=> $a['total_score']);

            return $final_scores;
        }

        // -------------------------------------------------------
        // Final standings across all heats in a division.
        // Called once all heats in a competition are finished.
        // -------------------------------------------------------

        public function calculate_final_standings($comp_id, $division) {

            // Get all heat results for this division, ordered by round then rank
            $sql = "SELECT hr.participant_id, hr.rank, hr.total_score, ch.round
                    FROM comp_heat_results hr
                    JOIN comp_heats ch ON hr.heat_id = ch.id
                    WHERE ch.comp_id  = :comp_id
                    AND   ch.division = :division
                    ORDER BY ch.id ASC, hr.rank ASC";

            $results = $this->model->query_bind($sql, [
                'comp_id'  => $comp_id,
                'division' => $division,
            ], 'array');

            if (empty($results)) {
                log_message('error', "calculate_final_standings: no results found for division {$division} in comp {$comp_id}.");
                return [];
            }

            // Use Final heat results as the definitive ranking,
            // falling back to the deepest round each participant reached
            $participant_best = [];

            foreach ($results as $row) {
                $pid = $row['participant_id'];

                // Always overwrite — last round is the most relevant result
                // since results are ordered by heat id (chronological)
                $participant_best[$pid] = [
                    'participant_id' => $pid,
                    'best_round'     => $row['round'],
                    'best_rank'      => $row['rank'],
                    'best_score'     => $row['total_score'],
                ];
            }

            // Sort: deeper round = better standing.
            // Within same round, lower rank = better standing.
            // Round priority: Final > Semi Final > Quarter Final > Rep Final > ...
            $round_priority = [
                'Final'         => 100,
                'Semi Final'    => 90,
                'Quarter Final' => 80,
                'Rep Final'     => 75,
                'Rep QF'        => 65,
                'Rep 2'         => 55,
                'Rep 1'         => 45,
                'Round 2'       => 40,
                'Round 1'       => 30,
            ];

            usort($participant_best, function($a, $b) use ($round_priority) {
                $pa = $round_priority[$a['best_round']] ?? 0;
                $pb = $round_priority[$b['best_round']] ?? 0;

                if ($pa !== $pb) return $pb - $pa;                            // deeper round wins
                if ($a['best_rank'] !== $b['best_rank']) return $a['best_rank'] - $b['best_rank']; // lower rank wins
                return $b['best_score'] <=> $a['best_score'];                 // higher score as tiebreaker
            });

            // Save to comp_final_standings
            $sql = "DELETE FROM comp_final_standings WHERE comp_id = :comp_id AND division = :division";
            $this->model->query_bind($sql, ['comp_id' => $comp_id, 'division' => $division]);

            foreach ($participant_best as $idx => $data) {
                $this->model->insert([
                    'comp_id'        => $comp_id,
                    'division'       => $division,
                    'participant_id' => $data['participant_id'],
                    'final_score'    => $data['best_score'],
                    'rank'           => $idx + 1,
                ], 'comp_final_standings');
            }

            return $participant_best;
        }

        // -------------------------------------------------------
        // DELETE all generated data for a competition.
        // Resets status back to 'closed' so it can be regenerated.
        // URL: heats/delete_generation/{comp_id}
        // -------------------------------------------------------

        public function delete_generation() {
            
            $comp_id = segment(3, 'int');

            $competition = $this->model->get_one_where('id', $comp_id, 'comp_name');

            if (!$competition) {
                $_SESSION['error'] = 'Competition not found.';
                return redirect('heats/show_heats_draw/' . $comp_id);
            }

            // Only allow deletion if competition is in 'generated' state
            // (prevents wiping a live or finished competition)
            if (!in_array($competition->status, ['generated', 'active'])) {
                $_SESSION['error'] = 'Cannot delete generation — competition is not in a generated state.';
                return;
            }

            $this->_wipe_generation($comp_id);

            $_SESSION['success'] = 'Heat generation deleted. Competition reset to closed.';
            return;
        }

        // -------------------------------------------------------
        // DELETE all generated data then immediately regenerate.
        // URL: heats/regenerate/{comp_id}
        // -------------------------------------------------------

        public function regenerate() {
            $comp_id = segment(3, 'int');

            // Security check: only organizers can perform this action
            $success = $this->_check_organizer_permission($comp_id);
            if (!$success) {
                return; // _check_organizer_permission handles error messaging
            }

            $competition = $this->model->get_one_where('id', $comp_id, 'comp_name');

            if (!$competition) {
                $_SESSION['error'] = 'Competition not found.';
                return redirect('heats/show_heats_draw/' . $comp_id);
            }

            if (!in_array($competition->status, ['generated', 'active', 'closed'])) {
                $_SESSION['error'] = 'Cannot regenerate — competition has already finished.';
                return redirect('heats/show_heats_draw/' . $comp_id);
            }

            // Wipe everything first
            $this->_wipe_generation($comp_id);

            // _generate_all_heats expects status = 'closed'
            // _wipe_generation already resets it, so we can call directly
            $this->_generate_all_heats($comp_id);

            // _generate_all_heats redirects internally, so no redirect needed here
        }

        // -------------------------------------------------------
        // Wipe all generated data for a competition.
        // Order matters — delete child tables before parent (comp_heats).
        // -------------------------------------------------------

        private function _wipe_generation($comp_id) {

            // Security check: only organizers can perform this action
            $success = $this->_check_organizer_permission($comp_id);
            if (!$success) {
                return; // _check_organizer_permission handles error messaging
            }

            // Get all heat IDs for this competition first
            $sql    = "SELECT id FROM comp_heats WHERE comp_id = :comp_id";
            $heats  = $this->model->query_bind($sql, ['comp_id' => $comp_id], 'array');
            $heat_ids = array_column($heats, 'id');

            if (!empty($heat_ids)) {
                $placeholders = implode(',', array_fill(0, count($heat_ids), '?'));

                // Delete child records in dependency order
                $this->model->query_bind(
                    "DELETE FROM comp_heat_results WHERE heat_id IN ({$placeholders})",
                    $heat_ids
                );

                $this->model->query_bind(
                    "DELETE FROM comp_heat_participants WHERE heat_id IN ({$placeholders})",
                    $heat_ids
                );

                $this->model->query_bind(
                    "DELETE FROM comp_heat_advancement WHERE from_heat_id IN ({$placeholders})",
                    $heat_ids
                );

                // Also clean up any advancement rows where to_heat_id points to these heats
                $this->model->query_bind(
                    "DELETE FROM comp_heat_advancement WHERE to_heat_id IN ({$placeholders})",
                    $heat_ids
                );

                // Now safe to delete the heats themselves
                $this->model->query_bind(
                    "DELETE FROM comp_heats WHERE comp_id = ?",
                    [$comp_id]
                );
            }

            // Clear final standings
            $this->model->query_bind(
                "DELETE FROM comp_final_standings WHERE comp_id = ?",
                [$comp_id]
            );

            // Reset competition status back to 'closed' so it can be regenerated
            $this->model->update($comp_id, ['status' => 'closed'], 'comp_name');
        }

        private function _remove_all_heat_data($comp_id) {
            $comp_id = segment(3, 'int');

            // Security check: only organizers can perform this action
            $this->_check_organizer_permission($comp_id);

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

        function _check_organizer_permission($comp_id) {
            $this->module('competitions');
            $org_id = $this->competitions->_get_organizer_id();

            $comp = $this->model->get_one_where('id', $comp_id, 'comp_name');
            if (!$comp) {
                echo "Competition not found.";
                return false;
            }

            if ($org_id !== $comp->organizer_id) {
                echo "Unauthorized: You do not have permission to modify this competition.";
                return false;
            }
            return true;
        }
    }
?>