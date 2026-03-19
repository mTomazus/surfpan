<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign Changes — 100 Exercises</title>
  <style>
    :root {
      --bg: #0b1020;
      --card: #121935;
      --ink: #e8edf7;
      --muted: #9fb2d4;
      --accent: #4ad9c7;
      --ok: #1ad176;
      --warn: #ffb020;
      --bad: #ff5563;
      --shadow: 0 6px 22px rgba(0,0,0,.35);
      --radius: 16px;
    }
    html, body { height: 100%; }
    body {
      margin: 0; background: var(--bg); color: var(--ink);
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Inter, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
      line-height: 1.5; 
    }
    .wrap { max-width: 1100px; margin: 32px auto; padding: 0 16px; }
    header { display: grid; gap: 10px; margin-bottom: 18px; }
    h1 { margin: 0; font-weight: 800; font-size: clamp(28px, 3.2vw, 40px); letter-spacing:.2px; }
    p.lead { margin: 0; color: var(--muted); font-size: clamp(14px, 1.7vw, 16px); }

    .controls { 
      display: flex; flex-wrap: wrap; gap: 10px; margin: 18px 0 22px; 
    }
    button, .btn {
      background: var(--accent); color: #041e1a; border: 0; padding: 10px 14px;
      border-radius: 999px; font-weight: 700; cursor: pointer; box-shadow: var(--shadow);
    }
    button.secondary { background: #243157; color: var(--ink); }
    button.ghost { background: transparent; color: var(--ink); border: 1px solid #2f3e6b; }

    .grid {
      display: grid; gap: 12px; 
      grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    @media (min-width: 720px) {
      .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    .card {
      background: var(--card); border-radius: var(--radius); padding: 16px; box-shadow: var(--shadow);
      display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 12px;
    }
    .expr { font-variant-numeric: tabular-nums; font-size: 18px; }
    .expr kbd { 
      display: inline-block; min-width: 80px; padding: 6px 8px; text-align: center; 
      border-radius: 10px; background: #0e1531; border: 1px solid #2b3a6e; color: var(--ink); 
      box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
    }
    .expr input[type="number"] {
      width: 110px; padding: 8px 10px; border-radius: 10px; border: 1px solid #31406e; background: #0e1531; color: var(--ink);
      font-size: 16px; outline: none;
    }

    .status { font-size: 13px; color: var(--muted); }
    .badge { 
      font-size: 12px; padding: 4px 8px; border-radius: 999px; display: inline-flex; align-items: center; gap: 6px;
      border: 1px solid #2f3e6b; color: var(--muted);
    }
    .badge.ok { color: #062715; background: rgba(26, 209, 118, .18); border-color: rgba(26, 209, 118, .4); }
    .badge.bad { color: #2b090c; background: rgba(255, 85, 99, .18); border-color: rgba(255, 85, 99, .4); }

    details { margin-top: 8px; }
    summary { cursor: pointer; color: var(--muted); }
    .ans { color: var(--ink); font-weight: 700; }
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Sign Changes — 100 Exercises</h1>
      <p class="lead">Practice handling negatives and parentheses. Fill the blank so the equation is true. Tip: <em>−(x)</em> means subtract the value; <em>−(−b)</em> becomes <em>+b</em>.</p>
      <div class="controls">
        <button id="checkAll">Check all</button>
        <button id="reveal" class="secondary">Reveal all answers</button>
        <button id="reset" class="ghost">Clear inputs</button>
      </div>
    </header>

    <section class="grid" id="grid"></section>
  </div>

  <script>
    // --- Seeded RNG for reproducible set ---
    let seed = 20251026;
    function rnd() { seed = (seed * 1664525 + 1013904223) >>> 0; return seed / 4294967296; }
    function randInt(min, max) { return Math.floor(rnd() * (max - min + 1)) + min; }
    function randNonZero(min, max) { let v; do { v = randInt(min, max); } while (v === 0); return v; }

    const ops = ['+','-'];
    const inSigns = ['+','-'];

    function formatSigned(n) {
      // show -5 or +5 (used inside parentheses for inner term)
      return (n >= 0 ? '+' : '') + String(n);
    }

    function computeS(a, op1, signB, b) {
      const inner = (signB === '+') ? +b : -b;
      return op1 === '+' ? (a + inner) : (a - inner);
    }

    function computeX(S, opBlank, C) {
      // S (opBlank) x = C  =>  if + then x = C - S; if - then x = S - C
      return opBlank === '+' ? (C - S) : (S - C);
    }

    function makeExercise(idx) {
      const A = randNonZero(-20, 20);
      const op1 = ops[randInt(0, ops.length - 1)];
      const signB = inSigns[randInt(0, inSigns.length - 1)];
      const B = randInt(1, 20);
      const S = computeS(A, op1, signB, B);
      const opBlank = ops[randInt(0, ops.length - 1)];
      const targetX = randNonZero(-15, 15);
      const C = (opBlank === '+') ? (S + targetX) : (S - targetX);

      const xAnswer = targetX; // by construction

      const card = document.createElement('article');
      card.className = 'card';

      const expr = document.createElement('div');
      expr.className = 'expr';

      // Pretty-print A with its sign when negative
      const aStr = String(A);
      const inStr = `(${signB}${B})`;

      expr.innerHTML = `
        <div aria-label="Exercise ${idx+1}">
          <span class="badge" title="Exercise number">#${String(idx+1).padStart(2,'0')}</span>
          &nbsp; &nbsp;
          <span class="formula">
            <span class="a">${aStr}</span>
            <span class="op1"> ${op1} </span>
            <span class="inner">${inStr}</span>
            <span class="opb"> ${opBlank} </span>
            <span class="blank">( <input type="number" inputmode="numeric" aria-label="Your answer for exercise ${idx+1}" /> )</span>
            <span class="eq"> = </span>
            <span class="c">${C}</span>
          </span>
        </div>
        <details>
          <summary>Show hint / answer</summary>
          <div class="status">
            Constant part S = <code>${A} ${op1} (${signB}${B})</code> = <strong>${S}</strong><br />
            Then <code>${S} ${opBlank} x = ${C}</code> ⇒ <strong>x = ${xAnswer}</strong>.
          </div>
        </details>
      `;

      const status = document.createElement('div');
      status.className = 'status';
      status.textContent = 'Enter a value for the blank.';

      card.appendChild(expr);
      card.appendChild(status);

      const input = card.querySelector('input');

      function updateStatus() {
        const val = input.value.trim();
        if (val === '') { status.className = 'status'; status.textContent = 'Enter a value for the blank.'; return; }
        const user = Number(val);
        const ok = (user === xAnswer);
        status.className = 'status';
        status.innerHTML = ok
          ? `<span class="badge ok">✔ Correct</span>`
          : `<span class="badge bad">✖ Try again</span>`;
      }

      input.addEventListener('input', updateStatus);

      // attach helpers for batch actions
      card.__answer = xAnswer;
      card.__input = input;

      return card;
    }

    const grid = document.getElementById('grid');

    // Build 100 exercises
    const cards = [];
    for (let i = 0; i < 100; i++) {
      const c = makeExercise(i);
      cards.push(c);
      grid.appendChild(c);
    }

    // Controls
    document.getElementById('checkAll').addEventListener('click', () => {
      cards.forEach(c => {
        c.__input.dispatchEvent(new Event('input', { bubbles: true }));
      });
    });

    document.getElementById('reveal').addEventListener('click', () => {
      cards.forEach(c => {
        c.__input.value = c.__answer;
        c.__input.dispatchEvent(new Event('input', { bubbles: true }));
      });
    });

    document.getElementById('reset').addEventListener('click', () => {
      cards.forEach(c => {
        c.__input.value = '';
        c.__input.dispatchEvent(new Event('input', { bubbles: true }));
      });
    });
  </script>
</body>
</html>
