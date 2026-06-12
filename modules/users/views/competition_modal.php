<?php
    $user_info    = Modules::run("users/_get_user_info");
    $canJoin      = ($competition->status ?? 'open') === 'open';
    $btnText      = ($competition->entry_type ?? 'free entry') === 'FREE ENTRY' ? 'Proceed to Payment' : 'Join Now';
    $needsProfile = $canJoin && !empty($profile_incomplete);
    $allJoined    = $canJoin && !$needsProfile && empty($divisions) && !empty($joined);
?>
<!-- Header -->
<div class="modal-title">
    <?php if (!$canJoin): ?>
        <p style="background:skyblue;padding:.5rem;color:black;text-transform:uppercase;margin-top:0;">Registration is not open yet!</p>
    <?php elseif ($needsProfile): ?>
        <p style="background:orange;padding:.5rem;color:black;text-transform:uppercase;margin-top:0;">Complete your profile to register!</p>
    <?php elseif ($allJoined): ?>
        <p style="background:var(--ok);padding:.5rem;color:black;text-transform:uppercase;margin-top:0;">You are registered!</p>
    <?php else: ?>
        <p style="background:lightgreen;padding:.5rem;color:black;text-transform:uppercase;margin-top:0;">You can join this competition!</p>
    <?php endif; ?>
    <div class="modal-grid">
        <h4 class="chip" style="margin:0 auto;text-transform:uppercase;background:lightgoldenrodyellow;">
        <?php
            $entryType = $competition->entry_type ?? 'free entry';
            if ($entryType === 'entry fee') {
                $fee      = isset($competition->entry_fee) ? number_format((float)$competition->entry_fee, 2) : '—';
                $currency = $competition->currency ?? 'EUR';
                echo 'Entry: ' . $fee . ' ' . $currency;
            } else {
                echo 'Free Entry';
            }
        ?>
        </h4>
        <h4 class="status-<?= out($competition->status) ?> chip text-center" style="text-transform:uppercase;margin:0 auto;"><?= out($competition->status ?? 'open') ?></h4>
    </div>
</div>

<!-- Main -->
<div class="modal-main" style="text-align:center;font-size:.8rem;">
    <h2 class="mb-0"><?= out($competition->name) . ' ' . out($competition->year) ?></h2>
    <h3 class="mt-0"><?= out($organizer->organization) ?></h3>
    <h3 class="mb-0"><?= out(date('M d', strtotime($competition->start_date)) . ' - ' . date('M d', strtotime($competition->end_date))) ?></h3>
    <h3 class="mt-0 mb-3"><?= out($competition->location ?? '—') ?></h3>

    <?php if ($competition->status === 'open'): ?>

        <?php if (!empty($joined)): ?>
        <div class="joined-divisions">
            <span class="joined-label">Registered in:</span>
            <?php foreach ($joined as $j): ?>
                <span class="chip status-open" style="font-size:.78rem;cursor:default"><?= out($j['division_name']) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($needsProfile): ?>
            <!-- Profile missing DOB and/or gender — eligibility can't be checked, no form -->
            <p style="font-size:.9rem;color:var(--text-muted);margin:1rem 0">
                We need your date of birth and gender to show the divisions you can enter.
                Add them to your profile, then come back to register.
            </p>
            <div class="actions">
                <button type="button" class="btn" onclick="closeModal()">Close</button>
                <a class="btn" href="<?= BASE_URL ?>users/profile_info">Complete Profile</a>
            </div>

        <?php elseif ($allJoined): ?>
            <!-- All eligible divisions already joined — no form -->
            <p style="font-size:.9rem;color:var(--text-muted);margin:1rem 0">You have joined all available divisions for this competition.</p>
            <div class="actions">
                <button type="button" class="btn" onclick="closeModal()">Close</button>
            </div>

        <?php else: ?>
            <!-- Join form — only available divisions shown -->
            <?php
                $join_form_attr = [
                    'id'                  => 'joinForm',
                    'class'               => 'highlight-errors',
                    'mx-post'             => 'users/join/' . (int) $competition->id,
                    'mx-close-on-success' => 'true',
                    'mx-on-success'       => '#registrations',
                ];
                echo form_open('#', $join_form_attr);
            ?>

            <fieldset class="divisions">
                <legend>Select your division</legend>
                <?php if (!empty($divisions)): ?>
                    <?php
                        $options = [];
                        foreach ($divisions as $division) {
                            $options[$division['name']] = $division['name'];
                        }
                    ?>
                    <?php echo form_dropdown('division', $options, '', ['style' => 'appearance:none;background:var(--card);color:var(--white);font-weight:900;border:none;padding:0 0.6em;']); ?>
                <?php else: ?>
                    <div class="empty">No divisions available yet.</div>
                <?php endif; ?>
            </fieldset>

            <label class="terms">
                <input type="checkbox" id="agreeRules" name="agree" value="1" required>
                I agree to the competition rules and terms.
            </label>

            <div class="actions">
                <button type="button" class="btn" onclick="closeModal()">Close</button>
                <button type="submit" class="btn" id="joinBtn" <?= $canJoin ? '' : 'disabled' ?>>
                    <?= $btnText ?>
                </button>
            </div>

            <?php echo form_close(); ?>
        <?php endif; ?>

    <?php else: ?>
        <button type="button" class="btn" style="margin:auto;display:grid;" onclick="closeModal()">Close</button>
    <?php endif; ?>
</div>

<style>
  .hidden{display:none}
  .modal-title{margin-bottom:12px}
  .modal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:8px 0 16px}

  .joined-divisions{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:.4rem;margin-bottom:1rem}
  .joined-label{font-size:.8rem;color:var(--text-muted)}

  .divisions{border:1px solid #eee;border-radius:12px;padding:12px;margin:10px 0 14px}
  .divisions legend{font-weight:700;padding:0 6px}

  .terms{display:flex;gap:10px;align-items:center;font-size:.92rem;margin:8px 0 12px}
  .actions{display:flex;justify-content:flex-end;gap:10px}

  .btn:disabled{opacity:.6;cursor:not-allowed}
</style>
