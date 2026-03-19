<div id="form-table" class="comp_dash">
    <div id="comp-create-form" mx-get="competitions/create_comp" mx-target="#form-container" mx-select="#form-table" mx-trigger="load" style="color: var(--white);font-weight:900;margin-bottom:2rem">
        <h2 class="mb-0"><span>Create</span> Competition</h2>
        <p class="text-right mb-2">Enter details and chose divisions for the competition.</p>
        <div id="response"></div>
        <?php
        $form_attr = [
            'mx-post' => 'competitions/submit_create_comp',
            'mx-redirect-on-success' => 'true',
            'mx-target' => '#response',
            'id' => 'create-comp-form'
        ];
        echo form_open('#', $form_attr); ?>
        <div style="grid-column: span 2;
                    display: grid;
                    grid-template-columns: auto 1fr;
                    align-items: center;
                     padding-inline: 0.5rem 0;" class="field form-field">
            <label>Entry:</label>
            <div style="display: flex;justify-content: center; align-items: center;"> 
                <input id="entry-free" class="entry" type="radio" name="entry_type" value="free" checked style="display: none;">    
                <label class="check-label" for="entry-free">Free</label>
                <input id="entry-fee" class="entry" type="radio" name="entry_type" value="fee" style="display: none;">
                <label class="check-label" for="entry-fee">Fee <input style ="min-width: 25px;width: 55px;height: 35px;margin-inline: 0;appearance: none;text-align: center;font-size: 1rem;" type="number" name="entry_fee" value="20" max="200" min="10"><i class="fa fa-eur" aria-hidden="true"></i></label>
            </div>
        </div>
        <div class="form-field">
            <?php
            echo '<label class="field">Competition:';
            $attr['placeholder'] = 'Competion name here...';
            $attr['autocomplete'] = 'off';
            $attr['style'] = 'text-align-last: center;';
            echo form_input('name', '', $attr);
            echo '</label>';

            echo '<label class="field">Location';
            $attr['placeholder'] = 'Enter location here...';
            $attr['autocomplete'] = 'off';
            echo form_input('location', '', $attr);
            echo '</label>';
            ?>
        </div>
        <div style="display: grid;grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));gap: 1rem;" class="form-field">
            <?php
            echo '<label class="field">Year';
            $attr = [
                'placeholder' => 'Enter competition year ...',
                'autocomplete' => 'off',
                'min' => date('Y'),
                'max' => date('Y', strtotime('+2 years')),
                'step' => '1',
                'style' => 'text-align-last: center;'
            ];
            echo form_number('year', date('Y'), $attr);
            echo '</label>';
            echo '<label class="field">Start Date';
            $attr = [
                'type' => 'date',
                'min' => date('Y-m-d'),
                'max' => date('Y', strtotime('+2 years')) . '-12-31',
                'placeholder' => date('Y-m-d', strtotime('+2 months'))
            ];
            echo form_date('start_date', date('Y-m-d'), $attr);
            echo '</label><label class="field">End Date';
            $attr = [
                'type' => 'date',
                'min' => date('Y-m-d'),
                'max' => date('Y', strtotime('+2 years')) . '-12-31',
                'placeholder' => date('Y-m-d', strtotime('+3 months'))
            ];
            echo form_date('end_date', '', $attr);
            echo '</label>';
            ?>
        </div>
            <?php
            echo '<label class="lg" style="font-weight:normal;grid-column: 1 / -1;text-align: left;margin: 1rem auto;">Pick Divisions For Your Competition:</label>';
            // Add division checkboxes
            echo '<div id="divisions-list" class="mb-2">';
            foreach ($divisions as $division) {
                echo '<input id="' . $division->id . '" class="division" style="display:none;" type="checkbox" name="divisions[]" value="' . $division->id . '">';
                echo '<label for ="' . $division->id . '" style="width:100%;font-weight:normal;text-align:center;padding: 0.2rem;margin:auto;">' . out($division->name) . '</label>';
            }
            echo '</div>';

            echo form_submit('submit', 'Create New', ['class' => 'btn primary']);
            echo form_close();
        ?>
    </div>
</div>

<div id="comp-table">
    <div id="comp-show-table" mx-get="competitions/create_comp" mx-trigger="load" mx-select="#comp-table" mx-target="#form-container">
        <div id="response2"></div>
        <h2 class="mb-0"><span>Available</span> Competition</h2>
        <?php
        if (empty($rows)) {
            echo '<p class="text-right mb-2">No new competitions available</p>';
        } else {
            echo '<p class="text-right mb-2">Click on the competition to view heats or edit / delete competition.<br>Set status to "Closed" to be able to generate heats.</p>';
            echo '<div style="display: grid;grid-template-columns: repeat(auto-fit, minmax(300px, 390px));justify-content: space-around;gap: 1rem;">';
        } ?>

        <?php
            for ($i = 0; $i < $num_rows; $i++) {
                $status = $rows[$i]->status;
                switch ($status) {
                    case 'open':
                        $bgColor = 'skyblue';
                        $txtColor = 'black';
                        break;
                    case 'closed':
                        $bgColor = 'orange';
                        $txtColor = 'black';
                        break;
                    default:
                        $bgColor = 'hsl(100, 100%, 20%)';
                        $txtColor = 'hsl(0, 0%, 100%)'; // fallback color
                }
        ?>
                <a href="heats/show_heats_draw/<?= $rows[$i]->id ?>" target="_blank" class="card-comp" style="background: transparent;">
                    <div class="card pad" style="align-items: center;background: var(--bg-light);">
                        <h3 style="text-transform: uppercase;text-align: center;background: <?= $bgColor ?>;color: <?= $txtColor ?>;padding: 1rem;"><?= $rows[$i]->status ?></h3>
                        
                        <div style="display:grid;margin:1rem">

                            <p class="mb-0 mt-0" style="font-size: 1.2rem;text-align: center;padding-bottom: 0;"><?= $rows[$i]->name ?> <?= $rows[$i]->year ?></p>
                            <p class="mt-0 mb-0" style="text-align:center;font-size: 0.85rem"><?= date('d M', strtotime($rows[$i]->start_date)) . ' - ' . date('d M', strtotime($rows[$i]->end_date)) ?></p>
                            <p class="mt-0 mb-0" style="text-transform:uppercase;font-size:1em;font-size: 0.85rem;text-align: center;"><?= $rows[$i]->location ?></p>
                        
                            <?php if ($rows[$i]->entry_type === "entry fee") { 
                                echo '<p style="text-align:center;margin:0;font-size: .8rem;">' . $rows[$i]->entry_type . ' ' . $rows[$i]->entry_fee . ' <i class="fa fa-eur" aria-hidden="true"></i></p>';
                            } else { 
                                echo '<p style="text-align:center;margin:0">Free Entry</p>'; 
                            } ?>

                        </div>

                        <div style="display: flex;gap: 1rem;">
                            <?php
                            // -- Only show generate button when status is closed
                            if ($rows[$i]->status === 'closed') {
                                $btn_attr = [
                                    'mx-post' => 'heats/generate_modal/' . $rows[$i]->id,
                                    'mx-build-modal' => 'comp-generate-modal',
                                    'mx-select' => '.modal-genetate-body',
                                    'class' => 'modal-generate btn-success',
                                    'style' => 'flex:1;color:lawngreen;width:100%;'
                                ];
                                echo form_button('generate_button', '<i class="fa fa-tasks" aria-hidden="true"></i> Generate', $btn_attr);
                            }

                            // -- Only show delete button when status is open or unknown
                            $del_arr = ['closed', 'generated', 'running', 'finished'];
                            if (!in_array($rows[$i]->status, $del_arr)) {
                                $del_attr = [
                                    'mx-get' => 'competitions/delete_modal/' . $rows[$i]->id,
                                    'mx-build-modal' => 'comp-delete-modal',
                                    'class' => 'btn danger',
                                    'style' => 'flex:1;'
                                ];
                                echo form_button('del_button', '<i class="fa fa-trash" aria-hidden="true"></i> Delete', $del_attr);
                            }

                            $btn_attr = [
                                'mx-get' => 'competitions/edit_comp/' . $rows[$i]->id,
                                'mx-build-modal' => 'comp-edit-modal',
                                'style' => 'flex:1;width:100%;',
                                'class' => 'btn primary'
                            ];
                            echo form_button('edit_button', '<i class="fa fa-edit" aria-hidden="true"></i> Edit', $btn_attr);
                            ?>
                        </div>

                    </div>
                </a>
        <?php }; ?>
    </div>
</div>
