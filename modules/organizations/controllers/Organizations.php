<?php
    class Organizations extends Trongate {

        public function index() {
            $data['view_file'] = 'create';
            $sql = "SELECT code, name FROM countries ORDER BY name ASC";
            $countries = $this->model->query($sql, 'object'); // returns array of objects
            $data['countries'] = $countries;
            $this->template('public', $data);
        }

        function time_local() {
            date_default_timezone_set('Europe/Vilnius');
            $t=time();
            echo($t . "<br>");
            echo(date("W D H:i T O",$t));die();
        }

        public function _to_utc(?string $local, string $orgTz): ?string {
            $local = trim((string)$local);
            if ($local === '') return null;

            $tz = new DateTimeZone($orgTz);
            $formats = [
                'Y-m-d H:i',
                'Y-m-d\TH:i',
                'Y-m-d H:i:s',
                'Y-m-d\TH:i:s',
            ];

            foreach ($formats as $fmt) {
                $dt = DateTimeImmutable::createFromFormat('!' . $fmt, $local, $tz);
                $errs = DateTimeImmutable::getLastErrors();
                if ($dt && (($errs['warning_count'] ?? 0) + ($errs['error_count'] ?? 0) === 0)) {
                    return $dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                }
            }
            throw new InvalidArgumentException('Invalid local datetime, expected YYYY-MM-DD HH:MM');
        }

        public function _to_local(string $utc, string $orgTz, string $fmtOut='Y-m-d H:i'): string {
            $dt = new DateTimeImmutable($utc, new DateTimeZone('UTC'));
            return $dt->setTimezone(new DateTimeZone($orgTz))->format($fmtOut);
        }

        public function confirmation_sent() {
            $data['view_file'] = 'confirmation_sent';
            $this->template('public', $data);
        }

        function submit_create() {
            // Validate inputs
            $this->validation->set_rules('organization', 'organization', 'required|min_length[2]|max_length[255]');
            $this->validation->set_rules('email', 'email', 'required|valid_email|max_length[255]|callback_email_unique');
            $this->validation->set_rules('phone', 'phone', 'max_length[50]');
            $this->validation->set_rules('address', 'address', 'max_length[255]');
            $this->validation->set_rules('country', 'country', 'max_length[10]');
            $this->validation->set_rules('password', 'password', 'required|min_length[6]|max_length[55]');
            $this->validation->set_rules('repeat_password', 'repeat password', 'required|matches[password]');

            $result = $this->validation->run();

            if ($result == false) {
                echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">Form fields have to be filled!</p>';
                echo validation_errors();
                return;
            }

            // Start by creating a new record on Trongate users.
            $trongate_user_data['code'] = make_rand_str(32);
            $trongate_user_data['user_level_id'] = 4; // organizer id.
            $trongate_user_id = $this->model->insert($trongate_user_data, 'trongate_users');

            // Collect input data
            $organization = post('organization', true);
            $password = post('password', true);
            $email = post('email', true);

            // Generate unique slug
            $slug = $this->_unique_slug($organization, 'comp_organizations');
            // Insert new organization (default status: inactive)
            $data = [
                'organization' => $organization,
                'slug'         => $slug,
                'email'        => $email,
                'phone'        => post('phone', true),
                'address'      => post('address', true),
                'country'      => post('country', true),
                'status'       => 'inactive',
                'trongate_user_id' => $trongate_user_id,
                'password' => $this->hash_string($password)
            ];
            $orgId = $this->model->insert($data, 'comp_organizations');

            $data = [
                'organization_id' => $orgId,
                'password' => $this->hash_string($password),
                'trongate_user_id' => $trongate_user_id,
                'email'        => $email,
                'phone'        => post('phone', true)
            ];

            $orgAcctId = $this->model->insert($data, 'comp_org_accounts');

            $confirm_link = $this->_create_confirm_link($trongate_user_id);
            $this->_send_confirm_email($email, $confirm_link);

            echo BASE_URL . 'organizations/confirmation_sent';
        }

        function submit_update() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('organizers area');
            $update_id = (int)segment(3);
            if ($update_id > 0) {
                $org = $this->model->get_one_where('id', $update_id, 'comp_organizations');
                if (!$org) {
                    echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">Invalid organization ID.</p>';
                    return;
                }

                $this->module('competitions');
                $org_id = $this->competitions->_get_organizer_id();

                if ($org->id !== $org_id) {
                    echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">You are not authorized to make changes to this organization.</p>';
                    return;
                }

                // Validate inputs
                $this->validation->set_rules('timezone', 'timezone', 'required|max_length[50]');
                $this->validation->set_rules('company_code', 'company_code', 'max_length[50]');
                $this->validation->set_rules('phone', 'phone', 'max_length[50]');
                $this->validation->set_rules('address', 'address', 'max_length[255]');
                $this->validation->set_rules('country', 'country', 'max_length[10]');
                $this->validation->set_rules('status', 'status', 'max_length[10]');

                                // Validate the form data
                if ($org->email !== post('email', true)) {
                    $this->validation->set_rules('email', 'email', 'required|valid_email|max_length[255]|callback_email_unique');
                    $reconfirmation_needed = true;
                    $data['confirmed'] = 0;
                }

                $result = $this->validation->run();

                if ($result == false) {
                    echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">Form fields have to be filled!</p>';
                    echo validation_errors();
                    return;
                }

                // Collect input data
                $data = [
                    'organization' => post('organization', true),
                    'email'        => post('email', true),
                    'phone'        => post('phone', true),
                    'address'      => post('address', true),
                    'country'      => post('country', true),
                    'status'       => post('status', true),
                    'timezone'    => post('timezone', true),
                    'company_code' => post('company_code', true)
                ];
                $this->model->update($update_id, $data, 'comp_organizations');

                    if (isset($reconfirmation_needed) && $reconfirmation_needed === true) {
                        // Email has changed, send confirmation email
                        $confirm_link = $this->_create_confirm_link($org->trongate_user_id);
                        $this->_send_confirm_email($data['email'], $confirm_link);
                    }

                echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">Record updated successfully!</p>';
                return;
            }

        }

        function email_confirm() {
            $token = $_GET['token'] ?? null;

            if (!$token) {
                show_404();
            }

            // Look for token in password resets table
            $sql = "SELECT user_id, expires_at FROM comp_password_resets WHERE token = ? LIMIT 1";
            $rows = $this->model->query_bind($sql, [$token], 'object');

            if (!$rows) {
                // Token not found
                $data['error_msg'] = 'Invalid or expired confirmation link.';
                $data['view_file'] = 'email_confirm';
                $this->template('users_area', $data);
                return;
            }

            $row = $rows[0];

            // Check if token has expired
            if (strtotime($row->expires_at) < time()) {
                // Token has expired
                $data['error_msg'] = 'Invalid or expired confirmation link.';
                $data['view_file'] = 'email_confirm';
                $this->template('users_area', $data);
                return;
            }

            $user_id = $row->user_id;

            // Activate organization account
            $sql = "UPDATE comp_organizations AS o
                    JOIN trongate_users AS u ON o.trongate_user_id = u.id
                    SET o.status = 'active', o.confirmed = 1
                    WHERE u.id = ?";
            $this->model->query_bind($sql, [$user_id], 'array');

            // Delete used token
            $this->model->delete($user_id, 'comp_password_resets');

            // Show success message
            $data['success_msg'] = 'Thank you! Your email has been confirmed. You can now log in.';
            $data['view_file'] = 'email_confirm';
            $this->template('users_area', $data);
        }

        public function show() {
            $slug = (string)segment(3);

            $org = $this->model->get_one_where('slug', $slug, 'comp_organizations');
            if (!$org) { redirect('404'); }

            $country = $this->model->get_one_where('code', $org->country, 'countries');

            // Stats array of counts with events, participants(distinct), judges 
            $stats = $this->_get_org_stats($org->id);

            $sql = "SELECT id,name,year,start_date,end_date,location,status
                    FROM comp_name 
                    WHERE organizer_id=:o 
                    AND status IN ('open', 'scheduled')
                    ORDER BY start_date ASC LIMIT 8";
            $upcoming = $this->model->query_bind($sql, ['o'=>$org->id], 'object');

            // Map divisions for each upcoming event if you have comp_competition_divisions
            $upcoming_arr = [];
            foreach ($upcoming ?? [] as $ev) {
            $divs = $this->model->query_bind(
                'SELECT d.name FROM comp_competition_divisions ccd 
                JOIN comp_divisions d ON d.id=ccd.division_id WHERE ccd.competition_id=:cid',
                ['cid'=>$ev->id], 'object');
            $upcoming_arr[] = [
                'id'=>$ev->id,'name'=>$ev->name,'year'=>$ev->year,'start_date'=>$ev->start_date,'end_date'=>$ev->end_date,
                'location'=>$ev->location,'status'=>$ev->status,
                'divisions'=>array_map(fn($r)=>$r->name,$divs??[])
            ];
            }

            $sql = "SELECT id,name,location,start_date,end_date 
                    FROM comp_name 
                    WHERE organizer_id=:o 
                    AND status = 'finished' 
                    ORDER BY end_date DESC LIMIT 6"; 
            $finished = $this->model->query_bind($sql, ['o'=>$org->id],'object');

            $recent_results = array_map('get_object_vars', $finished ?? []);
            // (Optional) attach podiums per event here.

            $judges = array_map('get_object_vars',
            $this->model->query_bind(
                'SELECT cj.name, coj.role, coj.status
                FROM comp_org_judges coj JOIN comp_judges cj ON cj.id=coj.user_id
                WHERE coj.organization_id=:o AND coj.status = "active" ORDER BY cj.name ASC',
                ['o'=>$org->id],'object') ?? []
            );

            $gallery = []; // pull from your media table if available
            $socials = ['instagram'=>$org->instagram ?? null,'facebook'=>$org->facebook ?? null,'youtube'=>$org->youtube ?? null,'tiktok'=>$org->tiktok ?? null];
            $contact = ['email'=>$org->email ?? null,'phone'=>$org->phone ?? null];

            $data = compact('org','stats','upcoming_arr','recent_results','judges','gallery','socials','contact');
            // view expects $upcoming not $upcoming_arr:
            $data['upcoming'] = $upcoming_arr;
            $data['country'] = $country->name ?? null;

            $data['view_module'] = 'organizations';
            $data['view_file'] = 'public_show';
            $this->template('users_area', $data);
        }

        function dashboard() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('organizers area');
            $this->module('competitions');
            $this->module('billings');

            $org_id = $this->competitions->_get_organizer_id();

            $data['upcoming_comps'] = $this->competitions->_get_upcoming_competitions($org_id);

            $data['event_pass_credits'] = $this->billings->_get_event_pass_credits($org_id); // need to check if this is working correctly and returning expected data

            $org = $this->model->get_one_where('id', $org_id, 'comp_organizations');

            $data['org'] = $org;

            $data['countries'] = $this->model->query("SELECT code, name FROM countries ORDER BY name ASC", 'object');
            
            if (from_trongate_mx()) {
                require_once APPPATH . 'modules/lang/controllers/Lang.php';
                $this->view('settings', $data);
            } else {
                $data['view_file'] = 'settings';
                $this->template('judges_area', $data);
            }
        }

        function profile() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('organizers area');
            $this->module('competitions');
            $org_id = $this->competitions->_get_organizer_id();

            $data['org'] = $this->model->get_one_where('id', $org_id, 'comp_organizations');

            $data['countries'] = $this->model->query("SELECT code, name FROM countries ORDER BY name ASC", 'object');

            if (from_trongate_mx()) {
                require_once APPPATH . 'modules/lang/controllers/Lang.php';
                $this->view('profile', $data);
            } else {
                $data['view_file'] = 'profile';
                $this->template('judges_area', $data);
            }
        }

        function change_pass() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('organizers area');

            $this->view('change_pass');
        }

        function submit_change_pass() {
            $this->module('trongate_security');
            $this->trongate_security->_make_sure_allowed('organizers area');

            $this->module('competitions');

            $org_id = $this->competitions->_get_organizer_id();

            // Validate inputs
            $this->validation->set_rules('new_password', 'new password', 'required|min_length[6]|max_length[55]');
            $this->validation->set_rules('repeat_password', 'repeat password', 'required|matches[new_password]');

            $result = $this->validation->run();

            if ($result == false) {
                echo validation_errors(406);
                return;
            }

            // Update password
            $new_password = post('new_password', true);
            $hashed_password = $this->hash_string($new_password);

            $data['password'] = $hashed_password;

            $this->model->update($org_id, $data, 'comp_organizations');

            echo '<p style="color: black;background: orange;text-align: center;padding: 0.5rem;">Password changed successfully!</p>';
        }

        // --------------------------
        // -------Helpers
        // --------------------------

        public function _get_org_by_slug($slug) {
            // Only active orgs are publicly visible
            $sql = "SELECT id, organization AS name, slug, email, phone, address, country, status, date_created
                    FROM comp_organizations
                    WHERE slug = ? AND status = 'active'
                    LIMIT 1";
            $rows = $this->model->query_bind($sql, [$slug], 'object');
            return $rows ? $rows[0] : false;
        }

        // Generate unique slug for organization
        // E.g., $slug = $this->_unique_slug($orgName, 'comp_organizations');
        public function _unique_slug(string $name, string $inTable, int $maxLen=120): string {
            $base = $this->slugify_ascii($name);

            // Ensure room for suffix (e.g., "-2")
            if (mb_strlen($base, 'UTF-8') > $maxLen) {
                $base = mb_substr($base, 0, $maxLen, 'UTF-8');
                $base = rtrim($base, '-_');
            }

            $count = $this->model->count_rows('slug', $base, $inTable);
    
            if ($count == 0) {
                return $base;
            }
            $i = 2;

            do {
                $try = $base.'-'.$i++;
                // trim if needed

                if (mb_strlen($try, 'UTF-8') > $maxLen) {
                    $cut = $maxLen - (mb_strlen((string)$i, 'UTF-8') + 1);
                    $try = mb_substr($base, 0, $cut, 'UTF-8').'-'.($i-1);
                }
            } while ($this->model->count_rows('slug', $try, $inTable) > 0);

            return $try;

        }

        // Convert string to URL-friendly ASCII slug
        // Requires PHP intl extension for best results
        // E.g., $slug = $this->slugify_ascii($orgName);
        private function slugify_ascii(string $str, string $sep='-'): string {
            $s = trim($str);

            if (class_exists('Transliterator')) {
                // Any script → Latin; then strip accents
                $tr = Transliterator::create('Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC');
                $s  = $tr->transliterate($s);
            } else {
                // Fallback (less accurate)
                $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            }

            $s = mb_strtolower($s, 'UTF-8');
            $s = preg_replace('/[^a-z0-9]+/', $sep, $s);
            $s = trim($s, $sep);

            return $s !== '' ? $s : 'org';
        }

        private function hash_string(string $str): string {
            $hashed_string = password_hash($str, PASSWORD_BCRYPT, array(
                'cost' => 11
            ));
            return $hashed_string;
        }

        private function verify_hash(string $plain_text_str, string $hashed_string): bool {
            $result = password_verify($plain_text_str, $hashed_string);
            return $result; //TRUE or FALSE
        }

        function email_unique($email) {
            $member_obj = $this->model->get_one_where('email', $email, 'comp_organizations');
            if($member_obj === false) {
                return true; // email is available
            } else {
                $error_msg = 'Email is not available!';
                return $error_msg;
            }
        }

        public function _create_confirm_link($trongate_user_id): string {
            // Create token for email confirmation but we use password resets table
            // as it has all the fields we need.
            // Token expires in 4 days (345600 seconds)
            // Note: you may want to use a different table for this purpose.
            // See: https://www.trongate.io/docs/security/password-resets/
            $token = bin2hex(random_bytes(32));

            $expires_at = date('Y-m-d H:i:s', time() + 345600);

            // Optional: clean previous unused tokens for this user
            $sql = "DELETE FROM comp_password_resets WHERE user_id = ? OR expires_at < NOW()";
            $data = [$trongate_user_id];
            $this->model->query_bind($sql, $data, 'array');

            $data = [
                'user_id'    => $trongate_user_id,
                'token'      => $token, // If you want to store a hash: hash_hmac('sha256', $token, SECRET_KEY)
                'expires_at' => $expires_at,
                'ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];

            $this->model->insert($data, 'comp_password_resets');

            // Build confirm link
            $confirm_link = rtrim(BASE_URL, '/').'/organizations/email_confirm?token='.$token;
            return $confirm_link;
        }

        public function _send_confirm_email($to_email, $confirm_link): void {

            $curl = curl_init();

            $data = [
                "sender" => [
                    "name" => "Surf Panel Support",
                    "email" => "info@surfpan.com"
                ],
                "to" => [
                    [
                        "email" => $to_email,
                    ]
                ],
                "subject" => "confirm account email",
                "templateId" => 53,
                "params" => [
                    "email" => $to_email,
                    "link" => $confirm_link
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

        // get organization stats for dashboard
        public function _get_org_stats($org_id) {
            $stats = [
                'events'       => $this->model->count_rows('organizer_id', $org_id, 'comp_name'),
                'participants' => $this->model->query(
                    'SELECT COUNT(DISTINCT p.user_id) c FROM comp_participants p 
                    JOIN comp_name c ON c.id=p.comp_id WHERE c.organizer_id = '.$org_id,
                    'object')[0]->c ?? 0,
                'judges'       => $this->model->count_rows('organization_id', $org_id, 'comp_org_judges'),
            ];
            return $stats;
        }

        // Get free competitions left
        public function _get_free_comps_left($org_id, $max_free_comps) {
            $sql = "SELECT COUNT(*) AS competitions_this_year
                    FROM comp_name
                    WHERE YEAR(created_at) = YEAR(CURDATE())
                    AND organizer_id = $org_id";
            $comps_obj = $this->model->query($sql, 'object');
            $used = $comps_obj[0]->competitions_this_year ?? 0;
            $left = $max_free_comps - $used;
            return $left >= 0 ? $left : 0;
        }

        // Get active subscription if any
        public function _get_active_subscription(int $org_id): ?object {
            $sql = "SELECT id, product_code, current_period_start, current_period_end, meta_json
                    FROM billing_subscriptions
                    WHERE organization_id = $org_id
                    AND status = 'active'
                    AND current_period_end >= NOW()
                    ORDER BY current_period_end DESC
                    LIMIT 1";
            $row = $this->model->query($sql, 'object');
            return $row ? $row[0] : null;
        }

        //----- timezone helper -----
        public function _tz_options_html(string $selected): string {
            $ids = DateTimeZone::listIdentifiers();
            $html = '';
            foreach ($ids as $id) {
                $sel = ($id === $selected) ? ' selected' : '';
                $html .= '<option value="'.out($id).'"'.$sel.'>'.out($id).'</option>';
            }
            return $html;
        }

        public function _make_sure_allowed() {
            //Make sure the user is loged in as a organizer
            $this->module('trongate_tokens');
            $token = $this->trongate_tokens->_attempt_get_valid_token(4); //4 = organizer user_level_id
       
            if($token === false) {
                redirect('users/login');
            } else {
                return $token;
            }
        }

    }