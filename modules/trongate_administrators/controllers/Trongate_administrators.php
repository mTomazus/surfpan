<?php

/**
 * Provides functionality for managing administrators within the Trongate framework.
 * This class handles login, user management, and authentication-related tasks.
 * 
 * NOTE: The default username and password for administrators is 'admin' and 'admin'.
 */
class Trongate_administrators extends Trongate {

    // NOTE: Uncomment the line below to set a custom secret login segment.
    // private $secret_login_segment = 'tg-admin';

    /**
     * Where to redirect after successful login.
     * Default dashboard home for administrators.
     *
     * @var string
     */
    private $dashboard_home = 'trongate_administrators/dashboard';

    /**
     * Renders a login page for administrators.
     *
     * @return void
     */
    public function login(): void {
        // Handle secret login segment for specific URL configurations.
        // Redirect to 404 if accessed incorrectly from within the module.
        if (isset($this->secret_login_segment)) {
            if (is_numeric(strpos(current_url(), 'trongate_administrators'))) {
                $this->template('error_404');
                die();
            }
            $data['form_location'] = BASE_URL . $this->secret_login_segment . '/submit_login';
        } else {
            $data['form_location'] = BASE_URL . 'trongate_administrators/submit_login';
        }

        $data['username'] = post('username');
        $data['view_module'] = 'trongate_administrators';
        $data['view_file'] = 'login_form';
        $this->load_template($data);
    }

    /**
     * Handles the submission of login forms, validating user input and logging users in if validation passes.
     * Redirects to the login form on validation failure or to the base URL on 'Cancel' submission.
     *
     * @return void
     */
    public function submit_login(): void {
        // Handle secret login segment for specific URL configurations.
        // Redirect to 404 if accessed incorrectly from within the module.
        if (isset($this->secret_login_segment)) {
            if (is_numeric(strpos(current_url(), 'trongate_administrators'))) {
                $this->template('error_404');
                die();
            }
        }

        $submit = post('submit');

        if ($submit === 'Submit') {
            // Set validation rules for username and password.
            $this->validation->set_rules('username', 'username', 'required|callback_login_check');
            $this->validation->set_rules('password', 'password', 'required|min_length[5]');
            $result = $this->validation->run();

            if ($result === true) {
                $this->log_user_in(post('username'));
            } else {
                // Reload login form on validation failure.
                $this->login();
            }
        } elseif ($submit === 'Cancel') {
            // Redirect to base URL on cancel submission.
            redirect(BASE_URL);
        }
    }

    /**
     * Handles form submission for user data, validates input, updates existing records or creates new ones accordingly.
     * Redirects to management view or the creation form based on form submission.
     *
     * @return void
     */
    public function submit(): void {
        $data['token'] = $this->_make_sure_allowed();
        $submit = post('submit');

        if ($submit === 'Submit') {
            // Set validation rules for username, password, and repeat password.
            $this->validation->set_rules('username', 'username', 'required|min_length[5]|callback_username_check');
            $this->validation->set_rules('password', 'password', 'required|min_length[5]');
            $this->validation->set_rules('repeat_password', 'repeat password', 'matches[password]');

            $result = $this->validation->run();

            if ($result === true) {
                $update_id = segment(3);
                $data = $this->get_data_from_post();
                unset($data['repeat_password']);
                $data['password'] = $this->hash_string($data['password']);

                if (is_numeric($update_id)) {
                    // Update existing administrator record.
                    $this->model->update($update_id, $data);
                    set_flashdata('The record was successfully updated');
                } else {
                    // Create new administrator record.
                    $this->module('trongate_users');
                    $data['trongate_user_id'] = $this->trongate_users->_create_user(1);
                    $this->model->insert($data);
                    set_flashdata('The record was successfully created');
                }

                // Redirect to administrators management page.
                redirect('trongate_administrators/manage');
            } else {
                // Reload creation form on validation failure.
                $this->create();
            }
        } elseif ($submit === 'Cancel') {
            // Redirect to administrators management page on cancel submission.
            redirect('trongate_administrators/manage');
        }
    }

    /**
     * Handles the deletion of a specific user record and related entries based on the given update ID.
     * Performs the deletion of related records from 'trongate_users' and 'trongate_administrators' tables.
     * Redirects to the management page after successful deletion.
     *
     * @return void
     */
    public function submit_delete(): void {
        $this->_make_sure_allowed();
        $update_id = segment(3);
        $submit = post('submit');

        if (($submit === 'Delete Record Now') && (is_numeric($update_id))) {
            // Get the trongate_user_id associated with the administrator record.
            $user_obj = $this->model->get_where($update_id, 'trongate_administrators');
            $trongate_user_id = $user_obj->trongate_user_id;

            // Delete records from 'trongate_users' and 'trongate_administrators' tables.
            $this->model->delete($trongate_user_id, 'trongate_users');
            $this->model->delete($update_id, 'trongate_administrators');
            set_flashdata('The record was successfully deleted');
        }

        // Redirect to administrators management page.
        redirect('trongate_administrators/manage');
    }

    /**
     * Manages the display of the administrator records within the 'trongate_administrators' table.
     * Retrieves necessary data such as admin ID, username rows from the model, and loads the management view.
     *
     * @return void
     */
    public function manage(): void {
        $token = $this->_make_sure_allowed();
        $data['my_admin_id'] = $this->get_my_id($token);
        $data['rows'] = $this->model->get('username', 'trongate_administrators');
        $data['view_module'] = 'trongate_administrators';
        $data['view_file'] = 'manage';
        $this->load_template($data);
    }

    /**
     * Platform overview dashboard for administrators.
     *
     * @return void
     */
    public function dashboard(): void {
        $token = $this->_make_sure_allowed();
        $data['my_admin_id'] = $this->get_my_id($token);

        $count = fn($r) => (is_array($r) && isset($r[0]->c)) ? (int)$r[0]->c : 0;

        // Organizations
        $data['total_orgs']  = $count($this->model->query('SELECT COUNT(*) AS c FROM comp_organizations', 'object'));
        $data['active_orgs'] = $count($this->model->query("SELECT COUNT(*) AS c FROM comp_organizations WHERE status = 'active'", 'object'));

        // Competitions
        $data['total_comps'] = $count($this->model->query("SELECT COUNT(*) AS c FROM comp_name WHERE status NOT IN ('archived','canceled')", 'object'));
        $data['live_comps']  = $count($this->model->query("SELECT COUNT(*) AS c FROM comp_name WHERE status = 'running'", 'object'));

        // Users & Judges
        $data['total_users']   = $count($this->model->query('SELECT COUNT(*) AS c FROM comp_users', 'object'));
        $data['total_judges']  = $count($this->model->query('SELECT COUNT(*) AS c FROM comp_judges', 'object'));

        // Total revenue (all paid charges)
        $r = $this->model->query("SELECT COALESCE(SUM(amount), 0) AS rev FROM billing_charges WHERE status = 'paid'", 'object');
        $data['total_revenue'] = (is_array($r) && isset($r[0]->rev)) ? (float)$r[0]->rev : 0;

        // Recent organizations (last 5 by id)
        $r = $this->model->query('SELECT id, organization, email, status FROM comp_organizations ORDER BY id DESC LIMIT 5', 'object');
        $data['recent_orgs'] = is_array($r) ? $r : [];

        // Recent competitions (last 5)
        $r = $this->model->query("SELECT id, name, year, status FROM comp_name WHERE status NOT IN ('archived','canceled') ORDER BY id DESC LIMIT 5", 'object');
        $data['recent_comps'] = is_array($r) ? $r : [];

        $data['view_module'] = 'trongate_administrators';
        $data['view_file'] = 'dashboard';
        $this->load_template($data);
    }

    /**
     * Redirects to the 'create' route for the Trongate administrators module based on the user's token.
     *
     * @return void
     */
    public function account(): void {
        $token = $this->_make_sure_allowed();
        $update_id = $this->get_my_id($token);
        redirect('trongate_administrators/create/' . $update_id);
    }

    /**
     * Manages the creation or updating of Trongate administrator records based on provided data.
     * Redirects to appropriate routes for the record management.
     *
     * @return void
     */
    public function create(): void {
        $token = $this->_make_sure_allowed();
        $update_id = segment(3);
        $submit = post('submit');

        if ((is_numeric($update_id)) && ($submit === '')) {
            $data = $this->get_data_from_db($update_id);
        } else {
            $data = $this->get_data_from_post();
        }

        $data['my_admin_id'] = $this->get_my_id($token);

        if (is_numeric($update_id)) {
            $data['headline'] = 'Update Record';

            if ($data['my_admin_id'] === $update_id) {
                $data['headline'] = str_replace('Record', 'Your Account', $data['headline']);
            }
        } else {
            $data['headline'] = 'Create Record';
        }

        $data['form_location'] = str_replace('/create', '/submit', current_url());
        $data['conf_delete_url'] = str_replace('/create', '/conf_delete', current_url());
        $data['token'] = $this->_make_sure_allowed();
        $data['view_module'] = 'trongate_administrators';
        $data['view_file'] = 'create';
        $this->load_template($data);
    }

    /**
     * Manages the confirmation process for deleting a Trongate administrator record.
     * Validates the deletion request and loads the confirmation template if the record exists.
     * Redirects to the management page if the record doesn't exist.
     *
     * @return void
     */
    public function conf_delete(): void {
        $token = $this->_make_sure_allowed();
        $update_id =  segment(3);

        if (!is_numeric($update_id)) {
            redirect('trongate_administrators/manage');
        } else {
            $data['my_admin_id'] = $this->get_my_id($token);
            $data['form_location'] = str_replace('/conf_delete', '/submit_delete', current_url());
            $data['view_module'] = 'trongate_administrators';
            $data['view_file'] = 'conf_delete';
            $this->load_template($data);
        }
    }

    /**
     * Detail page for a single organization.
     *
     * @return void
     */
    public function org_detail(): void {
        $token = $this->_make_sure_allowed();
        $data['my_admin_id'] = $this->get_my_id($token);

        $org_id = (int) segment(3);
        if ($org_id <= 0) {
            redirect('trongate_administrators/organizations_list');
        }

        $org = $this->model->get_one_where('id', $org_id, 'comp_organizations');
        if (!$org) {
            redirect('trongate_administrators/organizations_list');
        }
        $data['org'] = $org;

        // Helper: safely extract count from query result
        $count = fn($r) => (is_array($r) && isset($r[0]->c)) ? (int)$r[0]->c : 0;
        
        // Country name
        $r = $this->model->query_bind(
            'SELECT name FROM countries WHERE code = :code LIMIT 1',
            ['code' => $org->country ?? ''],
            'object'
        );
        $data['country_name'] = (is_array($r) && isset($r[0]->name))
            ? $r[0]->name
            : strtoupper($org->country ?? '—');

        // Stats
        $r = $this->model->query_bind(
            'SELECT COUNT(*) AS c FROM comp_name WHERE organizer_id = :oid',
            ['oid' => $org_id], 'object'
        );
        $data['total_comps'] = $count($r);

        $r = $this->model->query_bind(
            'SELECT COUNT(DISTINCT p.user_id) AS c FROM comp_participants p
             JOIN comp_name cn ON cn.id = p.comp_id WHERE cn.organizer_id = :oid',
            ['oid' => $org_id], 'object'
        );
        $data['total_participants'] = $count($r);

        $r = $this->model->query_bind(
            'SELECT COUNT(*) AS c FROM comp_org_judges WHERE organization_id = :oid',
            ['oid' => $org_id], 'object'
        );
        $data['total_judges'] = $count($r);

        // Competitions (most recent 10)
        $r = $this->model->query_bind(
            "SELECT id, name, year, status, location FROM comp_name
             WHERE organizer_id = :oid ORDER BY id DESC LIMIT 10",
            ['oid' => $org_id], 'object'
        );
        $data['competitions'] = is_array($r) ? $r : [];

        // Active subscription (most recent)
        $r = $this->model->query_bind(
            "SELECT * FROM billing_subscriptions
             WHERE organization_id = :oid
             ORDER BY id DESC LIMIT 1",
            ['oid' => $org_id], 'object'
        );
        $data['subscription'] = (is_array($r) && isset($r[0])) ? $r[0] : null;

        // Recent billing charges (last 8) — organizer_id is the correct column
        $r = $this->model->query_bind(
            "SELECT bc.id, bp.name AS product, bc.amount, bc.currency, bc.status
             FROM billing_charges bc 
             JOIN billing_products bp ON bp.id = bc.product_id
             WHERE bc.organizer_id = :oid
             ORDER BY bc.id DESC LIMIT 8",
            ['oid' => $org_id], 'object'
        );
        $data['charges'] = is_array($r) ? $r : [];

        // Event pass credits (admin-granted free event_pass id = 6)
        $r = $this->model->query_bind(
            "SELECT COALESCE(SUM(quantity),0) AS c FROM billing_charges
             WHERE organizer_id = :oid AND product_id = 6 AND status = 'paid'",
            ['oid' => $org_id], 'object'
        );
        $data['event_pass_credits'] = (is_array($r) && isset($r[0]->c)) ? (int)$r[0]->c : 0;

        $data['view_module'] = 'trongate_administrators';
        $data['view_file'] = 'org_detail';
        $this->load_template($data);
    }

    /**
     * Toggle an organization's status between active and inactive.
     *
     * @return void
     */
    public function toggle_org_status(): void {
        $this->_make_sure_allowed();

        $org_id = (int) segment(3);
        if ($org_id <= 0) {
            redirect('trongate_administrators/organizations_list');
        }

        $org = $this->model->get_one_where('id', $org_id, 'comp_organizations');
        if (!$org) {
            redirect('trongate_administrators/organizations_list');
        }

        $new_status = ($org->status === 'active') ? 'inactive' : 'active';
        $this->model->update($org_id, ['status' => $new_status], 'comp_organizations');

        redirect('trongate_administrators/org_detail/' . $org_id);
    }

    /**
     * Billing overview: all competition charges across all organizations.
     *
     * @return void
     */
    public function billing_overview(): void {
        $token = $this->_make_sure_allowed();
        $data['my_admin_id'] = $this->get_my_id($token);

        $count = fn($r) => (is_array($r) && isset($r[0]->c)) ? (int)$r[0]->c : 0;
        $sum   = fn($r) => (is_array($r) && isset($r[0]->c)) ? (float)$r[0]->c : 0.0;

        // Summary stats (product_id = 6 is the event pass product)
        $data['total_collected']     = $sum($this->model->query("SELECT COALESCE(SUM(amount),0) AS c FROM billing_charges WHERE status = 'paid' AND product_id != 6", 'object'));
        $data['total_pending']       = $sum($this->model->query("SELECT COALESCE(SUM(amount),0) AS c FROM billing_charges WHERE status = 'pending'", 'object'));
        $data['paid_comps']          = $count($this->model->query("SELECT COUNT(*) AS c FROM billing_charges WHERE status = 'paid' AND product_id != 6", 'object'));
        $data['free_events_granted'] = $count($this->model->query("SELECT COALESCE(SUM(quantity),0) AS c FROM billing_charges WHERE product_id = 6 AND status = 'paid'", 'object'));

        // All competition charges, most recent first
        $sql = "SELECT bc.id, bc.amount, bc.currency, bc.status, bc.participants_count,
                       cn.name AS comp_name, cn.year AS comp_year,
                       co.organization AS org_name, co.id AS org_id
                FROM billing_charges bc
                LEFT JOIN comp_name cn ON cn.id = bc.comp_id
                LEFT JOIN comp_organizations co ON co.id = bc.organizer_id
                WHERE bc.product_id != 6
                ORDER BY bc.id DESC LIMIT 100";
        $r = $this->model->query($sql, 'object');
        $data['charges'] = is_array($r) ? $r : [];

        // Admin-granted free event passes (product_id = 6, joined with usage to show if consumed)
        $sql = "SELECT bc.id, bc.quantity, bc.status,
                       co.organization AS org_name, co.id AS org_id,
                       bpu.competition_id AS used_in_comp,
                       cn.name AS used_in_comp_name
                FROM billing_charges bc
                LEFT JOIN comp_organizations co ON co.id = bc.organizer_id
                LEFT JOIN billing_pass_uses bpu ON bpu.charge_id = bc.id
                LEFT JOIN comp_name cn ON cn.id = bpu.competition_id
                WHERE bc.product_id = 6
                ORDER BY bc.id DESC LIMIT 50";
        $r = $this->model->query($sql, 'object');
        $data['event_passes'] = is_array($r) ? $r : [];

        $data['view_module'] = 'trongate_administrators';
        $data['view_file'] = 'billing_overview';
        $this->load_template($data);
    }

    /**
     * Grant a free event pass credit to an organization.
     *
     * @return void
     */
    public function grant_free_event(): void {
        $this->_make_sure_allowed();

        $org_id = (int) segment(3);
        if ($org_id <= 0) {
            redirect('trongate_administrators/organizations_list');
        }
        $data = [
            'organizer_id' => $org_id,
            'product_id'   => 6,
            'amount'       => 0,
            'quantity'     => 1,
            'status'       => 'paid',
            'provider'     => 'manual'
        ];
        $this->model->insert($data, 'billing_charges');

        set_flashdata('Free event granted to organization ID ' . $org_id);
        redirect('trongate_administrators/org_detail/' . $org_id);
    }

    /**
     * Lists all organizations for the admin panel.
     *
     * @return void
     */
    public function organizations_list(): void {
        $token = $this->_make_sure_allowed();
        $data['my_admin_id'] = $this->get_my_id($token);

        $sql = "SELECT co.id, co.organization, co.email, co.country, co.status AS org_status,
                       (SELECT bs.status
                        FROM billing_subscriptions bs
                        WHERE bs.organization_id = co.id
                        ORDER BY bs.id DESC LIMIT 1) AS sub_status
                FROM comp_organizations co
                ORDER BY co.id DESC";

        $data['orgs'] = $this->model->query($sql, 'object') ?: [];
        $data['view_module'] = 'trongate_administrators';
        $data['view_file'] = 'organizations_list';
        $this->load_template($data);
    }

    /**
     * Impersonate an organization's admin account.
     * Saves the current admin token and issues a new token for the org's trongate_user.
     *
     * @return void
     */
    public function impersonate(): void {
        $this->_make_sure_allowed();

        $org_id = (int) segment(3);
        if ($org_id <= 0) {
            redirect('trongate_administrators/organizations_list');
        }

        $org = $this->model->get_one_where('id', $org_id, 'comp_organizations');
        if (!$org) {
            redirect('trongate_administrators/organizations_list');
        }

        // Save the current admin token so we can restore it later.
        $admin_token = $_SESSION['trongatetoken'] ?? ($_COOKIE['trongatetoken'] ?? null);
        if (!$admin_token) {
            redirect('trongate_administrators/login');
        }

        $_SESSION['admin_token_backup']    = $admin_token;
        $_SESSION['impersonating_org_id']  = $org_id;
        $_SESSION['impersonating_org_name'] = $org->organization;

        // Clear the admin token from session (keep it in DB so restore works).
        unset($_SESSION['trongatetoken']);

        // Issue a new session token for the org's trongate_user (user_level_id = 4).
        $this->module('trongate_tokens');
        $token_data = [
            'user_id'     => $org->trongate_user_id,
            'expiry_date' => time() + 3600, // 1-hour impersonation window
        ];
        $this->trongate_tokens->_generate_token($token_data);

        redirect('competitions');
    }

    /**
     * End impersonation and restore the original admin token.
     *
     * @return void
     */
    public function stop_impersonating(): void {
        if (empty($_SESSION['admin_token_backup'])) {
            redirect('trongate_administrators/dashboard');
        }

        // Delete the impersonation token from DB.
        $impersonation_token = $_SESSION['trongatetoken'] ?? null;
        if ($impersonation_token) {
            $params = ['token' => $impersonation_token];
            $sql = 'DELETE FROM trongate_tokens WHERE token = :token';
            $this->model->query_bind($sql, $params);
        }

        // Restore admin token.
        $_SESSION['trongatetoken'] = $_SESSION['admin_token_backup'];
        unset($_SESSION['admin_token_backup'], $_SESSION['impersonating_org_id'], $_SESSION['impersonating_org_name']);

        redirect('trongate_administrators/dashboard');
    }

    /**
     * Redirects to the designated dashboard home page.
     *
     * @return void
     */
    public function go_home(): void {
        redirect($this->dashboard_home);
    }

    /**
     * Ensures that access is allowed.
     *
     * @return string|null Returns a token if access is allowed, otherwise null.
     */
    public function _make_sure_allowed(): ?string {

        //let's assume that only users with a valid token 
        //who are user_level_id = 1 can view
        $this->module('trongate_tokens');
        $token = $this->trongate_tokens->_attempt_get_valid_token(1);

        if ($token === false) {

            if (ENV === 'dev') {
                //automatically give token to user when in dev mode

                //generate trongatetoken for 1st trongate_administrator record on tbl
                $sql = 'select * from trongate_administrators order by id limit 0,1';
                $rows = $this->model->query($sql, 'object');

                if ($rows === false) {
                    redirect(BASE_URL . 'trongate_administrators/missing_tbl_msg');
                } else {
                    $token_params['user_id'] = $rows[0]->trongate_user_id;

                    //start off by clearing all tokens for this user
                    $this->delete_tokens_for_user($token_params['user_id']);

                    //now generate the new token
                    $token_params['expiry_date'] = 86400 + time();
                    $this->module('trongate_tokens');
                    $_SESSION['trongatetoken'] = $this->trongate_tokens->_generate_token($token_params);
                    return $_SESSION['trongatetoken'];
                }
            } else {
                redirect('trongate_administrators/login');
            }
        } else {
            return $token;
        }
    }

    /**
     * Handles user logout by destroying tokens, clearing session and cookie data, and redirecting appropriately.
     *
     * @return void
     */
    public function logout(): void {
        $this->module('trongate_tokens');
        
        // Destroy all tokens for the user
        $this->trongate_tokens->_destroy();
        
        // Unset the session token
        if (isset($_SESSION['trongatetoken'])) {
            unset($_SESSION['trongatetoken']);
        }
        
        // Delete the cookie if it exists
        if (isset($_COOKIE['trongatetoken'])) {
            setcookie('trongatetoken', '', time() - 3600, '/');
        }
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Redirect based on secret_login_segment
        if (isset($this->secret_login_segment)) {
            redirect(BASE_URL);
        } else {
            redirect('trongate_administrators/login');
        }
    }

    /**
     * Checks the availability of a username and validates it against existing usernames.
     *
     * @param string $str The username to be checked.
     * @return string|bool Returns an error message if the username is not available, otherwise returns TRUE.
     */
    public function username_check(string $str): string|bool {
        //NOTE: You may wish to add other rules of your own here! 
        $update_id =  (int) segment(3);
        $result = $this->model->get_one_where('username', $str, 'trongate_administrators');
        $error_msg = 'The username that you submitted is not available.';

        if (gettype($result) === 'object') {
            if (!is_numeric($update_id)) {
                return $error_msg;
            } else {
                $register_id = $result->id;
                if ($update_id !== $register_id) {
                    return $error_msg;
                }
            }
        }

        return true;
    }

    /**
     * Validates the submitted username and password for login authentication.
     *
     * @param string $submitted_username The username submitted for login.
     * @return string|bool Returns an error message (string) if authentication fails, otherwise returns TRUE.
     */
    public function login_check(string $submitted_username): string|bool {
        $submitted_password = post('password');
        $error_msg = 'You did not enter a correct username and/or or password.';

        $result = $this->model->get_one_where('username', $submitted_username, 'trongate_administrators');
        if (gettype($result) === 'object') {
            $hashed_password = $result->password;
            $is_password_good = $this->verify_hash($submitted_password, $hashed_password);
            if ($is_password_good === true) {
                return true;
            }
        }

        return $error_msg;
    }

    /**
     * Renders a template file with provided data.
     *
     * @param array $data The data to be passed into the template.
     * @return void
     */
    private function load_template(array $data): void {
        $file_path = APPPATH . 'modules/trongate_administrators/views/tg_admin_template.php';
        require_once($file_path);
    }

    /**
     * Retrieves the user ID associated with the given token.
     *
     * @param string $token The token associated with the user.
     * @return mixed Returns the user ID if found, otherwise false.
     */
    private function get_my_id(string $token) {
        $params['token'] = $token;
        $sql = 'SELECT trongate_administrators.id
                FROM trongate_users
                INNER JOIN trongate_administrators
                       ON trongate_users.id = trongate_administrators.trongate_user_id
                INNER JOIN trongate_tokens
                       ON trongate_tokens.user_id = trongate_users.id 
                WHERE trongate_tokens.token = :token 
                ORDER BY trongate_tokens.id DESC LIMIT 0,1';
        $result = $this->model->query_bind($sql, $params, 'object');
        if (gettype($result) === 'array') {
            $id = $result[0]->id;
        } else {
            $id = false;
        }
        return $id;
    }

    /**
     * Retrieves data from the database based on the provided update ID.
     *
     * @param int $update_id The ID used to fetch data from the database.
     * @return array|false Returns an array containing fetched data or false if no data is found.
     */
    private function get_data_from_db(int $update_id) {
        $result_obj = $this->model->get_where($update_id);
        if (gettype($result_obj) === 'object') {
            $data = (array) $result_obj;
        } else {
            $data = false;
        }
        return $data;
    }

    /**
     * Retrieves and organizes data from the POST request.
     *
     * @return array Contains username, password, and repeated password data from the POST request.
     */
    private function get_data_from_post(): array {
        $data['username'] = post('username');
        $data['password'] = post('password');
        $data['repeat_password'] = post('repeat_password');
        return $data;
    }

    /**
     * Logs in the user based on the provided username and handles token generation for session or cookie-based authentication.
     *
     * @param string $username The username used for login.
     * @return void
     */
    private function log_user_in(string $username): void {
        $this->module('trongate_tokens');
        $user_obj = $this->model->get_one_where('username', $username);
        $trongate_user_id = $user_obj->trongate_user_id;
        $token_data['user_id'] = $trongate_user_id;

        $remember = post('remember');
        if (($remember === '1') || ($remember === 1)) {
            //set a cookie and remember for 30 days
            $token_data['expiry_date'] = time() + (86400 * 30);
            $token = $this->trongate_tokens->_generate_token($token_data);
            setcookie('trongatetoken', $token, $token_data['expiry_date'], '/');
        } else {
            //user did not select 'remember me' checkbox
            $_SESSION['trongatetoken'] = $this->trongate_tokens->_generate_token($token_data);
        }

        redirect($this->dashboard_home);
    }

    /**
     * Deletes tokens associated with a specific Trongate user and removes expired tokens.
     *
     * @param int $trongate_user_id The ID of the Trongate user.
     * @return void
     */
    private function delete_tokens_for_user(int $trongate_user_id): void {
        $params['user_id'] = $trongate_user_id;
        $sql = 'delete from trongate_tokens where user_id = :user_id';
        $this->model->query_bind($sql, $params);

        //let's delete expired tokens too
        $this->delete_expired_tokens();
    }

    /**
     * Deletes expired tokens from the Trongate tokens table.
     *
     * @return void
     */
    private function delete_expired_tokens(): void {
        $params['nowtime'] = time();
        $sql = 'delete from trongate_tokens where expiry_date<:nowtime';
        $this->model->query_bind($sql, $params);
    }

    /**
     * Hashes a string using the Bcrypt algorithm.
     *
     * @param string $str The string to be hashed.
     * @return string The hashed string.
     */
    private function hash_string(string $str): string {
        $hashed_string = password_hash($str, PASSWORD_BCRYPT, array(
            'cost' => 11
        ));
        return $hashed_string;
    }

    /**
     * Verifies a plain text string against a hashed string.
     *
     * @param string $plain_text_str The plain text string to verify.
     * @param string $hashed_string The hashed string to compare against.
     * @return bool Returns TRUE if the verification is successful, otherwise FALSE.
     */
    private function verify_hash(string $plain_text_str, string $hashed_string): bool {
        $result = password_verify($plain_text_str, $hashed_string);
        return $result; //TRUE or FALSE
    }

}