<div id="judge-table" class="comp_dash">
    <h2><span>Assign</span> Judges</h2>
    <p>You can assign only your organization judges.<br>You can create new judge or add by email.</p>
    <div id="response" style="width: fit-content;margin: auto;"></div>

    <!-- Competition selection dropdown -->
    <div class="container-xs nav">
        <?php
            $comps = Modules::run('competitions/get_competitions');
            $nav_attr = [
                'mx-get' => 'judges/assign_judges/${this.value}',
                'mx-target' => '#judge-wrap',
                'mx-select' => '#judge-wrap',
                'mx-push-url' => "true",
                'style' => 'appearance: none;width: fit-content;margin: auto;flex: 1;text-align-last: center;',
                'mx-trigger' => "change"
            ];
            if (empty($comps)) {
                echo '<p class="text-right mb-2">No competitions available<br>You have to create competition first</p>';
            } else {
                // Competition choice dropdown
                echo '<label class="field mb-1"><i class="fa fa-arrow-right" style="padding-inline: 0.5rem; color: light-dark(var(--accent), var(--opposite));"></i>';
                $options = ['0' => 'Select Competition...'];
                foreach ($comps as $comp) {
                    if ($comp->status !== 'finished') {
                        $options[$comp->id] = $comp->name . ' ' . $comp->year;
                    }
                }
                $first_key = key($options);
                echo form_dropdown('competition_id', $options, $first_key, $nav_attr);
                echo '</label>';
            }
        ?>
    </div>
    
    <div id="judge-wrap">

        <!-- Judge assignment table -->
        <div id="judge-assign" class="grid-2 table-wrapper" mx-get="judges/assign_judges/<?= (int)$comp_id ?>" mx-trigger="activate" mx-target="#judge-assign" mx-select="#judge-assign" style="gap:0">

            <div id="judge-assign-form" mx-get="judges/assign_judges/<?= (int)$comp_id ?>" mx-trigger="activate" mx-target="#judge-assign-form" mx-select="#judge-assign-form" style="padding-bottom: 2rem;">
                <h3 class="mb-0 text-center">Available Judges</h3><hr style="margin:0 0 0.5rem;padding-right: 1rem;">
                <?php
                if (empty($org_judges)) {
                    echo '<p class="mt-0 text-center">No judges available...<br>You have to create or invite judges to your organization</p>';
                } else {
                    echo '<p class="mt-0 text-center">Click on the judge to assign to competition.</p>';
                    echo '<div class="judge-list">';
                    $num_rows = count($org_judges);
                    for ($i = 0; $i < $num_rows; $i++) {
                        $role = $org_judges[$i]->role;
                        switch ($role) {
                            case 'head_judge':
                                $bgColor = 'royalblue';
                                $txtColor = 'white';
                                $roleText = 'head judge';
                                break;
                            default:
                                $bgColor = 'skyblue'; // fallback color
                                $txtColor = 'black';
                                $roleText = 'judge';
                        }
                        $status = $org_judges[$i]->status;
                        switch ($status) {
                            case 'suspended':
                                $statusColor = 'crimson';
                                break;
                            default:
                                $statusColor = 'limegreen';
                        }
                        echo '<div class="justify-between" style="display: flex;
                                                align-items: center;
                                                border: .5px dashed rgba(116, 116, 117, 0.46);
                                                font-size: 0.8rem;
                                                border-top-right-radius: 15px;
                                                border-bottom-right-radius: 15px;§
                                                background: rgba(196, 196, 196, 0.07);">';
                        echo '<div class="flex" style="align-items: center;"><h3 style="text-transform: uppercase;text-align: center;background: ' . $bgColor . ';color: black;padding: 5px;">' . $roleText . '</h3>';
                        echo '<span style="border:1px solid ' . $statusColor . ';color:' . $statusColor . ';box-shadow: 0 0 3px ' . $statusColor . ', 0 0 3px ' . $statusColor . ' inset;">' . $org_judges[$i]->status . '</span></div>';
                        echo '<div class="grid-1" style="flex: 4;padding-right: 1rem;"><p class="mb-0 mt-0 text-right">' . $org_judges[$i]->name . '</p><p class="mt-0 mb-0 text-right" style="text-transform:lowercase;font-size: 0.5rem;">' . $org_judges[$i]->email . '</p></div>';
                        if ($org_judges[$i]->status === 'suspended') {
                            echo '<div style="padding-right: 1rem;"><p class="mb-0 mt-0 text-right" style="color: crimson; font-weight: bold;">(suspended)</p></div>';
                            echo '</div>';
                            continue; // Skip the assign button for suspended judges
                        } else if ($comp_id == 0) {
                            // No competition selected
                            echo '<div style="padding-right: 1rem;"><p class="mb-0 mt-0 text-right" style="color: orange; font-weight: bold;">no comp</p></div>';
                        } else {
                            $btn_attr = [
                                'mx-post' => 'judges/submit_assign/' . $comp_id . '/' . $org_judges[$i]->id,
                                'mx-vals' => '{"csrf_token":"' . ($_SESSION['csrf_token'] ?? '') . '"}',
                                'mx-on-success' => '#judge-assign',
                                'mx-target' => '#response',
                                'class' => 'btn'
                            ];
                            echo form_button('assign_button', '<i class="fa fa-plus" aria-hidden="true"></i>', $btn_attr);
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>

            <div id="judge-show-list" mx-get="judge/assign_judges/<?= (int)$comp_id ?>" mx-trigger="activate" mx-target="#judge-show-list" mx-select="#judge-show-list" style="padding-bottom: 2rem;border-left: 1px solid var(--primary-60);">
                <h3 class="mb-0 text-center">Assigned Judges</h3><hr style="margin:0 0 0.5rem;">
                <?php
                if (empty($comp_judges)) {
                    echo '<p class="text-center mb-2">No judges assigned</p>';
                } else {
                    echo '<p class="text-center mt-0">Click button to unassign a judge</p>';
                    echo '<div class="judge-list">';
                    $num_judges = count($comp_judges);
                    for ($i = 0; $i < $num_judges; $i++) {
                        $status = $comp_judges[$i]->assignment_status;
                        switch ($status) {
                            case 'accepted':
                                $statusColor = 'lawngreen';
                                $statusText = 'black';
                                break;
                            case 'pending invite':
                                $statusColor = 'orange';
                                $statusText = 'white';
                                break;
                            default:
                                $statusColor = 'crimson'; // fallback color
                                $statusText = 'white';
                        }
                        $role = $comp_judges[$i]->assignment_role;
                        switch ($role) {
                            case 'head_judge':
                                $bgColor = 'royalblue';
                                $txtColor = 'white';
                                break;
                            default:
                                $bgColor = 'skyblue'; // fallback color
                                $txtColor = 'black';
                        }
                        echo '<div class="justify-between" style="display: flex;
                                    align-items: center;
                                    border: .5px dashed rgba(116, 116, 117, 0.46);
                                    font-size: 0.8rem;
                                    border-top-right-radius: 15px;
                                    border-bottom-right-radius: 15px;
                                    background: rgba(196, 196, 196, 0.07);">';
                    echo '<div class="grid-1" style="flex: 1.2;display: flex;justify-content: center;gap: 0.5rem;"><h3 style="text-transform: uppercase;text-align: center;background: ' . $bgColor . ';color: black;padding: 5px;">' . $comp_judges[$i]->assignment_role . '</h3>';
                    echo '<h4 style="text-align: center;margin: 0;background:' . $statusColor . ';color:' . $statusText . ';padding: 7px 5px;">' . $comp_judges[$i]->assignment_status . '</h4></div>';
                    echo '<div class="grid-1" style="flex: 1;padding-right: 1rem;"><p class="mb-0 mt-0 text-right">' . $comp_judges[$i]->name . '</p><p class="mt-0 mb-0 text-right" style="text-transform:lowercase;font-size: 0.5rem;">' . $comp_judges[$i]->email . '</p></div>';
                        
                    $btn_attr = [
                        'mx-get' => 'judges/remove_modal/' . $comp_id . '/' . $comp_judges[$i]->id,
                        'mx-build-modal' => 'remove-judge-modal',
                        'class' => 'danger btn'
                    ];
                    echo form_button('remove_button', '<i class="fa fa-minus" aria-hidden="true"></i>', $btn_attr);

                    echo '</div>';

                    }

                echo '</div>';

                }
                ?>
            </div>

        </div>
    </div>
</div>
