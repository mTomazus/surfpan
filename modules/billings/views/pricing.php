<?php
// modules/billing/views/pricing.php

if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

// You can tweak these routes in your app:
$start_free_url   = BASE_URL.'organizations/';                     // Starter (no payment)
$start_pro_url    = BASE_URL.'billing/start?plan=pro&period=month';  // Will be updated by JS when toggling
$start_biz_url    = BASE_URL.'billing/start?plan=business&period=month';
$event_pass_url   = BASE_URL.'billing/checkout-event-pass?code=event_pass';
?>

<div class="container-xxl">
<!-- Page title -->
    <div style="text-align:center;margin-bottom:20px;">
        <h1 style="margin:0 0 10px;font-size:34px;">Pricing Plans</h1>
        <p style="margin:0;color:var(--text-dark);font-size:16px;">
            Pick a plan that fits your organization. Upgrade anytime.
        </p>
    </div>

    <section id="pricing-plans" style="max-width:1100px;margin:40px auto;padding:0 20px;">

        <!-- Billing period toggle -->
        <div style="display:flex;justify-content:center;margin:18px 0 28px;">
            <div style="display:inline-flex;border;overflow:hidden;">
                <button id="btn-month" type="button"
                        style="padding:10px 16px;border:2px solid;background:light-dark(var(--accent), var(--opposite));color:var(--primary-white);cursor:pointer;box-shadow: none;border-top-left-radius: 25px;border-bottom-left-radius: 25px;">
                Monthly
                </button>
                <button id="btn-year" type="button"
                        style="padding:10px 16px;border:2px solid;background:transparent;color:light-dark(var(--accent), var(--opposite));opacity:.8;cursor:pointer;position:relative;box-shadow: none;border-top-right-radius: 25px;border-bottom-right-radius: 25px;">
                Annual
                    <span class="d-sm-none" style="font-size:12px;color:var(--primary-ink); background:light-dark(var(--opposite), var(--accent)); border:1px solid rgba(74,217,199,.35);
                                padding:2px 6px;border-radius:999px;margin-left:6px;position:relative;top:-1px;">
                        save 2 months
                    </span>
                </button>
            </div>
        </div>

        <!-- Plans grid -->
        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));grid-template-rows: repeat(5, auto);gap:18px;">
        <!-- Starter -->
            <article class="card p-1 sub-row-5">
                <h2 style="margin:0 0 6px;font-size:35px;">Starter</h2>
                <p style="margin:0 0 12px;">For small clubs getting started.</p>
                <div>
                    <div style="font-size:30px;font-weight:800;"><i class="fa fa-euro"></i> 0</div>
                    <div style="font-size:13px;">forever</div>
                </div>
                <ul style="list-style:none;padding:0;margin:14px 0;display:grid;gap:8px;font-size:14px;">
                    <li>• 1 event / year</li>
                    <li>• Up to 32 participants/event</li>
                    <li>• Basic live page</li>
                    <li>• Judge scoring</li>
                </ul>
                <a class="cta-btn" href="<?= h($start_free_url) ?>"
                style="display:block;text-align:center;padding:12px;border-radius:10px;background:var(--ring);color:var(--primary-ink);
                        font-weight:700;text-decoration:none;">Get Started</a>
            </article>

            <!-- Pro -->
            <article  class="card p-1 sub-row-5">
                <div style="position:relative;">
                    <div style="position:absolute;right:10px;font-size:12px;padding:4px 8px;border-radius:999px;
                                background: hsl(175, 60%, 50%);border:1px solid hsl(175, 60%, 20%);color:var(--accent);">
                    Most popular
                    </div>
                    <h2 style="margin:0 0 6px;font-size:35px;">Pro</h2>
                </div>
                <p style="margin:0 0 12px;">Serious organizers running multiple events.</p>

                <!-- Price wrapper with data attributes for toggle -->
                <div data-price-wrap>
                    <div data-price-month style="font-size:30px;font-weight:800;"><i class="fa fa-euro"></i> 14</div>
                    <div data-note-month style=";font-size:13px;">per month</div>

                    <div data-price-year style="font-size:30px;font-weight:800;display:none;"><i class="fa fa-euro"></i> 120</div>
                    <div data-note-year style="font-size:13px;display:none;">per year (save <i class="fa fa-euro"></i> 48)</div>
                </div>

                <ul style="list-style:none;padding:0;margin:14px 0;display:grid;gap:8px;font-size:14px;">
                    <li>• Unlimited events</li>
                    <li>• Up to 128 participants/event</li>
                    <li>• Public org & event pages</li>
                    <li>• Custom branding</li>
                    <li>• Judge analytics & exports</li>
                </ul>
                <a id="cta-pro" class="cta-btn" href="<?= h($start_pro_url) ?>"
                style="display:block;text-align:center;padding:12px;border-radius:10px;background:var(--ring);color:var(--primary-ink);
                        font-weight:700;text-decoration:none;">Start Pro</a>
            </article>

            <!-- Business -->
            <article class="card p-1 sub-row-5">
                <h2 style="margin:0 0 6px;font-size:35px;">Business</h2>
                <p style="margin:0 0 12px;">Larger organizations & federations.</p>

                <div data-price-wrap>
                    <div data-price-month style="font-size:30px;font-weight:800;"><i class="fa fa-euro"></i> 39</div>
                    <div data-note-month style="font-size:13px;">per month</div>

                    <div data-price-year style="font-size:30px;font-weight:800;display:none;"><i class="fa fa-euro"></i> 390</div>
                    <div data-note-year style="font-size:13px;display:none;">per year (save <i class="fa fa-euro"></i> 78)</div>
                </div>

                <ul style="list-style:none;padding:0;margin:14px 0;display:grid;gap:8px;font-size:14px;">
                    <li>• Unlimited events</li>
                    <li>• 4 admin seats</li>
                    <li>• API access</li>
                    <li>• Priority support</li>
                    <li>• Advanced analytics</li>
                </ul>
                <a id="cta-biz" class="cta-btn" href="<?= h($start_biz_url) ?>"
                style="display:block;text-align:center;padding:12px;border-radius:10px;background:var(--accent);border:1px solid var(--accent);
                        color:var(--primary-white);font-weight:700;text-decoration:none;">Start Business</a>
            </article>
            
        </div>

        <!-- Add-on -->
        <section style="margin-top:22px;">
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;">
                <div style="background:var(--card);border:1px solid var(--stroke);border-radius:16px;padding:18px;">
                <h3 style="margin:0 0 10px;font-size:18px;">Event Pass (add-on)</h3>
                <p style="margin:0;font-size:14px;">
                    Need one more event this month on Starter or Pro? Buy a single-event pass.
                </p>
                </div>
                <div style="background:var(--card);border:1px solid var(--stroke);border-radius:16px;padding:18px;display:grid;align-content:center;">
                <div style="font-size:22px;font-weight:800;"><i class="fa fa-euro"></i> 29</div>
                <a class="cta-btn" href="<?= h($event_pass_url) ?>"
                    style="margin-top:10px;display:block;text-align:center;padding:10px;border-radius:10px;background:var(--ring);color:var(--primary-ink);
                            font-weight:700;text-decoration:none;">Buy Event Pass</a>
                </div>
            </div>
        </section>

        <!-- Comparison table -->
        <section style="margin-top:26px;">
            <h3 style="margin:0 0 10px;font-size:18px;">Compare plans</h3>
            <div style="overflow:auto;border:1px solid var(--stroke);border-radius:12px;">
            <table style="width:100%;border-collapse:collapse;min-width:720px;">
            <thead>
                <tr style="background:light-dark(var(--accent), var(--opposite));">
                <th style="text-align:left;padding:12px;border-bottom:1px solid var(--stroke);color:var(--bg)">Feature</th>
                <th style="text-align:center;padding:12px;border-bottom:1px solid var(--stroke);color:var(--bg)">Starter</th>
                <th style="text-align:center;padding:12px;border-bottom:1px solid var(--stroke);color:var(--bg)">Pro</th>
                <th style="text-align:center;padding:12px;border-bottom:1px solid var(--stroke);color:var(--bg)">Business</th>
                </tr>
            </thead>
            <tbody style="font-size:14px;">
                <tr>
                <td style="padding:10px;border-top:1px solid var(--stroke);">Events / year</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">1</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">Unlimited</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">Unlimited</td>
                </tr>
                <tr>
                <td style="padding:10px;border-top:1px solid var(--stroke);">Participants / event</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">32</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">128</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">Unlimited</td>
                </tr>
                <tr>
                <td style="padding:10px;border-top:1px solid var(--stroke);">Public pages (org & event)</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">Basic</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">✓</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">✓</td>
                </tr>
                <tr>
                <td style="padding:10px;border-top:1px solid var(--stroke);">Branding & colors</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">—</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">✓</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">✓</td>
                </tr>
                <tr>
                <td style="padding:10px;border-top:1px solid var(--stroke);">Judge analytics</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">—</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">✓</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">Advanced</td>
                </tr>
                <tr>
                <td style="padding:10px;border-top:1px solid var(--stroke);">Admin seats</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">1</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">2</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">4</td>
                </tr>
                <tr>
                <td style="padding:10px;border-top:1px solid var(--stroke);">API access</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">—</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">—</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">✓</td>
                </tr>
                <tr>
                <td style="padding:10px;border-top:1px solid var(--stroke);">Priority support</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">—</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">—</td>
                <td style="text-align:center;border-top:1px solid var(--stroke);">✓</td>
                </tr>
            </tbody>
            </table>
            </div>
            <p style="margin:8px 2px 0;color:var(--muted);font-size:12px;">
                * Prices include VAT. Annual plans billed upfront. Event Pass is a one-time purchase.
            </p>
        </section>
    </section>
</div>

<!-- Tiny JS to handle monthly/annual toggle and CTA links -->
<script>
    (function(){
      const btnM = document.getElementById('btn-month');
      const btnY = document.getElementById('btn-year');
      const wraps = document.querySelectorAll('[data-price-wrap]');
      const ctaPro = document.getElementById('cta-pro');
      const ctaBiz = document.getElementById('cta-biz');

      function setPeriod(p){
        wraps.forEach(w=>{
          const pm = w.querySelectorAll('[data-price-month],[data-note-month]');
          const py = w.querySelectorAll('[data-price-year],[data-note-year]');
          pm.forEach(el=> el.style.display = (p==='month' ? '' : 'none'));
          py.forEach(el=> el.style.display = (p==='year'  ? '' : 'none'));
        });
        // Update CTA query params
        if (ctaPro) {
          const url = new URL(ctaPro.href, window.location.origin);
          url.searchParams.set('period', p);
          ctaPro.href = url.toString();
        }
        if (ctaBiz) {
          const url = new URL(ctaBiz.href, window.location.origin);
          url.searchParams.set('period', p);
          ctaBiz.href = url.toString();
        }
        // Visual state
        if (p==='month') {
          btnM.style.background = 'light-dark(var(--accent), var(--opposite))';
          btnM.style.color = 'var(--bg)';
          btnM.style.border = 'none';
          btnM.style.opacity = '1';
          btnY.style.background = 'transparent';
          btnY.style.border = '2px solid';
          btnY.style.color = 'light-dark(var(--accent), var(--opposite))';
          btnY.style.opacity = '.8';
        } else {
            btnY.style.background = 'light-dark(var(--accent), var(--opposite))';
            btnY.style.color = 'var(--bg)';
            btnY.style.border = 'none';
            btnY.style.opacity = '1';
            btnM.style.background = 'transparent';
            btnM.style.color = 'light-dark(var(--accent), var(--opposite))';
            btnM.style.border = '2px solid';
            btnM.style.opacity = '.8';
        }
      }

      btnM.addEventListener('click', ()=> setPeriod('month'));
      btnY.addEventListener('click', ()=> setPeriod('year'));

      // default: monthly
      setPeriod('month');
    })();
</script>