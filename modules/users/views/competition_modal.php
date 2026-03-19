<?php
    $user_info = Modules::run("users/_get_user_info");
    $canJoin = ($competition->status ?? 'open') === 'open';
    $btnText = ($competition->entry_type ?? 'free entry') === 'FREE ENTRY' ? 'Proceed to Payment' : 'Join Now';
?>
<!-- Header -->
<div class="modal-title">
    <?php
    if (!$canJoin) {
        echo '<p style="background: skyblue;padding: .5rem;color: black;text-transform: uppercase;margin-top:0;">
        Registration is not open yet!</p>';
    } else {
        echo '<p style="background: lightgreen;padding: .5rem;color: black;text-transform: uppercase;margin-top:0;">
        You can join this competition!</p>';
    }
    ?>
    <div class="modal-grid">
        <h4 class="chip" style="margin:0 auto;text-transform: uppercase;background: lightgoldenrodyellow;">
        <?php
            $entryType = $competition->entry_type ?? 'free entry';
            if ($entryType === 'entry fee') {
                $fee = isset($competition->entry_fee) ? number_format((float)$competition->entry_fee, 2) : '—';
                $currency = $competition->currency ?? 'EUR';
                echo 'Entry: ' . $fee . ' ' . $currency;
            } else {
                echo 'Free Entry';
            }
        ?>
        </h4>  
        <h4 class="status-<?= out($competition->status) ?> chip text-center" style="text-transform: uppercase;margin:0 auto;"><?= out($competition->status ?? 'open') ?></h4>
    </div>
</div>
    
    <!-- Main -->
    <div class="modal-main" style="text-align: center;font-size: .8rem;">
        <h2 class="mb-0"> <?= out($competition->name) . ' ' . out($competition->year ) ?> </h2>
        <h3 class="mt-0"> <?= out($organizer->organization) ?> </h3>
        <h3 class="mb-0"><?= out(date('M d', strtotime($competition->start_date)) . ' - ' . date('M d', strtotime($competition->end_date))) ?></h3>
        <h3 class="mt-0 mb-3"><?= out($competition->location ?? '—') ?></h3>
    <!-- entry type -->
    <?php
    // Helper to compute seats left if your divisions have capacity and registered_count
    // $hasSeatsInfo = !empty($divisions) && isset($divisions[0]['capacity']);
    ?>
    <?php if ($competition->status === 'open'): ?>
    <!-- Join competition form -->
    <form mx-post="users/join/<?= $competition->id ?>" mx-close-on-success="true" mx-on-success="#registrations" id="joinForm" action="#" method="post">

        <fieldset class="divisions">
            <legend>Select your division</legend>

            <?php if (!empty($divisions)): ?>

                <?php
                    // Build options array from divisions
                    $options = [];
                    foreach ($divisions as $division) {
                        // Use division id as key, name as value
                        $options[$division['name']] = $division['name'];
                    }
                ?>
                <?php echo form_dropdown('division', $options, '', array('style' => 'appearance: none;background: var(--card);color: var(--white);font-weight: 900;border: none;padding: 0 0.6em;')); ?>

            <?php else: ?>

                <div class="empty">No divisions available yet.</div>
            <?php endif; ?>

        </fieldset>

        <label class="terms">
            <input type="checkbox" id="agreeRules" name="agree" value="1" required>
            I agree to the competition rules and terms.
        </label>

        <div class="actions">
            <button type="button" class="btn" onclick="closeModal()">close</button>
            <button type="submit" class="btn" id="joinBtn" <?= $canJoin ? '' : 'disabled' ?>>
            <?= $btnText ?>
            </button>
        </div>

        <?php echo form_close(); ?>

    <?php else: ?>
        <button type="button" class="btn" style="margin: auto;display: grid;" onclick="closeModal()">Close</button>
    <?php endif; ?>
</div>
        
<style>
  .hidden{display:none}
  .modal-title {margin-bottom:12px;}
  .modal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:8px 0 16px}

  .divisions{border:1px solid #eee;border-radius:12px;padding:12px;margin:10px 0 14px}
  .divisions legend{font-weight:700;padding:0 6px}
  .division-option{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid #eee;border-radius:10px;padding:10px 12px;margin:8px 0;cursor:pointer}
  .division-option input{margin-right:8px}
  .division-option.disabled{opacity:.5;cursor:not-allowed}
  .division-name{font-weight:600}
  .division-cap{font-size:.82rem;background:#f1f5f9;border-radius:999px;padding:2px 8px}
  .division-cap.full{background:#fee2e2}

  .terms{display:flex;gap:10px;align-items:center;font-size:.92rem;margin:8px 0 12px}
  .actions{display:flex;justify-content:flex-end;gap:10px}
  .note{font-size:.85rem;color:#a00;margin-top:8px}

  .btn:disabled{opacity:.6;cursor:not-allowed}
</style>
