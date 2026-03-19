<?php
/**
 * FILE: modules/organizers/views/subscriptions.php
 * PURPOSE: Show organiser's current subscription, usage, upgrade options, and invoices
 * DATA expected from controller:
 *   $org (object): id, name
 *   $current_plan (array|null): ['plan_code','plan_name','status','renews_on','trial_ends_on','created_at','cancel_at_period_end']
 *   $usage (array): ['event_pass_total'=>int,'event_pass_used'=>int,'event_pass_remaining'=>int]
 *   $plans (array): list of available plans with features & price
 *   $invoices (array): recent invoices/charges
 *   $csrf_token (string): token for POST forms (Trongate tokens)
 */
?>

  <style>

    * { box-sizing: border-box; }
    .wrap { max-width: 1100px; margin: 24px auto 64px; padding: 0 16px; }
    .page-head { display:flex; align-items:center; justify-content:space-between; gap: 16px; margin-bottom: 20px; }
    .page-head h1 { font-size: 24px; margin:0; letter-spacing: .3px; }
    .breadcrumb { color: var(--subtext); font-size: 13px; }

    .grid { display:grid; grid-template-columns: 1.2fr .8fr; gap:20px; }
    @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }

    .card { background: var(--bg-elev); border:1px solid var(--border); border-radius: var(--radius); padding:20px; box-shadow: var(--shadow); }
    .card + .card { margin-top: 16px; }
    .card h2 { margin:0 0 12px; font-size: 18px; letter-spacing: .2px; }
    .muted { color: var(--subtext); }
    .row { display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
    .chip { display:inline-flex; align-items:center; gap:6px; padding: 6px 10px; border-radius: 999px; background:#0f1733; border:1px solid var(--border); font-size:12px; color: var(--subtext); }
    .chip.ok { color:#d6ffe9; background: #0d2a20; border-color:#164b34; }
    .chip.warn { color:#fff5e0; background: #2b2209; border-color:#5c4a15; }
    .chip.danger { color:#ffe5e9; background: #3a0f16; border-color:#6d1f2a; }

    .stat { display:grid; grid-template-columns: 1fr auto; gap:6px; padding:10px 12px; background:#0f1630; border:1px dashed var(--border); border-radius: 12px; }
    .stat .label { color: var(--subtext); font-size:12px; }
    .stat .value { font-weight:700; }

    .progress { height: 10px; background:#0f1733; border:1px solid var(--border); border-radius:999px; overflow:hidden; }
    .progress > i { display:block; height:100%; background: linear-gradient(90deg, var(--primary), #42b0ff); }

    .plans { display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; }
    @media (max-width: 1100px) { .plans { grid-template-columns: 1fr; } }

    .plan { background:#0e1530; border:1px solid var(--border); border-radius: var(--radius); padding:18px; position:relative; overflow:hidden; }
    .plan h3 { margin:0 0 6px; font-size:18px; }
    .price { font-size:26px; font-weight:800; margin: 8px 0 10px; }
    .feat { display:grid; gap:6px; margin: 10px 0 12px; }
    .feat li { list-style:none; display:flex; align-items:center; gap:8px; }
    .feat .dot { width:8px; height:8px; border-radius:50%; background: var(--primary); display:inline-block; }

    .btn { appearance:none; border:none; background:var(--primary); color:#001e1a; font-weight:700; padding:10px 14px; border-radius: 12px; cursor:pointer; box-shadow: 0 6px 20px var(--ring); }
    .btn.secondary { background:#0f1733; border:1px solid var(--border); color: var(--text); box-shadow: none; }
    .btn.ghost { background: transparent; border:1px dashed var(--border); color: var(--subtext); }
    .btn:disabled { opacity:.6; cursor:not-allowed; }

    table { width:100%; border-collapse: collapse; }
    th, td { text-align:left; padding:10px 8px; border-bottom:1px solid var(--border); font-size:14px; }
    th { color: var(--subtext); font-weight:600; }
    tr:hover td { background: #0f1531; }

    .notice { background:#0f1733; border:1px solid var(--border); border-radius:12px; padding:12px; color: var(--subtext); }
    .kbd { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 12px; padding:2px 6px; border-radius:6px; background:#0b1020; border:1px solid var(--border); color:#cbd5e1; }
  </style>

<div class="wrap">
  <div class="page-head">
    <div>
      <div class="breadcrumb">Organiser → Billing</div>
      <h1>Subscriptions & Billing</h1>
    </div>
    <form method="post" action="<?= BASE_URL ?>billing/portal">
      <input type="hidden" name="token" value="<?= $csrf_token ?>">
      <button class="btn secondary" title="Manage payment method">Open Billing Portal</button>
    </form>
  </div>

  <div class="grid">
    <!-- LEFT: Current plan & usage -->
    <section class="card">
      <h2>Current plan</h2>
      <?php if ($current_plan): ?>
        <div class="row" style="justify-content:space-between; gap:24px;">
          <div>
            <div class="row" style="gap:10px;">
              <span class="chip">Plan</span>
              <strong><?= out($current_plan['plan_name']) ?></strong>
            </div>
            <div class="muted" style="margin-top:8px;">
              Status: <span class="chip <?= $current_plan['status']==='active' ? 'ok' : ($current_plan['cancel_at_period_end']?'warn':'danger') ?>"> <?= out(ucfirst($current_plan['status'])) ?><?= $current_plan['cancel_at_period_end']? ' (cancels at period end)' : '' ?></span>
            </div>
            <?php if (!empty($current_plan['trial_ends_on'])): ?>
              <div class="muted">Trial ends: <strong><?= date('M j, Y', strtotime($current_plan['trial_ends_on'])) ?></strong></div>
            <?php endif; ?>
            <?php if (!empty($current_plan['renews_on'])): ?>
              <div class="muted">Renews on: <strong><?= date('M j, Y', strtotime($current_plan['renews_on'])) ?></strong></div>
            <?php endif; ?>
          </div>
          <div class="row" style="gap:8px;">
            <form method="post" action="<?= BASE_URL ?>billing/change_plan">
              <input type="hidden" name="token" value="<?= $csrf_token ?>">
              <input type="hidden" name="direction" value="downgrade">
              <button class="btn ghost"<?= $current_plan['plan_code']==='free' ? ' disabled' : '' ?>>Switch plan…</button>
            </form>
            <form method="post" action="<?= BASE_URL ?>billing/cancel">
              <input type="hidden" name="token" value="<?= $csrf_token ?>">
              <button class="btn ghost"<?= $current_plan['status']!=='active' ? ' disabled' : '' ?>>Cancel auto‑renew</button>
            </form>
          </div>
        </div>

        <hr style="border:none; border-top:1px solid var(--border); margin:16px 0;">

        <div class="row" style="gap:16px;">
          <div class="stat" style="min-width:220px;">
            <div class="label">Event Pass credits</div>
            <div class="label" style="text-align:right;">Remaining</div>
            <div class="value"><?= (int)$usage['event_pass_total'] ?> total</div>
            <div class="value" style="text-align:right;"><?= (int)$usage['event_pass_remaining'] ?></div>
            <div class="progress" style="grid-column:1 / -1; margin-top:8px;">
              <?php 
                $pct = 0; 
                if ($usage['event_pass_total']>0) { 
                  $pct = max(0, min(100, round(($usage['event_pass_used']/$usage['event_pass_total'])*100))); 
                }
              ?>
              <i style="width: <?= $pct ?>%"></i>
            </div>
            <div class="label" style="grid-column:1 / -1; text-align:right;">Used: <?= (int)$usage['event_pass_used'] ?></div>
          </div>

          <form method="post" action="<?= BASE_URL ?>billing/checkout" class="row" style="gap:10px;">
            <input type="hidden" name="token" value="<?= $csrf_token ?>">
            <input type="hidden" name="product_code" value="event_pass">
            <label class="chip">Add passes:</label>
            <select name="qty" class="chip" style="padding:8px 12px;">
              <option value="5">+5 passes</option>
              <option value="10">+10 passes</option>
              <option value="20">+20 passes</option>
              <option value="50">+50 passes</option>
            </select>
            <button class="btn" title="Buy more event passes">Purchase</button>
          </form>
        </div>
      <?php else: ?>
        <div class="notice">No active subscription found. Choose a plan below to get started.</div>
      <?php endif; ?>
    </section>

    <!-- RIGHT: Helpful info -->
    <aside class="card">
      <h2>What’s included</h2>
      <ul class="feat">
        <li><span class="dot"></span> Organiser dashboard & multi‑competition support</li>
        <li><span class="dot"></span> Judge management & role control</li>
        <li><span class="dot"></span> Live heat scoring & results pages</li>
        <li><span class="dot"></span> Event Pass credits for paid competitions</li>
        <li><span class="dot"></span> Billing portal & invoice history</li>
      </ul>
      <p class="muted">Tip: You can move between plans any time. Changes take effect next billing cycle. Use <span class="kbd">Billing → Portal</span> to update payment method.</p>
    </aside>
  </div>

  <!-- Plans -->
  <section class="card" style="margin-top:20px;">
    <h2>Upgrade / Change plan</h2>
    <div class="plans">
      <?php foreach ($plans as $plan): ?>
        <?php 
          $is_current = $current_plan && $current_plan['plan_code'] === $plan['plan_code'];
          $btn_label = $is_current ? 'Current plan' : 'Select';
        ?>
        <div class="plan">
          <h3><?= out($plan['plan_name']) ?></h3>
          <div class="muted">Ideal for: <?= out($plan['tagline']) ?></div>
          <div class="price">€<?= out($plan['price_eur']) ?><span class="muted" style="font-size:14px; font-weight:500;">/mo</span></div>
          <ul class="feat">
            <?php foreach ($plan['features'] as $f): ?>
              <li><span class="dot"></span><?= out($f) ?></li>
            <?php endforeach; ?>
          </ul>
          <form method="post" action="<?= BASE_URL ?>billing/checkout">
            <input type="hidden" name="token" value="<?= $csrf_token ?>">
            <input type="hidden" name="product_code" value="<?= out($plan['plan_code']) ?>">
            <button class="btn" <?= $is_current ? 'disabled' : '' ?>><?= $btn_label ?></button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Invoices -->
  <section class="card">
    <h2>Invoices & Charges</h2>
    <?php if (!empty($invoices)): ?>
      <div style="overflow:auto;">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Item</th>
              <th>Status</th>
              <th>Amount</th>
              <th>Receipt</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($invoices as $inv): ?>
              <tr>
                <td><?= date('Y-m-d', strtotime($inv['created_at'])) ?></td>
                <td><?= out($inv['description'] ?: strtoupper($inv['product_code'])) ?></td>
                <td><span class="chip <?= $inv['status']==='paid'?'ok':($inv['status']==='refunded'?'warn':'danger') ?>"><?= out(ucfirst($inv['status'])) ?></span></td>
                <td>€<?= number_format($inv['amount_eur']/100, 2) ?></td>
                <td>
                  <?php if (!empty($inv['receipt_url'])): ?>
                    <a class="btn secondary" href="<?= out($inv['receipt_url']) ?>" target="_blank" rel="noopener">Open</a>
                  <?php else: ?>
                    <span class="muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="notice">No invoices yet.</div>
    <?php endif; ?>
  </section>
</div>
