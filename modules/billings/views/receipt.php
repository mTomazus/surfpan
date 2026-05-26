<div class="card pad" style="max-width:540px;margin:2rem auto">
  <div class="section-head" style="margin-bottom:1.5rem">
    <div>
      <h2 style="margin:0">Payment Receipt</h2>
      <span class="subtle">Entry fee confirmation</span>
    </div>
    <span class="badge" style="background:color-mix(in oklab,var(--ok),transparent 75%);border-color:color-mix(in oklab,var(--ok),transparent 40%);color:var(--text)">Paid</span>
  </div>

  <div style="border:1px dashed color-mix(in oklab,var(--text),transparent 75%);border-radius:.75rem;padding:1.25rem;margin-bottom:1.5rem;display:grid;gap:.5rem;font-size:.9rem">
    <div style="display:flex;justify-content:space-between">
      <span class="subtle">Competition</span>
      <strong><?= out($charge['comp_name']) ?> <?= out($charge['comp_year']) ?></strong>
    </div>
    <div style="display:flex;justify-content:space-between">
      <span class="subtle">Location</span>
      <span><?= out($charge['comp_location']) ?></span>
    </div>
    <div style="display:flex;justify-content:space-between">
      <span class="subtle">Division</span>
      <span><?= out($charge['division_name']) ?></span>
    </div>
    <div style="display:flex;justify-content:space-between">
      <span class="subtle">Competitor</span>
      <span><?= out($charge['user_name']) ?></span>
    </div>
    <hr style="border:none;border-top:1px solid color-mix(in oklab,var(--text),transparent 85%);margin:.5rem 0">
    <div style="display:flex;justify-content:space-between">
      <span class="subtle">Amount</span>
      <strong style="font-size:1.1rem"><?= number_format((float)$charge['amount'], 2) ?> <?= out($charge['currency'] ?? 'EUR') ?></strong>
    </div>
    <div style="display:flex;justify-content:space-between">
      <span class="subtle">Payment reference</span>
      <code style="font-size:.8rem"><?= out($charge['payment_ref'] ?? '—') ?></code>
    </div>
    <div style="display:flex;justify-content:space-between">
      <span class="subtle">Paid at</span>
      <span><?= $charge['paid_at'] ? out(date('Y-m-d H:i', strtotime($charge['paid_at']))) : '—' ?></span>
    </div>
  </div>

  <div class="controls">
    <a href="<?= BASE_URL ?>users/dashboard" class="btn" style="text-decoration:none">Back to dashboard</a>
    <button class="btn" onclick="window.print()">Print</button>
  </div>
</div>
