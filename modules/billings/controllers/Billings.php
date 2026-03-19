<?php
    class Billings extends Trongate {

        private $grace_days = 3; // grace window for past_due (and optionally active) subs
        private static array $product_cache = [];

        ///===================== Public pages =====================///

        function pricing() {
            $data['view_module'] = 'billings';
            $data['view_file'] = 'pricing';
            if(from_trongate_mx()) {
                $this->view('pricing');
            } else {
                $data['headline'] = 'Subscriptions';
                $this->template('public', $data);
            }
        }

        function subscriptions() {
            $data['view_module'] = 'billings';
            $data['view_file'] = 'org_subs';
            if(from_trongate_mx()) {
                $this->view('org_subs');
            } else {
                $data['headline'] = 'Subscribe';
                $this->template('users_area', $data);
            }
        }

        function payment_modal() {
            $data['view_module'] = 'billings';
            $data['view_file'] = 'payment_modal';
            if(from_trongate_mx()) {
                $this->view('payment_modal');
            } else {
                $data['headline'] = 'Checkout';
                $this->template('users_area', $data);
            }
        }
        
        function entry_pay_modal() {
            $comp_id = (int)segment(3);
            $this->module('trongate_tokens');
            $trongate_user_id = $this->trongate_tokens->_get_user_id();
            $sql = "SELECT cn.name as comp_name, cn.location as comp_location, cn.year as comp_year, cn.entry_type, cn.entry_fee, cp.status, cp.billing_charge_id, cp.user_id, cp.id AS participant_id, cu.name as user_name
                    FROM comp_name AS cn
                    JOIN comp_participants AS cp ON cn.id = cp.comp_id
                    JOIN comp_users AS cu ON cp.user_id = cu.id
                    WHERE cu.trongate_user_id = $trongate_user_id
                    AND cn.id = $comp_id
                    LIMIT 1";

            $user_data = $this->model->query($sql, 'object');

            $user_comp = $user_data[0] ?? null;

            if (!$user_comp->billing_charge_id) {

                $data = [
                    'user_id'           => $user_comp->participant_id ?? null,
                    'comp_id'           => $comp_id,
                    'product_id'        => 11, // entry fee product
                    'amount'            => $user_comp->entry_fee ?? 0,
                    'currency'          => 'EUR',
                    'status'            => 'pending'
                ];

                // Insert order
                $order_id = $this->model->insert($data, 'billing_charges');
                $data2 = [
                    'billing_charge_id' => $order_id
                ];
                $this->model->update($user_comp->participant_id, $data2, 'comp_participants');
                $order = 'fee_' . $order_id;

                // Generate payment link using EveryPay
                $payment_link = $this->_get_everypay_link($order, $user_comp->entry_fee ?? 0);
                $_SESSION['payment_link'] = $payment_link;

            } elseif (isset($_SESSION['payment_link'])) {

                // Use existing payment link from session
                $payment_link = $_SESSION['payment_link'];

                if ($payment_link === null || $payment_link === false) {
                    // Fallback: get new link
                    $charge = $this->model->get_one_where('id', $user_comp->billing_charge_id, 'billing_charges');
                    $payment_link = $this->_get_everypay_link($charge->id, $charge->amount);
                }

            } else {

                // Get new payment link for existing order_id
                $charge = $this->model->get_one_where('id', $user_comp->billing_charge_id, 'billing_charges');
                $payment_link = $this->_get_everypay_link($charge->id, $charge->amount);

            }

            $data['user_comp'] = $user_comp;
            $data['payment_link'] = $payment_link;

            $data['view_module'] = 'billings';
            $data['view_file'] = 'entry_pay_modal';

            if(from_trongate_mx()) {
                $this->view('entry_pay_modal', $data);
            } else {
                $data['headline'] = 'Checkout';
                $this->template('users_area', $data);
            }
        }

        public function _get_comp_price(int $confirmed): array {
            if ($confirmed <= 12) {
                return [
                    'tier'   => 'free_12',
                    'amount' => 0.00,
                    'label'  => '1–12 participants (FREE)',
                ];
            }
            if ($confirmed <= 24) {
                return [
                    'tier'   => 'paid_24',
                    'amount' => 29.00,
                    'label'  => '13–24 participants',
                ];
            }
            if ($confirmed <= 50) {
                return [
                    'tier'   => 'paid_50',
                    'amount' => 59.00,
                    'label'  => '25–50 participants',
                ];
            }
            if ($confirmed <= 75) {
                return [
                    'tier'   => 'paid_75',
                    'amount' => 79.00,
                    'label'  => '51–75 participants',
                ];
            }
            if ($confirmed > 75) {
                return [
                    'tier'   => 'paid',
                    'amount' => 99.00,
                    'label'  => 'more than 75 participants',
                ];
            }
             return [
                'tier'   => 'unknown',
                'amount' => 0.00,
                'label'  => 'Unknown participant count',
            ];
        }

        // Ensure the org (by id) can create a new competition now
        // (based on plan limits and any Event Pass credits)
        // If not, set flashdata and redirect away from the create page
        public function ensure_can_create_comp(int $org_id) {

            $sub = $this->_get_active_subscription($org_id); // returns null for free plans
            
            // Derive counting window
            if ($sub) {
                $from = $sub->current_period_start;
                $to   = $sub->current_period_end;
                $limit = $this->_plan_limit($sub->product_code, 'events_per_period'); // e.g., null or 2, etc.
            } else {
                // Free plan: 1 per calendar year (example)
                $from  = date('Y-01-01 00:00:00');
                $to    = date('Y-12-31 23:59:59');
                $limit = 1;
            }

            if ($limit === null) return; // unlimited

            // Count events in the window (exclude archived/canceled)
            $sql = "SELECT COUNT(*) AS c
                    FROM comp_name
                    WHERE organizer_id = :oid
                    AND COALESCE(start_date, created_at) >= :from
                    AND COALESCE(start_date, created_at) <= :to
                    AND status NOT IN ('archived','canceled')";
            $count_obj = $this->model->query_bind($sql, ['oid'=>$org_id,'from'=>$from,'to'=>$to], 'object');
            $count = $count_obj[0]->c ?? 0;

            if ($count < $limit) return;

            // Over limit → try consume an Event Pass credit
            $credits = $this->_get_event_pass_credits($org_id); // e.g., from org_credits
            
            if ($credits > 0) {
                $this->_consume_event_pass_credit($org_id); // do this atomically/transactionally
                return;
            }

            // Block
            set_flashdata('Event limit reached for your plan. Upgrade or use an Event Pass.');
            return false;
        }

        // Get the org's active/effective subscription (if any)
        // Returns null if none (free plan)
        public function _get_active_subscription(int $org_id): ?object {
            $now = date('Y-m-d H:i:s');

            // Fetch most recent subs in relevant statuses (latest period first)
            $sql = "SELECT *
                    FROM billing_subscriptions
                    WHERE organization_id = $org_id
                    AND status IN ('trialing','active','past_due')
                    ORDER BY current_period_end DESC, id DESC
                    LIMIT 5";
            $rows = $this->model->query($sql, 'object');

            if (!$rows) {
                return null;
            }

            foreach ($rows as $row) {
                $in_period = ($row->current_period_start <= $now && $now <= $row->current_period_end);

                // Grace window for past_due (and optionally active that just expired by a few hours)
                $grace_end = date('Y-m-d H:i:s', strtotime($row->current_period_end.' +'.$this->grace_days.' days'));
                $in_grace  = ($now <= $grace_end);

                $is_effective =
                    ($row->status === 'trialing' && $in_period) ||
                    ($row->status === 'active'   && ($in_period || $in_grace)) ||
                    ($row->status === 'past_due' && $in_grace);

                if ($is_effective) {
                    // Attach a few computed helpers (handy in views/guards)
                    $row->in_period = $in_period;
                    $row->in_grace  = !$in_period && $in_grace;
                    $row->days_left = max(0, (int)floor((strtotime($row->current_period_end) - time()) / 86400));
                    $row->term_months = (int)($row->period_months ?? 1); // snapshot on the row
                    return $row;
                }
            }

            return null;
        }

        public function _get_event_pass_credits(int $org_id): int {
            // Count paid Event Pass purchases
            $sql = "SELECT COALESCE(SUM(quantity),0) AS c
                    FROM billing_charges
                    WHERE organizer_id = $org_id
                    AND product_id = 'event_pass'
                    AND status = 'paid'";
            $paid_obj = $this->model->query($sql, 'object');
            $paid = $paid_obj[0]->c ?? 0;

            $sql = "SELECT COUNT(*) AS c
                FROM billing_pass_uses
                WHERE organization_id = $org_id";
            $used = $this->model->query($sql, 'object');
            $used = $used[0]->c ?? 0;

            return max(0, (int)$paid - (int)$used);
        }

        public function _consume_event_pass_credit(int $org_id, ?int $competition_id = null, ?int $used_by_account = null): bool {

                // Lock one PAID, unused event_pass charge
                $sql = "SELECT c.id
                    FROM billing_charges c
                    LEFT JOIN billing_pass_uses u ON u.charge_id = c.id
                    WHERE c.organization_id = :org
                    AND c.product_code = 'event_pass'
                    AND c.status = 'paid'
                    AND u.id IS NULL
                    ORDER BY c.id ASC
                    LIMIT 1";
                $row = $this->model->query_bind($sql,['org'=>$org_id], 'object');

                if (!$row) return false; // none available

                $this->model->insert('billing_pass_uses', [
                    'organization_id' => $org_id,
                    'charge_id'       => (int)$row[0]->id,
                    'competition_id'  => $competition_id,
                    'used_by_account' => $used_by_account
                ]);
                return true;
        }

        public function _plan_limit(string $product_code, string $limitKey, ?int $default = null): ?int{
            $product = $this->_get_product_by_code($product_code);
            if (!$product) {
                return $default;
            }

            $features = $this->_decode_features($product->features_json);

            // 1) Try direct fetch (supports "events_per_period" or "limits.events_per_period")
            $val = $this->_array_get_dot($features, $this->normalize_key($limitKey));

            // 2) If not found, try synonyms/inference (month/year vs period)
            if ($val === null) {
                $val = $this->_infer_limit_from_synonyms(
                    $features,
                    $this->_normalize_key($limitKey),
                    (int)($product->period_months ?? 1)
                );
            }

            // 3) Normalize: null or <=0 => unlimited (NULL)
            if ($val === null) {
                return $default; // fall back to provided default if you want a hard value
            }
            if (is_numeric($val)) {
                $n = (int)$val;
                return ($n > 0) ? $n : null;
            }

            // Strings like "unlimited" → NULL
            if (is_string($val) && strtolower($val) === 'unlimited') {
                return null;
            }

            // Otherwise, fallback
            return $default;
        }

        /* ===================== Helpers ===================== */

        /** Load product once per request. Expects code, period_months, features_json columns. */
        private function _get_product_by_code(string $code): ?object {
            if (isset(self::$product_cache[$code])) {
                return self::$product_cache[$code];
            }
            $sql = "SELECT code, period_months, features_json
                    FROM billing_products
                    WHERE code = :code AND active = 1
                    LIMIT 1";
            $rows = $this->model->query_bind($sql, ['code' => $code], 'object');
            $row = $rows[0] ?? null;
            self::$product_cache[$code] = $row ?: null;
            return $row ?: null;
        }

        /** Decode features_json safely to array */
        private function _decode_features(?string $json): array {
            if (!$json) return [];
            $arr = json_decode($json, true);
            return is_array($arr) ? $arr : [];
        }

        /** Support either "events_per_period" or "limits.events_per_period" */
        private function _normalize_key(string $key): string {
            // If key already has a dot, assume full path; else prefix with "limits."
            return (strpos($key, '.') !== false) ? $key : "limits.$key";
        }

        /** Dot-path getter: array_get_dot($arr, 'a.b.c') */
        private function _array_get_dot(array $arr, string $path){
            $parts = explode('.', $path);
            $cur = $arr;
            foreach ($parts as $p) {
                if (!is_array($cur) || !array_key_exists($p, $cur)) {
                    return null;
                }
                $cur = $cur[$p];
            }
            return $cur;
        }

        /**
         * Infer missing limits from common synonyms:
         * - If asking for limits.events_per_period but only year/month is present,
         *   derive using product->period_months (1 = month, 12 = year).
         */
        private function infer_limit_from_synonyms(array $features, string $normKey, int $period_months): ?int{
            // Only handle the common "events_per_period" case
            if ($normKey !== 'limits.events_per_period') {
                return null;
            }

            $year = $this->array_get_dot($features, 'limits.events_per_year');
            $month = $this->array_get_dot($features, 'limits.events_per_month');

            // Normalize year/month values
            $year = (is_numeric($year) && (int)$year > 0) ? (int)$year : null;
            $month = (is_numeric($month) && (int)$month > 0) ? (int)$month : null;

            if ($period_months >= 12) {
                // Annual plan period
                if ($year !== null) return $year;
                if ($month !== null) return $month * 12;
                return null;
            } else {
                // Monthly (or other N-month) plan period
                if ($month !== null && $period_months === 1) return $month;
                if ($year !== null) {
                    // Best-effort downscale: spread yearly cap over months (ceil so small caps don't become 0)
                    $approx = (int)ceil($year / max(1, (12 / max(1, $period_months))));
                    return max(1, $approx);
                }
                return null;
            }
        }

        /* ================= Optional convenience ================= */

        /**
         * Convenience: get a limit for an org's *effective* plan.
         * Falls back to a free product code if no subscription is active.
         */
        public function _plan_limit_for_org(int $org_id, string $limitKey, ?int $default = null, string $free_code = 'starter_free'): ?int{
            $sub = $this->_get_active_subscription($org_id); // your existing method
            if ($sub) {
                return $this->_plan_limit($sub->product_code, $limitKey, $default);
            }
            return $this->_plan_limit($free_code, $limitKey, $default);
        }

        //---------------- EVERYPAY INTEGRATION POINTS -----//

        function process_order() {

            $comp_id = (int) segment(3);
            $comp = $this->model->get_where($comp_id, 'comp_name');

            $sql = "SELECT COUNT(*) AS confirmed
                    FROM comp_participants
                    WHERE comp_id = ? 
                    AND status = 'confirmed'";
            $row = $this->model->query_bind($sql, [$comp_id], 'object');

            $confirmed = (int) $row[0]->confirmed;

            $pricing = $this->_get_comp_price($confirmed);

            if (!$comp->billing_charge_id) {

                $product = $this->model->get_one_where('code', $pricing['tier'], 'billing_products');

                $data = [
                    'organizer_id'      => $comp->organizer_id ?? null,
                    'comp_id'           => $comp->id,
                    'product_id'        => $product->id,
                    'amount'            => $product->price,
                    'currency'          => 'EUR',
                    'participants_count'=> $confirmed,
                    'status'            => 'pending'
                ];

                // Insert order
                $order_id = $this->model->insert($data, 'billing_charges');
                $data2 = [
                    'billing_tier'      => $product->code,
                    'billing_charge_id' => $order_id,
                    'billing_status'    => 'pending',
                    'billing_participants_locked'=> $confirmed
                ];
                $this->model->update($comp_id, $data2, 'comp_name');

                // Generate payment link using EveryPay
                $payment_link = $this->_get_everypay_link($order_id, $product->price);
                $_SESSION['payment_link'] = $payment_link;

            } elseif (isset($_SESSION['payment_link'])) {

                // Use existing payment link from session
                $payment_link = $_SESSION['payment_link'];

            } else {

                // Get new payment link for existing order_id
                $charge = $this->model->get_one_where('id', $comp->billing_charge_id, 'billing_charges');
                $payment_link = $this->_get_everypay_link($charge->id, $charge->amount);

            }

            $data['payment_link'] = $payment_link;
            $data['comp'] = $comp;
            $data['pricing'] = $pricing;

            $this->view('payment_modal', $data);

            // $this->process_order();

        }

        public function _get_everypay_link($order_id, $total_amount) {

            $api_username = constant('EVERYPAY_API_USERNAME');
            $account_name = constant('EVERYPAY_ACCOUNT_NAME');
            $auth_key = constant('EVERYPAY_AUTH_KEY');
            $api_url = constant('EVERYPAY_URL');

            $everypay_url = "{$api_url}oneoff";
        
            $amount = number_format($total_amount, 2, '.', '');

            $payload = [
                "api_username" => $api_username,
                "account_name" => $account_name,
                "amount" => $amount,
                "order_reference" => "SP-ODR-25-$order_id",
                "nonce" => uniqid(),
                "timestamp" => date('c'),
                "customer_url" => BASE_URL . "billings/payment_result/"
            ];

            $ch = curl_init($everypay_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($api_username . ':' . $auth_key),
                'Content-Type: application/json'
            ]);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $response = curl_exec($ch);
            curl_close($ch);
        
            $result = json_decode($response, true);
        
            if (isset($result['payment_link'])) {
                return $result['payment_link'];
            } else {
                // Optional debug log
                // file_put_contents('everypay_errors.log', $response . PHP_EOL, FILE_APPEND);
                return false;
            }
        }

        public function payment_result() {
            $payment_ref = $_GET['payment_reference'] ?? null;

            if (!$payment_ref) {
                echo "<h2>Error</h2><p>Missing payment reference.</p>";
                return;
            }
        
            $api_username = constant('EVERYPAY_API_USERNAME');
            $auth_key = constant('EVERYPAY_AUTH_KEY');
            $api_url = constant('EVERYPAY_URL');
        
            // ✅ FIX: Add api_username and detailed to the URL
            $url = "{$api_url}{$payment_ref}?api_username={$api_username}&detailed=true";

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Basic ' . base64_encode($api_username . ':' . $auth_key),
                    'Content-Type: application/json'
                ]
            ]);
        
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                echo "<h2>cURL Error:</h2><p>$err</p>";
                return;
            }
        
            $result = json_decode($response, true);
        
            if (
                !$result ||
                !isset($result['payment_state'], $result['payment_reference'], $result['order_reference'])
            ) {
                echo "<h2>Error</h2><p>Invalid response from EveryPay.</p>";
                return;
            }
        
            $order_reference = $result['order_reference'];
            $payment_state = $result['payment_state'];
        
            if ($payment_state === 'settled') {

                // Data to update
                $data['status'] = 'paid';
                $data['payment_ref'] = $payment_ref;
                $data['paid_at'] = date('Y-m-d H:i:s');

                $order_id = (int) str_replace('SP-ODR-25-', '', $order_reference); // gives id

                // Update the customer record in the 'billing_charges' table
                $this->model->update($order_id, $data, 'billing_charges');
                
                $order = $this->model->get_one_where('id', $order_id, 'billing_charges');
                if ($order->organizer_id != null) {
                    // if organizer_id is set, it's a competition payment
                    $comp_id = $order->comp_id;
                    // Update the competition record in the 'comp_name' table
                    $data2 = [
                        'billing_status' => 'paid',
                        'billing_participants_locked' => $order->participants_count
                    ];
                    $this->model->update($comp_id, $data2, 'comp_name');

                    redirect('heats/generate_heats/' . $comp_id);

                } else {
                    // Otherwise, it's a entry fee payment
                    $user_id = $order->user_id;
                    // Update the participant status as needed
                    $data2 = [
                        'status' => 'paid'
                    ];
                    $this->model->update($user_id, $data2, 'comp_participants');
                    // (implementation depends on your schema)

                    redirect('users');

                }

            } else {
                // Data to update if not 'settled'
                $data['status'] = 'failed';
                $data['payment_ref'] = $payment_ref;
                $order_id = (int) str_replace('SP-ODR-25-', '', $order_reference); // gives id

                // Update the customer record in the 'billing_charges' table
                $this->model->update($order_id, $data, 'billing_charges');

                echo "<h2>Payment Failed</h2><p>Status: $payment_state</p>";
            }
        }
        
        public function payment() { 
            $payment_ref = $_GET['payment_reference'] ?? null;
            $order_ref = (int) str_replace('SP-ODR-26-', '', $_GET['order_reference'] ?? '0');
            if (!$payment_ref) {
                echo "<h2>Error</h2><p>Missing payment reference.</p>";
                return;
            }

            echo "<h2>Payment Result</h2><p>Payment Reference: $payment_ref</p><p>Order Reference: $order_ref</p>";
            return;

            $data['order'] = $this->model->get_one_where('id', $order_id, 'billing_charges');

            $sql = "
                SELECT p.name, p.image, i.quantity, i.price
                FROM products_orders_items i
                JOIN products p ON i.product_id = p.id
                WHERE i.order_id = ?";
            
            $data['items'] = $this->model->query_bind($sql, [$order_id], 'object');

            // Get the EveryPay iframe URL
            $data['payment_link'] = $_SESSION['payment_link'] ?? null;
        
            $data['view_file'] = 'payment_page';
            $this->template('shop_area', $data);
        }
    }