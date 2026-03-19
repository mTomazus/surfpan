
<style>
    :root{
      --bg:#070A12;
      --panel:rgba(255,255,255,.06);
      --panel2:rgba(255,255,255,.09);
      --text:rgba(255,255,255,.92);
      --muted:rgba(255,255,255,.68);
      --muted2:rgba(255,255,255,.55);
      --border:rgba(255,255,255,.12);
      --shadow: 0 18px 60px rgba(0,0,0,.45);
      --radius: 20px;

      --accent: #7C5CFF; /* purple */
      --accent2:#22D3EE; /* cyan */
      --good:#22C55E;
      --warn:#F59E0B;

      --container: 1160px;
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
      background:
        radial-gradient(1000px 700px at 15% 10%, rgba(124,92,255,.24), transparent 55%),
        radial-gradient(1000px 700px at 85% 15%, rgba(34,211,238,.18), transparent 55%),
        radial-gradient(900px 700px at 45% 95%, rgba(34,197,94,.10), transparent 60%),
        var(--bg);
      color:var(--text);
      line-height:1.5;
      overflow-x:hidden;
    }

    a{color:inherit; text-decoration:none}
    .container{max-width:var(--container); margin:0 auto; padding:0 20px}
    .pill{
      display:inline-flex; align-items:center; gap:10px;
      padding:8px 12px; border:1px solid var(--border);
      background:rgba(255,255,255,.04);
      border-radius:999px;
      color:var(--muted);
      font-size:13px;
      backdrop-filter: blur(10px);
    }
    .dot{width:8px; height:8px; border-radius:999px; background:linear-gradient(135deg,var(--accent),var(--accent2))}
    .btn{
      display:inline-flex; align-items:center; justify-content:center; gap:10px;
      padding:12px 16px;
      border-radius:14px;
      border:1px solid var(--border);
      background:rgba(255,255,255,.06);
      color:var(--text);
      font-weight:650;
      letter-spacing:.2px;
      transition: transform .15s ease, background .15s ease, border-color .15s ease, box-shadow .15s ease;
      cursor:pointer;
      user-select:none;
    }
    .btn:hover{transform: translateY(-1px); background:rgba(255,255,255,.09); border-color:rgba(255,255,255,.18)}
    .btn:active{transform: translateY(0)}
    .btn.primary{
      border-color: rgba(124,92,255,.55);
      background: linear-gradient(135deg, rgba(124,92,255,.95), rgba(34,211,238,.35));
      box-shadow: 0 14px 40px rgba(124,92,255,.25);
    }
    .btn.primary:hover{box-shadow: 0 18px 60px rgba(124,92,255,.32)}
    .btn.ghost{background:transparent}
    .btn.small{padding:10px 12px; border-radius:12px; font-size:14px}

    .hero{
      padding: 56px 0 18px;
    }
    .hero-grid{
      display:grid;
      grid-template-columns: 1.05fr .95fr;
      gap: 26px;
      align-items:center;
      padding: 30px;
      border-radius: calc(var(--radius) + 8px);
      border:1px solid rgba(255,255,255,.10);
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
      box-shadow: var(--shadow);
      position:relative;
      overflow:hidden;
    }
    .hero-grid:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(700px 300px at 10% 0%, rgba(124,92,255,.20), transparent 60%),
        radial-gradient(700px 300px at 90% 10%, rgba(34,211,238,.14), transparent 60%);
      pointer-events:none;
      filter:saturate(1.1);
    }
    .hero-left, .hero-right{position:relative}
    h1{
      font-size: 48px;
      line-height: 1.05;
      margin: 14px 0 12px;
      letter-spacing: -.8px;
    }
    .subhead{
      color:var(--muted);
      font-size: 16.5px;
      margin: 0 0 18px;
      max-width: 58ch;
    }
    .cta-row{display:flex; gap:12px; flex-wrap:wrap; margin: 18px 0 16px}
    .trust{
      display:flex; gap:14px; flex-wrap:wrap;
      color:var(--muted2);
      font-size:13px;
      margin-top:10px;
    }
    .trust span{
      display:inline-flex; gap:8px; align-items:center;
      padding:8px 10px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.04);
    }
    .check{
      width:18px; height:18px; border-radius:7px;
      display:inline-flex; align-items:center; justify-content:center;
      background: rgba(34,197,94,.16);
      border:1px solid rgba(34,197,94,.28);
      color:rgba(34,197,94,.95);
      font-weight:900;
      font-size:12px;
    }

    .mock{
      border-radius: calc(var(--radius) + 10px);
      border:1px solid rgba(255,255,255,.12);
      background:
        radial-gradient(900px 500px at 50% -20%, rgba(255,255,255,.12), transparent 55%),
        rgba(255,255,255,.04);
      overflow:hidden;
      box-shadow: 0 18px 70px rgba(0,0,0,.38);
    }
    .mock-top{
      display:flex; align-items:center; justify-content:space-between;
      padding:12px 14px;
      border-bottom:1px solid rgba(255,255,255,.10);
      background: rgba(0,0,0,.18);
    }
    .traffic{display:flex; gap:8px}
    .t-dot{width:10px; height:10px; border-radius:999px; background:rgba(255,255,255,.18)}
    .mock-title{color:var(--muted); font-size:13px}
    .mock-body{padding:14px}
    .score-grid{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:12px;
    }
    .card{
      border-radius: 16px;
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.05);
      padding:12px;
    }
    .card h4{margin:0 0 8px; font-size:14px; color:rgba(255,255,255,.86)}
    .mini-row{display:flex; justify-content:space-between; align-items:center; gap:10px; margin:8px 0}
    .tag{
      font-size:12px;
      color: rgba(255,255,255,.78);
      border:1px solid rgba(255,255,255,.12);
      padding:6px 10px;
      border-radius:999px;
      background: rgba(255,255,255,.04);
      white-space:nowrap;
    }
    .tag.red{border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.10)}
    .tag.green{border-color:rgba(34,197,94,.35); background:rgba(34,197,94,.10)}
    .tag.blue{border-color:rgba(59,130,246,.35); background:rgba(59,130,246,.10)}
    .tag.white{border-color:rgba(255,255,255,.22); background:rgba(255,255,255,.06)}
    .score{
      font-variant-numeric: tabular-nums;
      font-weight:800;
      font-size:14px;
      padding:6px 10px;
      border-radius:12px;
      background: rgba(124,92,255,.12);
      border:1px solid rgba(124,92,255,.22);
    }
    .mock-footer{
      margin-top:12px;
      display:flex; gap:10px; flex-wrap:wrap;
    }

    @media (max-width: 980px){
      .hero-grid{grid-template-columns:1fr; padding:22px}
      h1{font-size:40px}
    }
    @media (max-width: 520px){
      h1{font-size:34px}
      .subhead{font-size:15.5px}
      .score-grid{grid-template-columns:1fr}
    }

    section{padding: 54px 0}
    .section-head{
      display:flex; justify-content:space-between; align-items:flex-end; gap:18px;
      margin-bottom: 18px;
    }
    h2{
      margin:0;
      font-size: 28px;
      letter-spacing: -.4px;
      line-height: 1.15;
    }
    .lead{margin:6px 0 0; color:var(--muted); max-width:70ch}
    .grid3{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap:14px;
    }
    .grid2{
      display:grid;
      grid-template-columns: repeat(2, 1fr);
      gap:14px;
    }
    @media (max-width: 900px){
      .grid3{grid-template-columns:1fr}
      .grid2{grid-template-columns:1fr}
    }

    .feature{
      padding:16px;
      border-radius: 18px;
      border:1px solid rgba(255,255,255,.10);
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
      box-shadow: 0 14px 44px rgba(0,0,0,.25);
    }
    .icon{
      width:38px; height:38px; border-radius:14px;
      display:inline-flex; align-items:center; justify-content:center;
      border:1px solid rgba(255,255,255,.16);
      background: radial-gradient(circle at 30% 25%, rgba(255,255,255,.18), rgba(255,255,255,.06));
      margin-bottom:10px;
    }
    .feature h3{margin:0 0 6px; font-size:16px}
    .feature p{margin:0; color:var(--muted); font-size:14.5px}

    .steps .step{
      padding:18px;
      border-radius:18px;
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.04);
      position:relative;
      overflow:hidden;
    }
    .steps .num{
      font-weight:900;
      font-size:12px;
      color: rgba(255,255,255,.86);
      background: rgba(124,92,255,.18);
      border:1px solid rgba(124,92,255,.28);
      padding:6px 10px;
      border-radius:999px;
      display:inline-flex;
      margin-bottom:10px;
    }
    .steps h3{margin:0 0 6px; font-size:16px}
    .steps p{margin:0; color:var(--muted); font-size:14.5px}

    .split{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      align-items:stretch;
    }
    @media (max-width: 900px){.split{grid-template-columns:1fr}}
    .panel{
      border-radius: 22px;
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.04);
      padding: 20px;
      box-shadow: 0 18px 60px rgba(0,0,0,.28);
    }
    .panel ul{margin:12px 0 0; padding:0; list-style:none}
    .panel li{
      display:flex; gap:10px; align-items:flex-start;
      padding:10px 0;
      border-bottom:1px solid rgba(255,255,255,.08);
      color:var(--muted);
    }
    .panel li:last-child{border-bottom:none}
    .badge{
      font-size:12px;
      padding:6px 10px;
      border-radius:999px;
      background: rgba(34,197,94,.14);
      border:1px solid rgba(34,197,94,.24);
      color: rgba(34,197,94,.95);
      display:inline-flex;
      margin-left:10px;
      vertical-align:middle;
    }

    .pricing{
      display:grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      align-items:stretch;
    }
    @media (max-width: 1100px){.pricing{grid-template-columns:1fr 1fr}}
    @media (max-width: 680px){.pricing{grid-template-columns:1fr}}
    .price{
      border-radius: 22px;
      border:1px solid rgba(255,255,255,.10);
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
      padding: 18px;
      box-shadow: 0 16px 54px rgba(0,0,0,.24);
      display:flex; flex-direction:column;
      min-height: 100%;
    }
    .price.popular{
      border-color: rgba(124,92,255,.35);
      box-shadow: 0 22px 72px rgba(124,92,255,.18);
      background: radial-gradient(900px 240px at 50% 0%, rgba(124,92,255,.22), transparent 60%),
                  linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
    }
    .price h3{margin:0 0 6px}
    .price .desc{margin:0 0 12px; color:var(--muted); font-size:14px}
    .amount{
      font-size: 28px;
      font-weight: 900;
      letter-spacing:-.5px;
      margin: 4px 0 2px;
    }
    .per{color:var(--muted2); font-size:13px}
    .price ul{margin:14px 0 0; padding:0; list-style:none}
    .price li{display:flex; gap:10px; align-items:flex-start; color:var(--muted); padding:8px 0}
    .spacer{flex:1}
    .price .btn{width:100%; margin-top:14px}

    .faq{
      border-radius: 22px;
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.04);
      overflow:hidden;
    }
    .q{
      width:100%;
      text-align:left;
      padding:16px 18px;
      background: transparent;
      border:0;
      color: var(--text);
      font-weight: 700;
      display:flex;
      justify-content:space-between;
      align-items:center;
      cursor:pointer;
      border-bottom:1px solid rgba(255,255,255,.08);
    }
    .q span{color:var(--muted); font-weight:700}
    .a{
      max-height:0;
      overflow:hidden;
      transition:max-height .25s ease;
      border-bottom:1px solid rgba(255,255,255,.08);
    }
    .a p{
      margin:0;
      padding: 0 18px 16px;
      color:var(--muted);
    }
    .faq .item:last-child .q, .faq .item:last-child .a{border-bottom:none}

    .cta{
      padding: 30px;
      border-radius: calc(var(--radius) + 8px);
      border:1px solid rgba(255,255,255,.10);
      background:
        radial-gradient(900px 320px at 10% 0%, rgba(34,211,238,.18), transparent 60%),
        radial-gradient(900px 320px at 90% 10%, rgba(124,92,255,.22), transparent 60%),
        rgba(255,255,255,.04);
      box-shadow: var(--shadow);
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:18px;
      flex-wrap:wrap;
    }
    .cta h2{margin:0}
    .cta p{margin:6px 0 0; color:var(--muted); max-width:70ch}

    footer{
      padding: 36px 0 44px;
      color:var(--muted2);
      font-size: 13px;
    }
    .foot{
      display:flex; justify-content:space-between; gap:18px; flex-wrap:wrap;
      border-top:1px solid rgba(255,255,255,.08);
      padding-top:18px;
    }
    .links{display:flex; gap:14px; flex-wrap:wrap}
    .links a{color:var(--muted2)}
    .links a:hover{color:var(--text)}
  </style>

  <!-- HERO -->
  <section class="hero">
    <div class="container">
      <div class="hero-grid">
        <div class="hero-left">
          <div class="pill"><span class="dot"></span> Real-time judging for competitions</div>
          <h1>Run heats. Score faster. Publish instantly.</h1>
          <p class="subhead">
            A modern judging panel that keeps your event moving: live heat control, multi-judge scoring,
            automatic totals, and instant leaderboards — on any device.
          </p>

          <div class="cta-row">
            <a class="btn primary" href="#contact">Request a demo</a>
            <a class="btn" href="#features">See features</a>
            <a class="btn ghost" href="#pricing">Start free</a>
          </div>

          <div class="trust">
            <span><span class="check">✓</span> Works on phone/tablet/laptop</span>
            <span><span class="check">✓</span> Heat timer + auto status</span>
            <span><span class="check">✓</span> Export results (CSV/PDF)</span>
          </div>
        </div>

        <div class="hero-right">
          <!-- Mock UI preview (replace with real screenshot later) -->
          <div class="mock" role="img" aria-label="Judging panel preview">
            <div class="mock-top">
              <div class="traffic" aria-hidden="true">
                <div class="t-dot"></div><div class="t-dot"></div><div class="t-dot"></div>
              </div>
              <div class="mock-title">Live Heat — U18 Final • 08:32 remaining</div>
            </div>
            <div class="mock-body">
              <div class="score-grid">
                <div class="card">
                  <h4>Judge input</h4>
                  <div class="mini-row"><span class="tag white">WHITE</span><span class="score">7.5</span></div>
                  <div class="mini-row"><span class="tag red">RED</span><span class="score">6.8</span></div>
                  <div class="mini-row"><span class="tag green">GREEN</span><span class="score">8.2</span></div>
                  <div class="mini-row"><span class="tag blue">BLUE</span><span class="score">5.9</span></div>
                </div>
                <div class="card">
                  <h4>Live totals</h4>
                  <div class="mini-row"><span class="tag green">GREEN</span><span class="score">15.9</span></div>
                  <div class="mini-row"><span class="tag white">WHITE</span><span class="score">14.6</span></div>
                  <div class="mini-row"><span class="tag red">RED</span><span class="score">13.2</span></div>
                  <div class="mini-row"><span class="tag blue">BLUE</span><span class="score">11.4</span></div>
                </div>
              </div>
              <div class="mock-footer">
                <span class="tag">Auto wave number</span>
                <span class="tag">Top 2 waves</span>
                <span class="tag">Judge sync</span>
                <span class="tag">Results locked</span>
              </div>
            </div>
          </div>
          <p style="margin:10px 0 0; color:var(--muted2); font-size:12.5px">
            Replace this preview with your real app screenshot whenever you’re ready.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- LOGOS / SOCIAL PROOF -->
  <section style="padding-top:22px">
    <div class="container">
      <div class="panel" style="padding:16px 18px">
        <div style="display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:center">
          <div>
            <strong style="font-size:14px">Built for real events</strong>
            <div style="color:var(--muted); font-size:13px; margin-top:3px">
              Perfect for surf, board sports, and any heat-based competition.
            </div>
          </div>
          <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:flex-end">
            <span class="tag">Live leaderboards</span>
            <span class="tag">Brackets + repechage</span>
            <span class="tag">Multi-division</span>
            <span class="tag">Audit-ready</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features">
    <div class="container">
      <div class="section-head">
        <div>
          <h2>Everything judges need — nothing they don’t</h2>
          <p class="lead">Fast scoring, clear UI, and automation that prevents mistakes on the beach.</p>
        </div>
      </div>

      <div class="grid3">
        <div class="feature">
          <div class="icon">⏱️</div>
          <h3>Heat control & timers</h3>
          <p>Schedule heats, start/finish automatically, and always load the currently running heat.</p>
        </div>

        <div class="feature">
          <div class="icon">🧑‍⚖️</div>
          <h3>Multi-judge scoring</h3>
          <p>Each judge scores in parallel. Scores aggregate instantly to rankings and totals.</p>
        </div>

        <div class="feature">
          <div class="icon">🌊</div>
          <h3>Wave-based workflow</h3>
          <p>Auto wave number + jersey selection. Built for “best 2 waves” style formats.</p>
        </div>

        <div class="feature">
          <div class="icon">📊</div>
          <h3>Live leaderboards</h3>
          <p>Show standings per heat or division in real-time on a public display page.</p>
        </div>

        <div class="feature">
          <div class="icon">🧩</div>
          <h3>Formats that match reality</h3>
          <p>Run repechage, semifinals, and finals without spreadsheet chaos.</p>
        </div>

        <div class="feature">
          <div class="icon">🔒</div>
          <h3>Audit & locking</h3>
          <p>Lock heats after finish, keep clean logs, export results when the horn ends.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section id="how">
    <div class="container">
      <div class="section-head">
        <div>
          <h2>From check-in to podium in minutes</h2>
          <p class="lead">A simple flow that keeps the beach crew, judges, and announcer in sync.</p>
        </div>
      </div>

      <div class="grid3 steps">
        <div class="step">
          <div class="num">STEP 1</div>
          <h3>Create event & divisions</h3>
          <p>Set divisions (age/gender), add participants, assign jersey colors automatically.</p>
        </div>
        <div class="step">
          <div class="num">STEP 2</div>
          <h3>Generate heats</h3>
          <p>Round 1 → repechage → finals. The system builds the bracket so you don’t have to.</p>
        </div>
        <div class="step">
          <div class="num">STEP 3</div>
          <h3>Judge live, publish instantly</h3>
          <p>Judges score waves, head judge monitors totals, results export with one click.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FORMATS / DETAILS -->
  <section id="formats">
    <div class="container">
      <div class="section-head">
        <div>
          <h2>Competition formats supported</h2>
          <p class="lead">Designed for real heat-based events — including repechage formats and multi-round progression.</p>
        </div>
      </div>

      <div class="split">
        <div class="panel">
          <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap">
            <h3 style="margin:0">Standard event toolkit</h3>
            <span class="badge">Most popular</span>
          </div>
          <ul>
            <li><span class="check">✓</span> 4-person heats (White/Red/Green/Blue)</li>
            <li><span class="check">✓</span> Auto current heat loading for judges</li>
            <li><span class="check">✓</span> Heat statuses: pending / scheduled / running / finished</li>
            <li><span class="check">✓</span> Best-2-waves totals + tie handling (configurable)</li>
            <li><span class="check">✓</span> Live results per division + overall standings</li>
          </ul>
        </div>

        <div class="panel">
          <h3 style="margin:0">Operations & reliability</h3>
          <ul>
            <li><span class="check">✓</span> Mobile-first judging UI (big buttons, quick entry)</li>
            <li><span class="check">✓</span> Role-based access (judge / head judge / admin)</li>
            <li><span class="check">✓</span> Export: CSV / PDF (results, heats, scores)</li>
            <li><span class="check">✓</span> Public display mode for announcer / big screen</li>
            <li><span class="check">✓</span> Data safety: locks, logs, and backups (optional)</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- PRICING -->
  <section id="pricing">
    <div class="container">
      <div class="section-head">
        <div>
          <h2>Pricing that fits events</h2>
          <p class="lead">Start free for small sessions. Upgrade when your event grows.</p>
        </div>
      </div>

      <div class="pricing">
        <div class="price">
          <h3>Starter</h3>
          <p class="desc">For small events and practice days.</p>
          <div class="amount">€0</div>
          <div class="per">Free</div>
          <ul>
            <li><span class="check">✓</span> 1 event at a time</li>
            <li><span class="check">✓</span> Live judging panel</li>
            <li><span class="check">✓</span> Basic exports</li>
          </ul>
          <div class="spacer"></div>
          <a class="btn" href="#contact">Start free</a>
        </div>

        <div class="price popular">
          <h3>Event Pass</h3>
          <p class="desc">Best for federations & one-off contests.</p>
          <div class="amount">€—</div>
          <div class="per">Pay per event</div>
          <ul>
            <li><span class="check">✓</span> Full formats (repechage)</li>
            <li><span class="check">✓</span> Public leaderboard page</li>
            <li><span class="check">✓</span> Priority support during event</li>
          </ul>
          <div class="spacer"></div>
          <a class="btn primary" href="#contact">Request event pricing</a>
        </div>

        <div class="price">
          <h3>Pro</h3>
          <p class="desc">For clubs running multiple events.</p>
          <div class="amount">€—</div>
          <div class="per">Per month</div>
          <ul>
            <li><span class="check">✓</span> Unlimited events</li>
            <li><span class="check">✓</span> Advanced exports + branding</li>
            <li><span class="check">✓</span> Multiple organizers</li>
          </ul>
          <div class="spacer"></div>
          <a class="btn" href="#contact">Talk to sales</a>
        </div>

        <div class="price">
          <h3>Enterprise</h3>
          <p class="desc">Federations and large championships.</p>
          <div class="amount">Custom</div>
          <div class="per">SLA + integrations</div>
          <ul>
            <li><span class="check">✓</span> SSO / custom roles</li>
            <li><span class="check">✓</span> Data export pipeline</li>
            <li><span class="check">✓</span> Dedicated onboarding</li>
          </ul>
          <div class="spacer"></div>
          <a class="btn" href="#contact">Contact</a>
        </div>
      </div>

      <p style="margin:14px 0 0; color:var(--muted2); font-size:13px">
        Tip: Replace €— with your real numbers (or add “Free under 12 participants” if that’s your rule).
      </p>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq">
    <div class="container">
      <div class="section-head">
        <div>
          <h2>FAQ</h2>
          <p class="lead">Common questions from organizers and judges.</p>
        </div>
      </div>

      <div class="faq" id="faqBox">
        <div class="item">
          <button class="q" type="button">
            Does it work on the beach with shaky internet?
            <span>+</span>
          </button>
          <div class="a"><p>Yes — the UI is lightweight and designed for mobile. For best results, use a hotspot/router near the judges. Offline-first mode can be added if you want it.</p></div>
        </div>
        <div class="item">
          <button class="q" type="button">
            Can judges log in and only see their own panel?
            <span>+</span>
          </button>
          <div class="a"><p>Exactly. Judges access a dedicated judging page. Head judge/admin get event controls, heat management, and lock/export tools.</p></div>
        </div>
        <div class="item">
          <button class="q" type="button">
            Do you support repechage and multi-round formats?
            <span>+</span>
          </button>
          <div class="a"><p>Yes. Round 1 → repechage → finals and other progressions can be configured so heats generate automatically from placements.</p></div>
        </div>
        <div class="item">
          <button class="q" type="button">
            Can I show live results on a TV/projector?
            <span>+</span>
          </button>
          <div class="a"><p>Yes — use the public leaderboard/display page. Great for announcers, beach screens, and live streams.</p></div>
        </div>
      </div>
    </div>
  </section>

  <!-- FINAL CTA + CONTACT -->
  <section id="contact">
    <div class="container">
      <div class="cta">
        <div>
          <h2>Want a demo for your next contest?</h2>
          <p>Tell me your sport + number of participants, and I’ll show you the fastest setup.</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap">
          <a class="btn primary" href="mailto:hello@yourdomain.com?subject=Surfpan%20Demo%20Request&body=Hi%2C%0A%0AWe%20want%20a%20demo.%20Sport%3A%20____%0AParticipants%3A%20____%0ADate%3A%20____%0ALocation%3A%20____%0A%0AThanks!">
            Email for demo
          </a>
          <a class="btn" href="#top">Back to top</a>
        </div>
      </div>

      <div style="margin-top:14px; color:var(--muted2); font-size:13px">
        Replace <code style="color:rgba(255,255,255,.78)">hello@yourdomain.com</code> with your email, or wire this section to a real form.
      </div>
    </div>
  </section>

<footer>
  <div class="container">
    <div class="foot">
      <div>© <span id="year"></span> Surfpan Judging Panel. All rights reserved.</div>
      <div class="links">
        <a href="#features">Features</a>
        <a href="#pricing">Pricing</a>
        <a href="#faq">FAQ</a>
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
      </div>
    </div>
  </div>
</footer>

<script>
  // Year
  document.getElementById('year').textContent = new Date().getFullYear();

  // FAQ accordion
  document.querySelectorAll('.faq .q').forEach((btn) => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.item');
      const answer = item.querySelector('.a');
      const isOpen = answer.style.maxHeight && answer.style.maxHeight !== '0px';

      // close others
      document.querySelectorAll('.faq .a').forEach(a => {
        a.style.maxHeight = '0px';
        a.previousElementSibling.querySelector('span').textContent = '+';
      });

      if(!isOpen){
        answer.style.maxHeight = answer.scrollHeight + 'px';
        btn.querySelector('span').textContent = '–';
      }
    });
  });

  // Simple mobile menu hint (optional)
  const menuBtn = document.getElementById('menuBtn');
  if(menuBtn){
    menuBtn.addEventListener('click', () => {
      alert('On mobile, you can scroll or add a drawer menu. If you want, I can generate a real slide-in menu.');
    });
  }
</script>
