<?php $user_info = Modules::run("competitions/_get_judge_info"); ?>
<div id="participantsTable">
    <section class="align-center">
        <h2 class="mb-0"><span>Event</span> Participants</h2>
        <?php if (!empty($rows)) { ?>
        <p>Competition: <strong style="color:var(--text)">"<?= out($rows[0]->competition_name) ?>"</strong><br>Total Participants: <?= count($rows) ?></p>
        <div style="color: orange;gap: 1rem;align-items: center;">
            <?php } else { ?>
            <p class="mb-0">No participants found.</p>
            <p class="mb-0">Please check the competition status.</p>
            <?php } ?> 
        </div>
        <!-- Filters -->
        <?php
            $divisions = Modules::run("heats/_get_divisions_comp", $comp_id);
            // $divisions = ['ALL' => '', 'U-12 F' => 'female_u12', 'U-15 F' => 'female_u15', 'U-18 F' => 'female_u18', 
            //            'U-12 M' => 'male_u12', 'U-15 M' => 'male_u15', 'U-18 M' => 'male_u18'];
        ?>
            <label id="division-label" for="division-selector" class="field" style="width: auto;margin: auto;align-items: center;justify-content: center;background: var(--bg-dark);">
                <i class="fa fa-arrow-right" style="padding-inline: 0.5rem; color: light-dark(var(--accent), var(--opposite));"></i>
                <select id="division-selector" 
                    mx-get="competitions/show_participants/<?= $comp_id ?>/${this.value}" 
                    mx-target="#participants-list" mx-select="#participants-list"
                    mx-trigger="change" style="appearance: none;text-align-last: center;">
                    <?php if (count($divisions) > 2): 
                            foreach ($divisions as $label => $value): ?>
                                <option value="<?= $value ?>"><?= out($label) ?></option>
                            <?php endforeach;
                    endif; ?>
                </select>
            </label>
        <div class="selector">
        </div>
        </section>
    <table id="participants-list" class="mt-1" mx-get="competitions/show_participants/<?= $comp_id ?>" mx-target="#participants-list" mx-select="#participants-list" mx-trigger="load">
        <thead>
            <tr>
                <th>No.</th>
                <th class="text-left">Athlete</th>
                <th>Division</th>
                <th>Status</th>
                <?php if ($user_info->role === 'organizer') { ?>
                    <th>Action</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row) { ?>
                <tr>
                    <td></td>
                    <td class="text-left"><?= out($row->user_name ?? $row->name) ?></td>
                    <td><?= out($row->gender_age) ?></td>
                    <?php if ($user_info->role === 'organizer') {
                        if ($row->status === 'pending') {
                            if ($row->entry_type === 'free entry') { ?>
                            <td><p class="chip" style="border: 1px solid orange;color: orange;box-shadow: 0 0 3px orange, 0 0 3px orange inset;">pending</p></td>
                            <td>
                                <a class="confirm" mx-post="competitions/confirm_participant/<?= $row->id ?>" mx-vals='{"csrf_token":"<?= $_SESSION['csrf_token'] ?? '' ?>"}' mx-on-success="#participants-list"><i class="fa fa-square-o" aria-hidden="true"></i></a>
                            </td>
                            <?php } else { ?>
                                <td>
                                    <p class="chip" style="border: 1px solid indianred;color: indianred;box-shadow: 0 0 3px indianred, 0 0 3px indianred inset;">unpaid</p>
                                </td>
                                <td>
                                    <a style="color:indianred; font-size: 1.5rem;"><i class="fa fa-square" aria-hidden="true"></i></a>
                                </td>
                            <?php } ?>
                        <?php } else if ($row->status === 'paid') { ?>
                            <td><p class="chip" style="border: 1px solid dodgerblue;color: dodgerblue;box-shadow: 0 0 3px dodgerblue, 0 0 3px dodgerblue inset;">entry paid</p></td>
                            <td>
                                <a class="confirm" mx-post="competitions/confirm_participant/<?= $row->id ?>" mx-vals='{"csrf_token":"<?= $_SESSION['csrf_token'] ?? '' ?>"}' mx-on-success="#participants-list"><i class="fa fa-square-o" aria-hidden="true"></i></a>
                            </td>
                        <?php } else if ($row->status === 'withdrawn') { ?>
                            <td><p class="chip" style="border: 1px solid gray;color: gray;box-shadow: 0 0 3px gray, 0 0 3px gray inset;">withdrawn</p></td>
                            <td>
                                <a style="color:gray; font-size: 1.5rem;" title="Participant withdrew"><i class="fa fa-minus-square-o" aria-hidden="true"></i></a>
                            </td>
                        <?php } else { ?>
                            <td>
                                <p class="chip" style="border: 1px solid var(--status-green);color: var(--status-green);box-shadow: 0 0 3px var(--status-green), 0 0 3px var(--status-green) inset;">confirmed</p>
                            </td>
                            <td>
                                <a class="confirmed"><i class="fa fa-check-square-o" aria-hidden="true"></i></a>
                            </td>
                        <?php } ?>
                                            
                    <?php } else { ?>                 
                        <?php if ($row->status === 'pending') {
                            if ($row->entry_type === 'free entry') { ?>
                                <td><p class="chip" style="border: 1px solid orange;color: orange;box-shadow: 0 0 3px orange, 0 0 3px orange inset;">pending</p></td>
                            <?php } else { ?>
                                <td><p class="chip" style="border: 1px solid indianred;color: indianred;box-shadow: 0 0 3px indianred, 0 0 3px indianred inset;">unpaid</p></td>
                            <?php } ?>
                        <?php } else if ($row->status === 'paid') { ?>
                            <td><p class="chip" style="border: 1px solid dodgerblue;color: dodgerblue;box-shadow: 0 0 3px dodgerblue, 0 0 3px dodgerblue inset;">paid</p></td>
                        <?php } else if ($row->status === 'withdrawn') { ?>
                            <td><p class="chip" style="border: 1px solid gray;color: gray;box-shadow: 0 0 3px gray, 0 0 3px gray inset;">withdrawn</p></td>
                        <?php } else { ?>
                            <td><p class="chip" style="border: 1px solid var(--status-green);color: var(--status-green);box-shadow: 0 0 3px var(--status-green), 0 0 3px var(--status-green) inset;">confirmed</p></td>
                        <?php } ?>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot><tr><td colspan="5" class="text-left">SHOWING <?= count($rows) ?> OF <?= count($rows) ?> PARTICIPANTS</td></tr></tfoot>
    </table>
</div>


<style>
    .comfirm {
        padding: 0.2rem 0.5rem;
        border-radius: 14px;
        border: 3px solid;
        color: green;
    }
</style>