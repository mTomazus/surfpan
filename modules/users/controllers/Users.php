<?php
    class Users extends Trongate {

        private const USER_TABLES = ['comp_users', 'comp_organizations', 'comp_judges'];
        private const ERROR_MSG = 'Your email and/or password was not correct!';
        private const MAX_LOGIN_ATTEMPTS = 5;
        private const LOCKOUT_DURATION = 900; // 15 minutes in seconds
        private const TOKEN_EXPIRY = 300; // 5 minutes
        private const TOKEN_EXPIRY_REMEMBER = 2592000; // 30 days
        private const TOKEN_EXPIRY_SESSION = 3600; // 1 hour

        function index() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('participants area');
            $this->module('trongate_tokens');
            $user_level = $this->trongate_tokens->_get_user_level();

            if ($user_level === 'participant') {
                $data['view_module'] = 'users';
                $data['view_file'] = 'dashboard';
                $this->template('users_area', $data);
            } else if ($user_level === 'organizer') {
                redirect('competitions');
            } else {
                redirect('judges');
            }
        }

        public function _make_sure_allowed() {
            //Make sure the user is loged in as participant (user_level_id = 5)
            $this->module('trongate_tokens');
            $token = $this->trongate_tokens->_attempt_get_valid_token([3, 4, 5]); //organizer and participant user_level_ids
       
            if($token === false) {
                redirect('login');
            } else {
                return $token;
            }
        }

        public function _get_user_info() {
            $this->module('trongate_tokens');
            $trongate_user_id = $this->trongate_tokens->_get_user_id();

            $sql = "SELECT cu.id, cu.name, cu.phone, cu.email, cup.dob, cup.gender, cup.club_name, c.code AS country, cup.avatar
                    FROM comp_users AS cu
                    LEFT JOIN comp_users_profiles AS cup ON cu.id = cup.user_id
                    JOIN countries AS c ON cup.country = c.code
                    WHERE cu.trongate_user_id = ?
                    LIMIT 1";
            $data = [$trongate_user_id];
            $user_info = $this->model->query_bind($sql, $data, 'array');
            $user_info[0]['user_age'] = $this->find_age_end($user_info[0]['dob'] ?? null);
            return $user_info;
        }

        function _get_user_heats() {
            $this->module('trongate_tokens');
            $trongate_user_id = $this->trongate_tokens->_get_user_id();

            $sql = "SELECT  cn.id, cn.name, cp.user_id, cd.name AS division_name, cd.id AS division_id,
                            cn.location, cn.year, cn.status, cn.entry_type, 
                            cp.id AS record_id, cp.status AS participation_status,
                            hp.jersey_color, ch.start_time, ch.end_time, ch.round,
                            ch.heat_number, ch.status AS heat_status, co.timezone
                    FROM comp_users AS cu
                    LEFT JOIN comp_participants AS cp
                    ON cu.id = cp.user_id
                    JOIN comp_divisions AS cd
                    ON cp.division_id = cd.id
                    LEFT JOIN comp_name AS cn
                    ON cp.comp_id = cn.id
                    LEFT JOIN comp_heat_participants AS hp
                    ON cp.id = hp.participant_id
                    LEFT JOIN comp_organizations AS co
                    ON cn.organizer_id = co.id
                    JOIN comp_heats AS ch
                    ON hp.heat_id = ch.id
                    WHERE cu.trongate_user_id = ? AND ch.status != 'finished'
                    LIMIT 1";
            $data = [$trongate_user_id];
            $user_comps = $this->model->query_bind($sql, $data, 'array');
            return $user_comps;
        }

        function _get_user_comps() {
            $this->module('trongate_tokens');
            $trongate_user_id = $this->trongate_tokens->_get_user_id();

            $sql = "SELECT cn.id, cn.name, cp.user_id, cd.name AS division_name, 
                            cn.location, cn.year, cn.status, cn.entry_type, 
                            cp.id AS record_id, cp.status AS participation_status
                    FROM comp_users AS cu
                    LEFT JOIN comp_participants AS cp
                    ON cu.id = cp.user_id
                    LEFT JOIN comp_name AS cn
                    ON cp.comp_id = cn.id
                    JOIN comp_divisions AS cd
                    ON cp.division_id = cd.id
                    WHERE cu.trongate_user_id = ? AND cn.status != 'finished';";
            $data = [$trongate_user_id];
            $user_comps = $this->model->query_bind($sql, $data, 'array');
            return $user_comps;
        }

        function login() {
            $data['view_module'] = 'users';
            $data['email'] = post('email', true);
            $sql = "SELECT code, name FROM countries ORDER BY name ASC";
            $countries = $this->model->query($sql, 'object'); // returns array of objects
            $data['countries'] = $countries;
            $data['view_file'] = 'login';
            $this->template('public', $data);
        }

        function logout() {
            $this->module('trongate_tokens');
            $this->trongate_tokens->_destroy();
            redirect('users');
        }

        function profile_info() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('participants area');
            $sql = "SELECT code, name FROM countries ORDER BY name ASC";
            $countries = $this->model->query($sql, 'object'); // returns array of objects
            $data['user'] = $this->_get_user_info();
            $data['countries'] = $countries;
            $data['view_file'] = 'profile';
            $this->template('users_area', $data);
        }

        function submit_create_account() {

            $this->validation->set_rules('name', 'name', 'min_length[6]|max_length[55]');
            $this->validation->set_rules('email', 'email', 'required|min_length[6]|valid_email|callback_email_unique');
            $this->validation->set_rules('password', 'password', 'required|min_length[6]|max_length[55]');
            $this->validation->set_rules('repeat_password', 'repeat password', 'required|matches[password]');
            $this->validation->set_rules('birthday', 'birthday', 'required|min_length[8]|max_length[15]');
            $this->validation->set_rules('phone', 'phone', 'required|min_length[8]|max_length[15]');
            $this->validation->set_rules('gender', 'gender', 'required');
            $this->validation->set_rules('country', 'country', 'required');

            $result = $this->validation->run(); //returns true or false
        
            if($result === true) {
                // Create new participant account.

                // Start by creating a new record on Trongate users.
                $trongate_user_data['code'] = make_rand_str(32);
                $trongate_user_data['user_level_id'] = 5; // comp member id.
                $trongate_user_id = $this->model->insert($trongate_user_data, 'trongate_users');

                // Now build up array of $data for the comp_users record.
                $data['name'] = post('name', true);
                $data['email'] = post('email', true);
                $data['phone'] = post('phone', true);
                $password = post('password');
                $data['password'] = $this->hash_string($password);
                $data['trongate_user_id'] = $trongate_user_id;
                $data['date_joined'] = date("Y-m-d H:i:s");
                
                // Create new user record in database.
                $user_id = $this->model->insert($data, 'comp_users');

                // Now create a new record in comp_users_profiles.
                $profile_data['user_id'] = $user_id;
                $profile_data['dob'] = post('birthday', true);
                $profile_data['country'] = post('country', true);
                $profile_data['gender'] = post('gender', true);
                $this->model->insert($profile_data, 'comp_users_profiles');

                // Create a new token to auto login the user.
                $this->module('trongate_tokens');
                $token_data['user_id'] = $trongate_user_id;
                $this->trongate_tokens->_generate_token($token_data);

                echo 'users'; // signal success and redirect to users area.

            } else {
                $this->login();
            }
        }

        function submit_update_profile() {
            $this->validation->set_rules('name', 'name', 'min_length[6]|max_length[55]');
            $this->validation->set_rules('email', 'email', 'min_length[6]|valid_email');
            $this->validation->set_rules('phone', 'phone', 'min_length[8]|max_length[15]');
            $this->validation->set_rules('dob', 'dob', 'required|min_length[8]|max_length[15]');
            $this->validation->set_rules('gender', 'gender', 'required');
            $this->validation->set_rules('club_name', 'club_name', 'min_length[4]|max_length[25]');
            
            $result = $this->validation->run(); //returns true or false
        
            if($result === true) {
                // Update participant account.
                $user_info = $this->_get_user_info();
                $user_id = $user_info[0]['id'];

                $data['gender'] = post('gender', true);
                $data['club_name'] = post('club_name', true);
                $data['dob'] = post('dob', true);

                $num_profiles = $this->model->count_rows('user_id', $user_id, 'comp_users_profiles');

                if ($num_profiles === 0) {
                    $data['user_id'] = $user_id;
                    $this->model->insert($data, 'comp_users_profiles');
                } 
                else {            
                    $this->model->update_where('user_id', $user_id, $data, 'comp_users_profiles');
                }
                $email = post('email', true);
                if ($user_info[0]['email'] === $email ) {
                    $data_user['name'] = post('name', true);
                    $data_user['phone'] = post('phone', true);
                    $this->model->update($user_id, $data_user, 'comp_users');
                    echo '<p style="text-align: center;background: green;padding: 0.5rem 0;margin-bottom: 1rem;">Profile updated successfully!</p>';
                } else {
                    if ($this->email_unique($email) === true) {
                        $data_user['name'] = post('name', true);
                        $data_user['phone'] = post('phone', true);
                        $data_user['email'] = $email;
                        $this->model->update($user_id, $data_user, 'comp_users');
                        echo '<p style="text-align: center;background: green;padding: 0.5rem 0;margin-bottom: 1rem;">Profile updated successfully!</p>';
                    }
                }
                return;
            } else {
                set_flashdata('Profile not updated! Please check the errors.');
            }
        }

        function email_unique($email) {
            $member_obj = $this->model->get_one_where('email', $email, 'comp_users');
            if($member_obj === false) {
                return true; // email is not in table, means available
            } else {
                $error_msg = 'email is not available!';
                return $error_msg;
            }
        }

        function submit_login() {

            $this->validation->set_rules('email', 'email', 'required|callback_login_check');
            $this->validation->set_rules('password', 'password', 'required');

            $result = $this->validation->run(); // true/false

            if ($result === false) {
                // $this->login(); // re-render form with errors
                set_flashdata(self::ERROR_MSG);
                redirect('login');
                return;
            }

            $email = $this->normalize_email((string) post('email'));
            $remember = (int) post('remember');

            // We know credentials are valid, now load the user again to start session/token.
            [$user, $table] = $this->find_user_by_email($email);

            if (!$user) {
                // Very unlikely if validation passed, but handle gracefully
                $this->validation->set_error('A login error occurred. Please try again.');
                // $this->login();
                echo self::ERROR_MSG;
                return;
            }

            $this->_in_you_go($user, $table, $remember);
        }

        function _in_you_go($user_row, $source_table, int $remember = 0) {
            // Regenerate session id to prevent fixation
            if (function_exists('session_regenerate_id')) {
                session_regenerate_id(true);
            }


            $organizerTables = ['comp_organizations', 'comp_judges'];
            $role = in_array($source_table, $organizerTables, true) ? 'organizer' : 'participant';

            $_SESSION['auth'] = [
                'user_id' => (int) ($user_row->id ?? 0),
                'trongate_user_id' => (int) ($user_row->trongate_user_id ?? 0),
                'role' => $role,
                'email' => $this->normalize_email($user_row->email ?? ''),
                'table' => $source_table,
                'logged_in_at' => time(),
            ];

            // Create last login record
            $update_data['last_login'] = time();
            $this->model->update($user_row->id, $update_data, $source_table);

            // Create Trongate token
            $this->module('trongate_tokens');
            $token_data = [
                'user_id' => (int) ($user_row->trongate_user_id ?? 0),
            ];
            if ($remember === 1) {
                $token_data['set_cookie'] = true; // Trongate will set a cookie
                // Optionally: $token_data['expiry_date'] = ... if your tokens module supports it
            }
            $this->trongate_tokens->_generate_token($token_data);

            // Role-based redirect
            if ($role === 'organizer') {
                if ($user_row->role === 'organizer') {
                    redirect('competitions'); // organizer dashboard
                } else {
                    redirect('judges'); // judge dashboard
                }
            } else {
                redirect('users'); // participant dashboard
            }
        }

        function request_modal() {
            $this->view('request_form');
        }

        function submit_request() {
            $this->validation->set_rules('email', 'email', 'required|valid_email');
            $result = $this->validation->run();
            if ($result === false) {
                echo '<p style="color:red;font-size: 0.8rem;">Not valid Email!</p>';
                echo '<button class="alt" style="color: var(--white);border: 1px solid;margin: 1.5rem auto;display: block;" onclick="closeModal()">Close</button>';
                return;
            }

            $email = post('email', true);

            $success_msg = '<p style="color:greenyellow;">request successfully sent!</p>';

            // Look up comp_users by email (do NOT reveal if not found)
            [$user, $table] = $this->find_user_by_email($email);

            if (!$user) {
                echo $success_msg;
                echo '<button class="alt" style="color: var(--white);border: 1px solid;margin: 1.5rem auto;display: block;" onclick="closeModal()">Close</button>';
                return;
            }

            $user_id = $user->trongate_user_id;

            // Create token
            $token = bin2hex(random_bytes(32));

            $expires_at = date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY);

            // Optional: clean previous unused tokens for this user
            $sql = "DELETE FROM comp_password_resets WHERE user_id = ? OR expires_at < NOW()";
            $data = [$user_id];
            $this->model->query_bind($sql, $data, 'array');

            $data = [
                'user_id'    => $user_id,
                'token'      => $token, // If you want to store a hash: hash_hmac('sha256', $token, SECRET_KEY)
                'expires_at' => $expires_at,
                'ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];

            $this->model->insert($data, 'comp_password_resets');

            // Build reset link
            $reset_link = rtrim(BASE_URL, '/').'/users/password_reset?token='.$token;

            // Send the email (example with Brevo; swap with your mailer)
            $this->_send_reset_email($email, $reset_link);

            echo $success_msg;
            echo '<button class="alt" style="color: var(--white);border: 1px solid;margin: 1.5rem auto;display: block;" onclick="closeModal()">Close</button>';
            return;
        }

        function password_reset() {
            $token = out($_GET['token']);
            if (!$token) {
                http_response_code(400);
                die('Bad request.');
            }

            $row = $this->model->get_one_where('token', $token, 'comp_password_resets');
            if (!$row || (int)$row->used === 1 || strtotime($row->expires_at) < time()) {
                // Token invalid or expired (don’t leak reason)
                $data['view_file'] = 'token_expired';
                return $this->template('public', $data);
            }

            $data['token'] = $token;
            $data['view_module'] = 'users';
            $data['view_file'] = 'pass_reset_form';
            $this->template('public', $data);
        }

        function submit_reset() {
            $this->validation->set_rules('token', 'token', 'required');
            $this->validation->set_rules('password', 'password', 'required|min_length[8]|max_length[72]');
            $this->validation->set_rules('repeat_password', 'repeat_password', 'required|matches[password]');

            $result = $this->validation->run();
            
            if ( $result === false) {
                echo 'Passwords do not match!';
                return; // will re-render with same token in GET
            }
            
            $token = post('token', true);
            $row = $this->model->get_one_where('token', $token, 'comp_password_resets');
            if (!$row || (int)$row->used === 1 || strtotime($row->expires_at) < time()) {
                $data['view_file'] = 'token_expired';
                return $this->template('public', $data);
            }

            [$user, $table] = $this->find_user_by_trongate_id($row->user_id);

            if (!$user) {
                $data['view_file'] = 'token_expired';
                return $this->template('public', $data);
            }

            $new_hash = $this->hash_string(post('password', true));

            // Update password
            $this->model->update($user->id, ['password' => $new_hash], $table);

            // Invalidate all existing login tokens for this user (recommended)
            if (isset($user->trongate_user_id)) {
                $sql = "DELETE FROM trongate_tokens WHERE user_id = ?";
                $data = [$user->trongate_user_id];
                $this->model->query_bind($sql, $data, 'array');
            }

            // Mark reset token used
            $this->model->update($row->id, ['used' => 1], 'comp_password_resets');

            // Done go to login
            $msg = "The password was successfuly updated.";
            set_flashdata($msg);
            $data['email'] = $user->email;
            $data['view_file'] = 'login';
            $this->template('public', $data);
        }

        // --- Helpers --------------------------------------------------------------
        public function _send_reset_email($to_email, $reset_link): void {

            $curl = curl_init();

            $data = [
                "sender" => [
                    "name" => "Surf Panel",
                    "email" => "info@surfpan.com"
                ],
                "to" => [
                    [
                        "email" => $to_email,
                    ]
                ],
                "subject" => "password reset link",
                "templateId" => 52,
                "params" => [
                    "email" => $to_email,
                    "link" => $reset_link
                ],
                "headers" => [
                    "X-Mailin-custom" => "custom_header_1:custom_value_1|custom_header_2:custom_value_2|custom_header_3:custom_value_3|custom_header_4:custom_value_4",
                    "charset" => "iso-8859-1"
                ]
            ];

            $api_secret = constant('BREVO_API_KEY');

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.brevo.com/v3/smtp/email",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => [
                    "accept: application/json",
                    "api-key: ".$api_secret,
                    "content-type: application/json"
                ],
                CURLOPT_POSTFIELDS => json_encode($data),
            ]);

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);

        }

        private function normalize_email(string $email): string {
            return strtolower(trim($email));
        }

        private function find_user_by_email(string $email, array $tables = self::USER_TABLES): array {

            foreach ($tables as $table) {
                $row = $this->model->get_one_where('email', $email, $table);
                if ($row !== false) {
                    return [$row, $table];
                }
            }
            return [false, null];
        }

        private function find_user_by_trongate_id(string $trongate_id, array $tables = self::USER_TABLES): array {
            foreach ($tables as $table) {
                $row = $this->model->get_one_where('trongate_user_id', $trongate_id, $table);
                if ($row !== false) {
                    return [$row, $table];
                }
            }
            return [false, null];
        }

        private function find_age_end($birthdate) {
            // $birthdate should be in format YYYY-MM-DD
            $birth = new DateTime($birthdate);

            // Get the last day of the current year
            $endOfYear = new DateTime(date('Y') . '-12-31');

            // Calculate the difference
            $age = $birth->diff($endOfYear)->y;

            return $age;
        }

        function user_has_role($user_id, $role_name) {
            $sql = "SELECT 1
                    FROM comp_users_roles ur
                    JOIN comp_roles r ON r.id = ur.role_id
                    WHERE ur.user_id = :uid AND r.name = :rname
                    LIMIT 1";
            return (bool) $this->model->query_bind($sql, ['uid'=>$user_id, 'rname'=>$role_name], 'num_rows');
        }

        function require_role($role_name) {
            $user_id = $this->trongate_security->_get_user_id();
            if (!$user_id || !$this->user_has_role($user_id, $role_name)) {
                redirect('not_allowed'); // or show_404()
            }
        }

        function grant_role($user_id, $role_name) {
            // ensure role exists -> get id
            $row = $this->model->query_bind("SELECT id FROM comp_roles WHERE name = ?", [$role_name], 'row');
            if (!$row) { return false; }
            $this->model->query_bind("INSERT IGNORE INTO comp_users_roles (user_id, role_id) VALUES (?, ?)", [$user_id, $row->id]);
            return true;
        }

        function revoke_role($user_id, $role_name) {
            $row = $this->model->query_bind("SELECT id FROM comp_roles WHERE name = ?", [$role_name], 'row');
            if (!$row) { return false; }
            $this->model->query_bind("DELETE FROM comp_users_roles WHERE user_id = ? AND role_id = ?", [$user_id, $row->id]);
            return true;
        }

        // Optional: store a precomputed dummy hash in config for timing equalization
        private function dummy_hash(): string {
            if (defined('DUMMY_HASH')) {
                return DUMMY_HASH;
            }
            // Fallback dummy bcrypt (format only; replace with a real hash from your hasher)
            return '$2y$11$abcdefghijklmnopqrstuvABCDE1234567890wxyzABCDE12';
        }

        private function hash_string(string $str): string {
            return password_hash($str, PASSWORD_BCRYPT, ['cost' => 11]);
        }

        private function verify_hash(string $plain_text_str, string $hashed_string): bool {
            return password_verify($plain_text_str, $hashed_string);
        }

        // --- Validation callback --------------------------------------------------

        function login_check($email) {
            $error_msg = self::ERROR_MSG;
            $email = $this->normalize_email((string) $email);
            $password = (string) post('password');

            // Try to find user in either table
            [$user, $table] = $this->find_user_by_email($email);
            if ($user === false || $table === null) {
                return $error_msg;
            }

            if ($user && isset($user->lockout_time) && (int)$user->lockout_time > time()) {
                $error_msg = 'Too many login attempts. Please try again later.';
                return $error_msg;
            }

            // Use real hash if found, otherwise dummy to match timing
            $stored_hash = $user ? $user->password : $this->dummy_hash();

            $is_valid = $this->verify_hash($password, $stored_hash);

            if (!$user || !$is_valid) {
                // soft rate limit here
                if ($user) {
                    $num_logins = (int) $user->num_logins + 1;
                    $update_data['num_logins'] = $num_logins;

                    if ($num_logins >= self::MAX_LOGIN_ATTEMPTS) {
                        $update_data['lockout_time'] = time() + self::LOCKOUT_DURATION;
                        $update_data['num_logins'] = 0; // reset counter after lockout
                        $error_msg = 'Too many login attempts. Please try again later.';
                    }
                    $data['num_logins'] = (int) $user->num_logins + 1;
                    $this->model->update($user->id, $update_data, $table);
                }

                return $error_msg;
            }
            $update_data['num_logins'] = 0; // reset counter after lockout
            $this->model->update($user->id, $update_data, $table);

            return true; // validation passes
        }

        // --- Modals  ---------------------------------------

        function organizer() {
            $org_id = (int)segment(3);
            $organizer = $this->model->get_where($org_id, 'comp_organizations');
            $competitions = $this->model->get_many_where('organizer_id', $org_id, 'comp_name');
            $data['organizer'] = $organizer;

            $grouped = [];
            foreach ($competitions as $comp) {
                $grouped[$comp->status][] = $comp;
            }
            $data['grouped_comps'] = $grouped;

            $this->view('organizer_modal', $data);
        }

        function withdraw() {
            $data['record_id'] = (int)segment(3);
            $this->view('withdraw_modal', $data);
        }

        function competition() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('participants area');
            $comp_id = (int)segment(3);
            $user = $this->_get_user_info();
            $data['user'] = $user;
            $user_age = $this->find_age_end($user[0]['dob']);
            $age = 'u' . $user_age;
            $gender = $user[0]['gender'];

            $competition = $this->model->get_where($comp_id, 'comp_name');
            $organizer = $this->model->get_where($competition->organizer_id, 'comp_organizations');
              
            $sql = "SELECT * 
                    FROM comp_competition_divisions AS ccd
                    LEFT JOIN comp_divisions AS cd
                    ON ccd.division_id = cd.id 
                    WHERE ccd.competition_id = ?
                    AND (cd.age >= ? OR cd.age = 'adult' OR cd.age = 'veteran')
                    AND cd.gender = ?";

            // Unnamed parameters to bind to the query.
            $data = [$comp_id, $age, $gender];

            // Execute the query using the unnamed parameters.
            $rows = $this->model->query_bind($sql, $data, 'array');

            $data['divisions'] = $rows;
            $data['competition'] = $competition;
            $data['organizer'] = $organizer;    
            $this->view('competition_modal', $data);
        }
        
        // --- Actions ---------------------------------------

        function confirm_withdraw() {
            $record_id = (int)segment(3);
            $user = $this->_get_user_info();
            $user_id = $user[0]['id'];
            if (from_trongate_mx()) {
                $participant = $this->model->get_one_where('id', $record_id, 'comp_participants');
                if ($user_id != $participant->user_id) {
                    set_flashdata('Request is NOT allowed');
                    redirect('users');
                } else {
                    $is_deleted = $this->model->delete($record_id, 'comp_participants');
                    set_flashdata('You have successfully withdrawn from the competition.');
                    redirect('users');
                }
            } else {
                set_flashdata('You are not registered for this competition.');
                redirect('users');
            };
        }

        function join() {
            
            $data['comp_id'] = segment(3, 'int');
            $user = $this->_get_user_info();
            $data['user_id'] = $user[0]['id'];

            $this->validation->set_rules('division', 'division', 'min_length[6]|max_length[15]');
            $result = $this->validation->run(); //returns true or false
        
            if($result === true) {

                $data['gender_age'] = post('division', true);

                $division = $this->model->get_one_where('name', $data['gender_age'], 'comp_divisions');

                $sql = "SELECT id 
                        FROM comp_participants 
                        WHERE comp_id = ? 
                        AND user_id = ?
                        AND division_id = ?";
                $signed_up = $this->model->query_bind($sql, [$data['comp_id'], $data['user_id'], $division->id], 'array');

                if ($signed_up) {
                    $flash_msg = 'You have already signed up for this competition in this division.';
                    set_flashdata($flash_msg);
                    redirect('users');
                }

                // Get the division ID from the division name
                $division = $this->model->get_one_where('name', $data['gender_age'], 'comp_divisions');
                if ($division) {
                    $data['division_id'] = $division->id;
                }

                //insert the new record
                $this->model->insert($data, 'comp_participants');
                $flash_msg = 'The record was successfully created';
                set_flashdata($flash_msg);
            }

        }

        public function search() {
            // get user id
            // q from query string: /competitions/search?q=...
            $q = trim($_GET['q']);
            if ($q === '' || mb_strlen($q) < 2) {
                header('Content-Type: application/json');
                echo json_encode([]); exit;
            }

            // Build bind once; use CONCAT in SQL so we don't have to inject % in PHP
            $bind1 = ['q' => $q];
            $bind2 = ['q' => $q];

            // --- Competitions (comp_name) ---
            // Assumptions:
            //   comp_name: id, name, organizer_id, status (optional)
            //   comp_organizers: id, name
            // If you don't have c.status, remove the column from SELECT.
            $sql1 = "SELECT
                        c.id,
                        c.name AS title,
                        c.year,
                        c.entry_type,
                        o.organization AS organiser,                         
                        LOWER(COALESCE(c.status, 'unknown')) AS status,
                        'competition' AS type
                    FROM comp_name c
                    LEFT JOIN comp_organizations o ON o.id = c.organizer_id
                    WHERE COALESCE(c.status,'') <> 'finished'
                    AND (
                        c.name LIKE CONCAT('%', :q, '%')
                        OR o.organization LIKE CONCAT('%', :q, '%')
                    )
                    ORDER BY c.name
                    LIMIT 20";
            $competitions = $this->model->query_bind($sql1, $bind1, 'array');

            // --- Organisers (comp_organizers) ---
            // Assumptions:
            //   comp_organizers: id, name, city (optional), status (optional)
            $sql2 = "SELECT
                        o.id,
                        o.organization AS title,
                        'active' AS status,
                        'organiser' AS type,
                        c.name AS country,
                        o.slug
                    FROM comp_organizations o
                    JOIN countries c ON c.code = o.country
                    WHERE o.organization LIKE CONCAT('%', :q, '%')
                    ORDER BY o.organization
                    LIMIT 20";
            $organisers = $this->model->query_bind($sql2, $bind2, 'array');

            $out = array_merge($competitions, $organisers);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($out);
            exit;
        }

    }