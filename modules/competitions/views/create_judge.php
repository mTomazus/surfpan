<div id="form-table">
    <div id="judge-create" mx-get="competitions/create_judge" mx-trigger="load" mx-select="#form-table">

        <h2><span>Create</span> Judge</h2>
        <p>Fill in the form below to create a new judge.<br>Or just enter email to invite judge.</p>
        <div id="response"></div>
        <div style="display:flex;gap: 1rem;font-size: 0.6rem;text-align: center; justify-content: center;">
            <?php
                if ('' !== flashdata()) {
                    echo flashdata();
                    } else {
                        echo validation_errors();
                        }  
                        ?>
        </div>
        <p style="text-align: center;color: var(--primary);">Judge roles can be changed later.</p>
        <?php
            $form_attr = [
                'mx-post' => 'competitions/submit_create_judge',
                'mx-target' => '#response',
                'mx-on-success' => '#judge-create',
                'id' => 'create-judge-form'
            ];
            echo form_open('#', $form_attr);
            echo '<div class="flex-row field" style="gap: 1rem;align-items: center;justify-content: space-between;padding-right: 0rem;">';
            echo form_label('Role: ');
            echo form_radio('role', 'judge', true, ['id' => 'role-judge', 'class' => 'judge-role', 'style' => 'display: none;']);
            echo form_label('Judge', ['for' => 'role-judge', 'class' => 'check-label']);
            echo form_radio('role', 'head_judge', false, ['id' => 'role-head', 'class' => 'judge-role', 'style' => 'display: none;']);
            echo form_label('Head Judge', ['for' => 'role-head', 'class' => 'check-label']);
            echo '</div>';

            echo '<label class="field">Full Name: ';
            $attr['placeholder'] = 'Enter judge full name here...';
            $attr['autocomplete'] = 'off';
            echo form_input('name', '', $attr);
            echo '</label>';

            echo '<label class="field">Email: ';
            $attr['placeholder'] = 'Enter judge email here...';
            $attr['autocomplete'] = 'off';
            echo form_email('email', '', $attr);
            echo '</label>';

            echo '<label class="field">Phone: ';
            $attr['placeholder'] = 'Enter judge phone here...';
            $attr['autocomplete'] = 'off';
            echo form_input('phone', '', $attr);
            echo '</label>';

            echo '<label class="field">Password: ';
            $attr['placeholder'] = 'Enter password here...';
            echo form_password('password', '', $attr);
            echo '</label>';

            echo '<label class="field">Repeat Password: ';
            $attr['placeholder'] = 'Repeat password here...';
            echo form_password('repeat_password', '', $attr);
            echo '</label>';

            echo form_submit('submit', 'Create', ['style' => 'margin: 2rem auto;']);
            echo form_close();

        ?>
    </div>
</div>
<div id="judge-table">
    <div class="show-judge" mx-get="competitions/create_judge" mx-trigger="load" mx-select="#judge-table">
        <h2 class="mb-0"><span>Available</span> Judges</h2>
        <?php
        if (empty($rows)) {
            echo '<p class="mb-0 text-right">No judges available<br>You have to create or invite judges</p>';
        } else {
            echo '<p class="mb-2 text-right">Click on the judge to edit or delete<br>If competition is created, you can assign judges to it.</p>';
            echo '<div class="judge-buttons"><button class="btn" mx-get="competitions/create_judge" mx-target="#form-container" mx-select="#form-table" mx-build-modal="judge-create-modal"><i class="fa fa-plus" aria-hidden="true"></i> Add Judge</button>';
            echo '<button class="btn" mx-get="judges/assign_judges" mx-target="#form-container" mx-select="#judge-table"><i class="fa fa-gavel" aria-hidden="true"></i> Assign Judges</button></div>';
            echo '<table id="judges-table">';
            echo '<div id="response"></div>';
            echo '<thead><tr><th>Status</th><th>Name</th><th>Role</th><th>Email</th><th>Actions</th></tr></thead>';
            echo flashdata();
            foreach ($rows as $judge) {
                $role = $judge->role;
                switch ($role) {
                    case 'head_judge':
                        $roleColor = 'var(--primary)';
                        break;
                    default:
                        $roleColor = 'var(--text-dark)'; // fallback color
                }
                $status = $judge->status;
                switch ($status) {
                    case 'suspended':
                        $statusColor = 'crimson';
                        break;
                    default:
                        $statusColor = 'var(--status-green)'; // fallback color
                }
                echo '<tr><td style="color: ' . $statusColor . ';">';
                $opt_status = ['active' => 'Active', 'suspended' => 'Suspended'];
                echo form_dropdown('status', $opt_status, $status, ['id' => 'status-select-' . $judge->id, 'mx-post' => 'competitions/submit_edit_judge/' . $judge->id, 'mx-dom-vals' => '{"status": {"selector": "#status-select-' . $judge->id . '","property": "value"}, "role": {"selector": "#role-select-' . $judge->id . '","property": "value"}}', 'mx-on-success' => '.show-judge', 'mx-trigger' => 'change', 'class' => 'chip judge-status', 'style' => 'border: 1px solid ' . $statusColor . ';color: ' . $statusColor . ';box-shadow: 0 0 3px ' . $statusColor . ', 0 0 3px ' . $statusColor . ' inset;']);
                echo '</td><td>' . $judge->name . '</td><td style="color: ' . $roleColor . ';">';
                $options = ['head_judge' => 'Head Judge', 'judge' => 'Judge'];
                echo form_dropdown('role', $options, $role, ['id' => 'role-select-' . $judge->id, 'mx-post' => 'competitions/submit_edit_judge/' . $judge->id, 'mx-dom-vals' => '{"status": {"selector": "#status-select-' . $judge->id . '","property": "value"},"role": {"selector": "#role-select-' . $judge->id . '","property": "value"}}', 'mx-on-success' => '.show-judge', 'mx-trigger' => 'change', 'class' => 'chip judge-role', 'style' => 'color: ' . $roleColor . ' ;border: 1px solid ' . $roleColor . ' ;box-shadow: 0 0 3px ' . $roleColor . ', 0 0 3px ' . $roleColor . ' inset;">']);
                echo '</td><td>' . $judge->email . '</td>';
                echo '<td>';
                $del_attr = [
                    'mx-get' => 'competitions/delete_judge_modal/' . $judge->id,
                    'mx-build-modal' => 'judge-delete-modal',
                    'class' => 'btn danger'
                ];
                echo anchor('#', '<i class="fa fa-chain-broken" aria-hidden="true"></i> Unlink', $del_attr);
                echo '</td></tr>';
            }
            echo '<tfoot><tr><td colspan="5" class="text-left">SHOWING ' . count($rows) . ' OF ' . count($rows) . ' JUDGES</td></tr></tfoot>';
            echo '</table>';
        }
        ?>
    </div>
</div>