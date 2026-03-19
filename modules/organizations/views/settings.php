<div class="span-12">
    <h2>Upcoming <span>Events</span></h2>
    <p>Click buttons to edit, change status or delete competition.<br>
        You have to set status to "closed" to be able to generate heats.</p>
    <div>
        <?= flashdata('<h3 style="color: white; background: cornflowerblue; padding: 0.5rem; margin: auto; width: -webkit-fit-content;">', '</h3>'); // display system messages ?> 
    </div>
    <div id="upcoming_comps" mx-get="organizations/dashboard" mx-select="#upcoming_comps" mx-target="#upcoming_comps" mx-trigger="load" class="p-1 mt-3">
        <?php if (!empty($upcoming_comps)): ?>
                <?php foreach ($upcoming_comps as $comp): 
                    switch ($comp->status) {
                        case 'generated':
                            $text = '<span style="font-size: 14px; line-height: 0px; margin-top: 1rem;">HEATS</span>';
                            break;
                        case 'closed':
                            $text = '<span style="font-size: 14px; line-height: 0px; margin-top: 1rem;">REGISTRATION</span>';
                            break;
                        case 'open':
                            $text = '<span style="font-size: 14px; line-height: 0px; margin-top: 1rem;">REGISTRATION</span>';
                            break;
                        default:
                            $text = '<span style="font-size: 14px; line-height: 0px; margin-top: 1rem;">COMPETITION</span>';
                    }
                ?>
                <div mx-get="competitions/edit_comp/<?= $comp->id ?>" mx-build-modal="comp-edit-modal" class="stat flex" style="color: var(--chip-<?= strtolower(out($comp->status)) ?>);box-shadow: 0 0 3px var(--chip-<?= strtolower(out($comp->status)) ?>), 0 0 3px var(--chip-<?= strtolower(out($comp->status)) ?>) inset;">
                    <h3 style="display:grid;font-size: 2rem;margin-inline: 1.5rem;text-transform: uppercase;text-align: center;padding: 0.5rem 1rem;color: var(--chip-<?= strtolower(out($comp->status)) ?>)"><?= $text ?><?= out($comp->status) ?></h3>
                    <div style="display:grid;margin:auto;gap:0.2rem;flex:1">
                        <p class="mb-0 mt-0" style="font-size: 1.5rem;line-height:1.5rem;text-align: center;padding-bottom: 0;"><?= out($comp->name) ?></p>
                        <p class="mb-0 mt-0" style="font-size: 0.8rem;text-align: center;padding-bottom: 0;"><?= out($comp->start_date) ?> / <?= date('m-d', strtotime($comp->end_date)) ?></p>
                    </div>

                    <div style="display: grid;gap: .5rem;align-items: center;">
                        <?php
                        // -- Only show generate button when status is closed
                        if ($comp->status === 'closed' || $comp->status === 'generated') {
                            $btn_attr = [
                                'mx-get' => 'heats/heat_generation_page',
                                'style' => 'flex:1;margin:0',
                                'mx-target' => '#form-container',
                                'mx-select' => '#form-container',
                                'class' => 'btn secondary'
                            ];
                            echo form_button('generate_button', '<i class="fa fa-tasks" aria-hidden="true"></i> Generate', $btn_attr);
                        }

                        // -- Only show delete button when status is open or unknown
                        $del_arr = ['closed', 'generated', 'running', 'finished'];
                        if (!in_array($comp->status, $del_arr)) {
                            $del_attr = [
                                'mx-get' => 'competitions/delete_modal/' . $comp->id,
                                'mx-build-modal' => 'comp-delete-modal',
                                'class' => 'btn danger',
                                'style' => 'flex:1;margin:0'
                            ];
                            echo form_button('del_button', '<i class="fa fa-trash" aria-hidden="true"></i> Delete', $del_attr);
                        }
                        ?>
                    </div>
                    <div style="display:grid;margin:auto;gap:0.2rem;flex: 0.5;">
                        <p class="text-center btn mt-0" style="border-radius: 12px;box-shadow: 0 0 3px;"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</p>
                    </div>

                </div>
                <?php endforeach; ?>
        <?php else: ?>
            <p>No upcoming competitions found.</p>
            <p>Create a new event 
                <a style="color:light-dark(var(--accent), var(--opposite));cursor:pointer" mx-get="competitions/create_comp" mx-target="#form-container" mx-select="#form-table">here</a>.</p>
        <?php endif; ?>
    </div>
</div>
<div class="span-12 mt-4 mb-2">
    <h2 class="mb-2">Settings <span>Tips</span></h2>
    <ul style="list-style: none; border: none; box-shadow: none;text-align: left;display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
        <li style="list-style: circle;margin-left: 2rem;">Change status to "open" for participants to join.</li>
        <li style="list-style: circle;margin-left: 2rem;">Update status to "closed" when registration is closed.</li>
        <li style="list-style: circle;margin-left: 2rem;">Organization status private hides you from searches.</li>
        <li style="list-style: circle;margin-left: 2rem;">Make sure you correctly choose time zone.</li>
        <li style="list-style: circle;margin-left: 2rem;">Link team members to your organization.</li>
        <li style="list-style: circle;margin-left: 2rem;">Contact support for any billing inquiries.</li>
    </ul>
</div>

<style>
    .txt {
        flex:2;
    }
    .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:10px}
    
    .stat {
        transition: all 1s ease;
        padding: 12px;
        border-radius: 14px;
        border: 1px dashed light-dark(var(--accent), var(--opposite));
        box-shadow: 1px 1px 3px light-dark(var(--accent-shadow), var(--opposite-shadow));
    }
    .stat:hover {
        box-shadow: 2px 4px 8px light-dark(var(--accent-shadow), var(--opposite-shadow));
        transform: translate(0, 2px);
        background: light-dark(hsla(260, 100%, 50%, 0.05), hsla(185, 100%, 50%, 0.05));
    }

    .field {
        transition: all 1s ease;
    }
    .field:hover, .field:focus-within {
        transform: translate(0, 1px);
        background: light-dark(hsla(260, 100%, 50%, 0.03), hsla(185, 100%, 50%, 0.03));
        box-shadow: 2px 4px 8px light-dark(var(--accent-shadow), var(--opposite-shadow));
    }
    .stat .k{font-size:22px;font-weight:900}
    .stat .l{font-size:12px;color:var(--sub)}

    .badge {
        box-shadow: 0 2px 4px;
        color: var(--opposite-dark);
        border: 1px solid;
        cursor: default;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(74,217,199,.12);
        font-size: 1rem;
        height: 40px;
    }

    form {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    }

    @media screen and (max-width: 750px) {
        form {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }
</style>
