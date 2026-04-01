<?php
    class Results extends Trongate {
        
        public function show() {
            $comp_id = (int)segment(3);
            $show_only = (string)segment(4);

            $show_only_value = out(str_replace('_', ' ', $show_only));
            $show_only = $this->model->get_one_where('name', $show_only_value, 'comp_divisions')->id ?? '';

            $data['comp_id'] = $comp_id;
            $data['divisions'] = $this->heats->_get_unique_divisions($comp_id);
            if (empty($show_only)) {
                $show_only = $data['divisions'][0];
            }
            $data['results'] = $this->get_final_standings($comp_id, $show_only_value);
            $data['active_division'] = $this->model->get_one_where('name', $show_only_value, 'comp_divisions')->name ?? '';
            $data['view_file'] = 'show_all';
            $data['participant_count'] = $this->model->count_rows('comp_id', $comp_id, 'comp_participants');;
            $data['comp_data'] = $this->heats->_get_comp_data($comp_id);
            $data['divisions_count'] = count($data['divisions']);
            $this->template('public', $data);
        }

        public function show_all() {
            $results = $this->get_final_standings(25);
            $this->view('show_all');
        }

        public function get_final_standings($comp_id, $division = null) {
            $params = ['comp_id' => $comp_id];
            $divFilter = '';

            if (!empty($division)) {
                //$division = $this->model->get_one_where('name', $division, 'comp_divisions')->name;
                $divFilter = ' AND h.division = :division';
                $params['division'] = $division;
            }

            $sql = "WITH top2 AS (
                    SELECT
                        js.heat_id,
                        js.participant_id,
                        js.avg_score,
                        ROW_NUMBER() OVER (
                        PARTITION BY js.heat_id, js.participant_id
                        ORDER BY js.avg_score DESC
                        ) AS rn
                    FROM comp_wave_averages js
                    ),
                    heat_totals AS (
                    SELECT
                        heat_id,
                        participant_id,
                        COALESCE(SUM(avg_score), 0) AS total_score,
                        MAX(avg_score) AS best_wave
                    FROM top2
                    WHERE rn <= 2
                    GROUP BY heat_id, participant_id
                    ),
                    best_rows AS (
                    SELECT
                        h.comp_id,
                        h.division              AS division_name,
                        h.id                    AS heat_id,
                        h.round,
                        h.heat_number,
                        cp.country,
                        hp.participant_id,
                        COALESCE(u.name, CONCAT(p.first_name, ' ', p.last_name)) AS name,
                        COALESCE(ht.total_score, 0) AS total_score,
                        COALESCE(ht.best_wave, 0)   AS best_wave,
                        CASE
                        WHEN LOWER(h.round) = 'final'                                   THEN 100
                        WHEN LOWER(h.round) IN ('semifinal','semi')                     THEN 80
                        WHEN LOWER(h.round) IN ('quarterfinal','qf')                    THEN 75
                        WHEN LOWER(h.round) IN ('round 2','round2','r2')                THEN 70
                        WHEN LOWER(h.round) IN ('repechage 2','repechage2','rep2')      THEN 60
                        WHEN LOWER(h.round) IN ('repechage 1', 'repechage1', 'rep1')    THEN 50
                        WHEN LOWER(h.round) IN ('round 1','round1','r1')                THEN 30
                        ELSE 0
                        END AS round_weight
                    FROM comp_heat_participants hp
                    JOIN comp_heats h  ON h.id = hp.heat_id
                    LEFT JOIN comp_participants p ON p.id = hp.participant_id
                    LEFT JOIN comp_users u ON u.id = p.user_id
                    LEFT JOIN comp_users_profiles cp ON cp.user_id = u.id
                    LEFT JOIN heat_totals ht
                            ON ht.heat_id = h.id
                            AND ht.participant_id = hp.participant_id
                    WHERE h.comp_id = :comp_id
                    $divFilter
                    ),
                    picked AS (
                    SELECT *,
                            ROW_NUMBER() OVER (
                            PARTITION BY division_name, participant_id
                            ORDER BY round_weight DESC,
                                        total_score DESC,
                                        best_wave DESC,
                                        heat_number DESC,
                                        heat_id DESC
                            ) AS rn
                    FROM best_rows
                    )
                    SELECT
                    division_name,
                    participant_id,
                    name,
                    round,
                    heat_number,
                    total_score,
                    best_wave,
                    country
                    FROM picked
                    WHERE rn = 1
                    ORDER BY
                    division_name,
                    CASE WHEN LOWER(round) = 'final' THEN 0 ELSE 1 END,
                    CASE WHEN LOWER(round) = 'final' THEN total_score END DESC,
                    round_weight DESC,
                    total_score DESC,
                    best_wave DESC";

            return $this->model->query_bind($sql, $params, 'array');
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
                    WHERE COALESCE(c.status,'') = 'finished'
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
                    AND o.status = 'active'
                    ORDER BY o.organization
                    LIMIT 20";
            $organisers = $this->model->query_bind($sql2, $bind2, 'array');

            $out = array_merge($competitions, $organisers);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($out);
            exit;
        }

    }
?>