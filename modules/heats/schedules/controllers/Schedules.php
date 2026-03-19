<?php
class Schedules extends Trongate {

    public function clear_schedule() {
        $comp_id = (int) segment(3);

        $p = json_decode(file_get_contents('php://input'), true) ?? [];
        $include_locked = (bool)($p['include_locked'] ?? false);

        $whereLocked = $include_locked ? "" : "AND (is_locked = 0 OR is_locked IS NULL)";

        $sql = "
            UPDATE comp_heats
            SET start_time = NULL,
                end_time = NULL,
                status = 'pending'
            WHERE comp_id = ?
            AND status NOT IN ('running','finished')
            $whereLocked
        ";

        $this->model->query_bind($sql, [$comp_id]);

        set_flashdata('Schedules successfully cleared');

        return;
    }

    // Modals and views for heat scheduling and time management
    public function auto_schedule_modal() {
        $comp_id = (int) segment(3);
        $this->view('auto_schedule_modal', ['comp_id' => $comp_id]);
    }

    public function set_time_modal() {
        $comp_id = (int) segment(3);
        $heat_id = (int) segment(4);
        $this->view('set_time_modal', ['comp_id' => $comp_id, 'heat_id' => $heat_id]);
    }

    public function reorder() {
        $comp_id = (int) segment(3);

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $order = $payload['order'] ?? [];

        if (!is_array($order) || count($order) < 1) {
            return $this->_json(['ok' => false, 'error' => 'Empty order'], 400);
        }

        // sanitize ids
        $order = array_values(array_filter(array_map('intval', $order), fn($v) => $v > 0));

        if (count($order) !== count(array_unique($order))) {
            return $this->_json(['ok' => false, 'error' => 'Duplicate heat ids in order'], 400);
        }

        $ids_list = implode(',', $order);

        // Load heat info (validates ids belong to comp)
        $rows = $this->model->query_bind("
            SELECT id, status, sort_order, `round`, division
            FROM comp_heats
            WHERE comp_id = ?
            AND id IN ($ids_list)
        ", [$comp_id], 'array');

        if (count($rows) !== count($order)) {
            return $this->_json(['ok' => false, 'error' => 'One or more heats not found for this comp'], 400);
        }

        // Map id => heat row
        $heatById = [];
        foreach ($rows as $r) $heatById[(int)$r['id']] = $r;

        // ---------- 1) finished/running cannot move ----------
        $fixedStatuses = ['running', 'finished'];

        // fixed order currently in DB (by sort_order)
        $fixedCurrent = [];
        foreach ($rows as $r) {
            if (in_array($r['status'], $fixedStatuses, true)) $fixedCurrent[] = (int)$r['id'];
        }
        usort($fixedCurrent, fn($a, $b) => ((int)$heatById[$a]['sort_order']) <=> ((int)$heatById[$b]['sort_order']));

        // fixed order in submitted list
        $fixedNew = [];
        foreach ($order as $id) {
            $st = $heatById[$id]['status'];
            if (in_array($st, $fixedStatuses, true)) $fixedNew[] = (int)$id;
        }

        // must be identical (same ids, same order)
        if ($fixedNew !== $fixedCurrent) {
            return $this->_json(['ok' => false, 'error' => 'Cannot move running/finished heats'], 409);
        }

        // RECOMMENDED: finished/running must stay as a prefix (first N items)
        $fixedCount = count($fixedCurrent);
        if ($fixedCount > 0) {
            $prefix = array_slice($order, 0, $fixedCount);
            if ($prefix !== $fixedCurrent) {
                return $this->_json(['ok' => false, 'error' => 'Finished/running heats must stay at top'], 409);
            }
        }

        // ---------- 2) per-division stage order ----------
        // Adjust these keys to match your actual `round` strings in DB:
        $rank = [
            'Round 1'      => 1,
            'Repechage 1'  => 2,
            'Round 2'      => 3,
            'Repechage 2'  => 4,
            'Final'        => 5,
        ];

        $lastRankByDivision = [];

        foreach ($order as $id) {
            $h = $heatById[$id];

            $div = trim((string)($h['division'] ?? ''));
            if ($div === '') {
                return $this->_json(['ok'=>false,'error'=>'Missing division value on comp_heats'], 500);
            }

            $roundVal = $h['round'];

            // If you ever store numeric stages, allow them too:
            if (is_numeric($roundVal)) {
                $rnk = (int) $roundVal;
            } else {
                $rnk = $rank[$roundVal] ?? null;
            }

            if ($rnk === null) {
                return $this->_json(['ok'=>false,'error'=>'Unknown round value: '.$roundVal], 400);
            }

            $prev = $lastRankByDivision[$div] ?? 0;
            if ($rnk < $prev) {
                return $this->_json([
                    'ok' => false,
                    'error' => "Invalid order: division '{$div}' moved backwards at heat {$id} ({$roundVal})"
                ], 409);
            }
            $lastRankByDivision[$div] = $rnk;
        }

        // ---------- 3) apply sort_order exactly as submitted ----------
        $caseParts = [];
        foreach ($order as $i => $heat_id) {
            $caseParts[] = "WHEN {$heat_id} THEN {$i}";
        }
        $caseSql = implode(' ', $caseParts);

        $this->model->query("START TRANSACTION");
        try {
            $sql = "
                UPDATE comp_heats
                SET sort_order = CASE id {$caseSql} END
                WHERE comp_id = ?
                AND id IN ($ids_list)
            ";
            $this->model->query_bind($sql, [$comp_id]);

            $this->model->query("COMMIT");
            return $this->_json(['ok' => true, 'updated' => count($order)]);
        } catch (Exception $e) {
            $this->model->query("ROLLBACK");
            return $this->_json(['ok' => false, 'error' => 'Reorder failed'], 500);
        }
    }

    /**
     * POST /surfpan/heats-schedules/auto_schedule/{comp_id}
     * Body:
     * {
     *   "start_local": "2026-02-15T09:00",
     *   "default_duration_min": 20,
     *   "changeover_min": 3,
     *   "mode": "unscheduled_only" | "overwrite",
     *   "snap_min": 5,
     *   "breaks": [
     *      {"start_local":"2026-02-15T12:30","end_local":"2026-02-15T13:00"}
     *      // or {"start_utc":"2026-02-15 10:30:00","end_utc":"2026-02-15 11:00:00"}
     *   ]
     * }
     */
    public function auto_schedule() {
        $comp_id = (int) segment(3);

        $this->validation->set_rules('start_time', 'Start Time', 'required|valid_datetime_local');
        $this->validation->set_rules('duration', 'Default Duration', 'required|integer|greater_than[0]');
        $this->validation->set_rules('changeover', 'Changeover Time', 'required|integer|min_length[0]');
        $this->validation->set_rules('mode', 'Mode', 'required');
        $this->validation->set_rules('snap_min', 'Snap Time', 'integer|min_length[0]');
        
        $result = $this->validation->run();

        if (!$result) {
            return $this->_json(['ok'=>false,'error'=>$this->validation->error_string()], 400);
        }
        // $p = json_decode(file_get_contents('php://input'), true) ?? [];

        $start_local = post('start_time') ?? null;

        $default_duration = post('duration', true) ?? 20;
        $changeover = post('changeover', true) ?? 3;
        $mode = post('mode', true) ?? 'unscheduled_only'; // or overwrite
        $snap_min = post('snap_min', true) ?? 0;

        $orgTz = $this->_get_org_timezone_by_comp($comp_id);

        $start_utc_sql = $this->_local_to_utc_sql($start_local, $orgTz);
        
        if (!$start_utc_sql) return $this->_json(['ok'=>false,'error'=>'Invalid start_local'], 400);

        $breaks = $this->_normalize_breaks($p['breaks'] ?? [], $orgTz);

        $cursor = new DateTime($start_utc_sql, new DateTimeZone('UTC'));

        $heats = $this->model->query_bind("
            SELECT id, status, sort_order, start_time, end_time, duration_min, is_locked
            FROM comp_heats
            WHERE comp_id = ?
            ORDER BY sort_order ASC, id ASC
        ", [$comp_id], 'array');

        $updated = 0;

        $this->model->query("START TRANSACTION");
        try {
            foreach ($heats as $h) {
                $id = (int)$h['id'];
                $status = (string)$h['status'];

                // Never touch running/finished heats
                if ($status === 'running' || $status === 'finished') {
                    // keep cursor moving forward if end_time exists
                    if (!empty($h['end_time'])) {
                        $end = $this->_dt_utc($h['end_time']);
                        if ($end && $end > $cursor) $cursor = clone $end;
                        $cursor->modify("+{$changeover} minutes");
                    }
                    continue;
                }

                $has_time = !empty($h['start_time']) && !empty($h['end_time']);
                $locked = ((int)($h['is_locked'] ?? 0) === 1);

                // locked: keep existing
                if ($locked && $has_time) {
                    $end = $this->_dt_utc($h['end_time']);
                    if ($end && $end > $cursor) $cursor = clone $end;
                    $cursor->modify("+{$changeover} minutes");
                    continue;
                }

                // unscheduled_only: keep existing
                if ($mode === 'unscheduled_only' && $has_time) {
                    $end = $this->_dt_utc($h['end_time']);
                    if ($end && $end > $cursor) $cursor = clone $end;
                    $cursor->modify("+{$changeover} minutes");
                    continue;
                }

                $dur = (int)($h['duration_min'] ?? 0);
                if ($dur <= 0) $dur = $default_duration;

                if ($snap_min > 0) $cursor = $this->_snap_dt($cursor, $snap_min);

                [$start, $end] = $this->_fit_breaks($cursor, $dur, $breaks, $snap_min);

                $this->model->update($id, [
                    'start_time' => $start->format('Y-m-d H:i:s'),
                    'end_time'   => $end->format('Y-m-d H:i:s'),
                    'status'     => 'scheduled',
                    'duration_min' => $dur
                ], 'comp_heats');

                $updated++;

                $cursor = clone $end;
                $cursor->modify("+{$changeover} minutes");
            }

            $this->model->query("COMMIT");

            set_flashdata('Auto Schedule successful');
            return $this->_json(['ok'=>true,'updated'=>$updated]);
        } catch (Exception $e) {
            $this->model->query("ROLLBACK");

            set_flashdata('Auto Schedule failed');
            return $this->_json(['ok'=>false,'error'=>'Auto schedule failed'], 500);
        }
    }

    /**
     * POST /surfpan/heats-schedules/reflow/{comp_id}/{heat_id}
     * Recomputes times from pivot heat (and after) based on sort_order.
     * Body (optional):
     * {
     *   "start_local": "2026-02-15T10:15",   // optional override cursor start (organizer tz)
     *   "default_duration_min": 20,
     *   "changeover_min": 5,
     *   "snap_min": 5,
     *   "breaks": [...]
     * }
     */
    public function reflow($comp_id, $pivot_id, $start_local = null, $default_duration = 20, $changeover = 5, $snap_min = 0) {

        $orgTz = $this->_get_org_timezone_by_comp($comp_id);
        $breaks = $this->_normalize_breaks($p['breaks'] ?? [], $orgTz);

        $pivot = $this->model->query_bind("
            SELECT id, sort_order, status, start_time
            FROM comp_heats
            WHERE comp_id = ? AND id = ?
            LIMIT 1
        ", [$comp_id, $pivot_id], 'object');

        if (!$pivot) return $this->_json(['ok'=>false,'error'=>'Pivot heat not found'], 404);

        // Determine cursor start:
        // 1) if start_local provided => use it
        // 2) else use previous heat end_time + changeover
        // 3) else fall back to pivot start_time (if exists)
        $cursor = null;

        if (!empty($start_local)) {
            $start_utc_sql = $this->_local_to_utc_sql($start_local, $orgTz);
            if (!$start_utc_sql) return $this->_json(['ok'=>false,'error'=>'Invalid start_local'], 400);
            $cursor = new DateTime($start_utc_sql, new DateTimeZone('UTC'));
        } else {
            $prev = $this->model->query_bind("
                SELECT end_time
                FROM comp_heats
                WHERE comp_id = ?
                AND sort_order < ?
                AND end_time IS NOT NULL
                ORDER BY sort_order DESC
                LIMIT 1
            ", [$comp_id, (int)$pivot->sort_order], 'object');

            if ($prev && !empty($prev->end_time)) {
                $cursor = $this->_dt_utc($prev->end_time);
                if ($cursor) $cursor->modify("+{$changeover} minutes");
            } elseif (!empty($pivot->start_time)) {
                $cursor = $this->_dt_utc($pivot->start_time);
            }
        }

        if (!$cursor) {
            return $this->_json([
                'ok'=>false,
                'error'=>'Cannot determine reflow start. Provide start_local or set previous heat end_time.'
            ], 409);
        }
        $heats = $this->model->query_bind("
            SELECT id, status, sort_order, start_time, end_time, duration_min, is_locked
            FROM comp_heats
            WHERE comp_id = ? AND sort_order >= ?
            ORDER BY sort_order ASC, id ASC
        ", [$comp_id, (int)$pivot->sort_order], 'array');

        $updated = 0;

        $this->model->query("START TRANSACTION");
        try {
            foreach ($heats as $h) {
                $id = (int)$h['id'];
                $status = (string)$h['status'];

                if ($status === 'running' || $status === 'finished') {
                    if (!empty($h['end_time'])) {
                        $end = $this->_dt_utc($h['end_time']);
                        if ($end && $end > $cursor) $cursor = clone $end;
                        $cursor->modify("+{$changeover} minutes");
                    }
                    continue;
                }

                $has_time = !empty($h['start_time']) && !empty($h['end_time']);
                $locked = ((int)($h['is_locked'] ?? 0) === 1);

                if ($locked && $has_time) {
                    $end = $this->_dt_utc($h['end_time']);
                    if ($end && $end > $cursor) $cursor = clone $end;
                    $cursor->modify("+{$changeover} minutes");
                    continue;
                }

                $dur = (int)($h['duration_min'] ?? 0);
                if ($dur <= 0) $dur = $default_duration;

                if ($snap_min > 0) $cursor = $this->_snap_dt($cursor, $snap_min);

                // Fit breaks and get final start/end for this heat
                // implement later: if heat has existing time, try to fit it in breaks first before assigning new times
                // [$start, $end] = $this->_fit_breaks($cursor, $dur, $breaks, $snap_min);

                $start = clone $cursor;
                $end = clone $start;
                $end->modify("+{$dur} minutes");
                // For now just assign sequentially without break fitting, to avoid accidentally increasing durations if breaks are tight. Implement break fitting later with option to skip heats that can't fit in breaks without increasing duration.

                $data = [
                    'start_time' => $start->format('Y-m-d H:i:s'),
                    'end_time'   => $end->format('Y-m-d H:i:s'),
                    'status'     => 'scheduled',
                    'duration_min' => $dur
                ];
                $this->model->update($id, $data, 'comp_heats');

                $updated++;

                $cursor = clone $end;
                $cursor->modify("+{$changeover} minutes");
            }

            $this->model->query("COMMIT");
            return $this->_json(['ok'=>true,'updated'=>$updated]);
        } catch (Exception $e) {
            $this->model->query("ROLLBACK");
            return $this->_json(['ok'=>false,'error'=>'Reflow failed'], 500);
        }
    }

    /**
     * POST /surfpan/heats-schedules/set_time/{comp_id}/{heat_id}
     * Body:
     * {
     *   "start_local": "2026-02-15T10:40",
     *   "duration_min": 20,
     *   "changeover_min": 3,         // optional (client uses if it will call reflow)
     *   "reflow_after": true         // optional (client may call reflow)
     * }
     */
    public function set_time() {
        $comp_id = (int) segment(3);
        $heat_id = (int) segment(4);

        $this->validation->set_rules('start_time', 'Start Time', 'required|valid_datetime_local');
        $this->validation->set_rules('duration', 'Default Duration', 'required|integer|greater_than[0]');
        $this->validation->set_rules('changeover', 'Changeover Time', 'required|integer|min_length[0]');
        
        $result = $this->validation->run();

        if (!$result) {
            return $this->_json(['ok'=>false,'error'=>$this->validation->error_string()], 400);
        }

        $start_time = post('start_time') ?? null;
        $dur = post('duration', true) ?? 20;
        $changeover = post('changeover', true) ?? 3;
        $snap_min = post('snap_min', true) ?? 0;
        $reflow_after = (bool)(post('reflow_after') ?? false);

        if (!$start_time || $dur <= 0) {
            return $this->_json(['ok'=>false,'error'=>'start_time and duration required'], 400);
        }
        $orgTz = $this->_get_org_timezone_by_comp($comp_id);
        
        $start_utc_sql = $this->_local_to_utc_sql($start_time, $orgTz);
        if (!$start_utc_sql) return $this->_json(['ok'=>false,'error'=>'Invalid start_time'], 400);

        // Ensure heat exists and belongs to comp
        $heat = $this->model->query_bind("
            SELECT id, status
            FROM comp_heats
            WHERE comp_id = ? AND id = ?
            LIMIT 1
        ", [$comp_id, $heat_id], 'object');

        if (!$heat) return $this->_json(['ok'=>false,'error'=>'Heat not found'], 404);

        if ($heat->status === 'running' || $heat->status === 'finished') {
            return $this->_json(['ok'=>false,'error'=>'Cannot set time for running/finished heat'], 409);
        }

        $start = new DateTime($start_utc_sql, new DateTimeZone('UTC'));
        $end = clone $start;
        $end->modify("+{$dur} minutes");

        $this->model->update($heat_id, [
            'start_time' => $start->format('Y-m-d H:i:s'),
            'end_time'   => $end->format('Y-m-d H:i:s'),
            'duration_min' => $dur,
            'status'     => 'scheduled'
        ], 'comp_heats');

        if ($reflow_after) {
            // Call reflow for heats after this one
            $this->reflow($comp_id, $heat_id, $start_time, $dur, $changeover, $snap_min);
            set_flashdata('All heat times updated accordingly.');
            return; 
        }

        set_flashdata('Heat time updated');

        return;
    }

    /* ---------------- Helpers ---------------- */

    // Get organizer timezone by comp_id, fallback to UTC if not set or invalid
    private function _get_org_timezone_by_comp(int $comp_id): string {

        // You said: comp_name has comp_id and organizer_id
        $sql = "SELECT o.timezone
                FROM comp_name c
                JOIN comp_organizations o ON o.id = c.organizer_id
                WHERE c.id = ?
                LIMIT 1";

        $row = $this->model->query_bind($sql, [$comp_id], 'object');
        
        $tz = $row[0]->timezone ?? '';

        return $this->_safe_tz($tz);
    }

    // Validate timezone string, return valid TZ or fallback to UTC
    private function _safe_tz(string $tz): string {
        $tz = trim($tz);
        if ($tz === '') return 'UTC';
        try {
            new DateTimeZone($tz);
            return $tz;
        } catch (Exception $e) {
            return 'UTC';
        }
    }

    // Converts datetime-local "YYYY-MM-DDTHH:MM" in organizer TZ -> UTC SQL "Y-m-d H:i:s"
    private function _local_to_utc_sql(string $localValue, string $orgTz): ?string {
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $localValue, new DateTimeZone($orgTz));
        if (!$dt) return null;
        $dt->setTimezone(new DateTimeZone('UTC'));
        return $dt->format('Y-m-d H:i:s');
    }

    // Parse SQL datetime string in UTC and return DateTime object in UTC timezone; return null if invalid
    private function _dt_utc(string $utcSql): ?DateTime {
        try {
            return new DateTime($utcSql, new DateTimeZone('UTC'));
        } catch (Exception $e) {
            return null;
        }
    }

    // Snap cursor UP to nearest N minutes (UTC)
    private function _snap_dt(DateTime $dt, int $snapMin): DateTime {
        if ($snapMin <= 0) return $dt;
        $t = $dt->getTimestamp();
        $snap = $snapMin * 60;
        $t2 = (int)(ceil($t / $snap) * $snap);
        $out = new DateTime('@'.$t2);
        $out->setTimezone(new DateTimeZone('UTC'));
        return $out;
    }

    // Accepts breaks in local or utc and returns list of ['start'=>DateTime,'end'=>DateTime] in UTC
    private function _normalize_breaks($breaks, string $orgTz): array {
        if (!is_array($breaks)) return [];

        $out = [];
        foreach ($breaks as $b) {
            if (!is_array($b)) continue;

            $bs = null; $be = null;

            if (!empty($b['start_utc']) && !empty($b['end_utc'])) {
                $bs = $this->_dt_utc($b['start_utc']);
                $be = $this->_dt_utc($b['end_utc']);
            } elseif (!empty($b['start_local']) && !empty($b['end_local'])) {
                $s = $this->_local_to_utc_sql($b['start_local'], $orgTz);
                $e = $this->_local_to_utc_sql($b['end_local'], $orgTz);
                if ($s && $e) {
                    $bs = $this->_dt_utc($s);
                    $be = $this->_dt_utc($e);
                }
            }

            if ($bs && $be && $be > $bs) {
                $out[] = ['start'=>$bs, 'end'=>$be];
            }
        }

        // sort by start
        usort($out, fn($a,$b) => $a['start'] <=> $b['start']);
        return $out;
    }

    // Ensure [start, end] doesn't overlap any break window; if overlap => push start to break end and retry
    private function _fit_breaks(DateTime $cursor, int $durMin, array $breaks, int $snapMin = 0): array {
        $start = clone $cursor;

        for ($i=0; $i<20; $i++) {
            $end = clone $start;
            $end->modify("+{$durMin} minutes");

            $moved = false;

            if (!empty($breaks)) {
                foreach ($breaks as $b) {
                    $bs = $b['start'];
                    $be = $b['end'];

                    // overlap if start < break_end AND end > break_start
                    if ($start < $be && $end > $bs) {
                        $start = clone $be;
                        if ($snapMin > 0) $start = $this->_snap_dt($start, $snapMin);
                        $moved = true;
                        break;
                    }
                }
            }

            if (!$moved) {
                $end = clone $start;
                $end->modify("+{$durMin} minutes");
                return [$start, $end];
            }
        }

        $end = clone $start;
        $end->modify("+{$durMin} minutes");
        return [$start, $end];
    }

    // Output JSON response and exit
    private function _json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

}
