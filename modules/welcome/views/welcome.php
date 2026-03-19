<style>
    .bg-gradi {
        background:radial-gradient(1000px 700px at 15% 10%, rgba(124,92,255,.24), transparent 55%),
                    radial-gradient(1000px 700px at 85% 15%, rgba(34,211,238,.18), transparent 55%),
                    radial-gradient(900px 700px at 45% 95%, rgba(34,197,94,.10), transparent 60%),
                    var(--bg);
    }
    .text-white {
        color: var(--text-light);
    }
    .text-primary {
        color: var(--primary);
    }
    .text-center {
        text-align: center;
    }
    .card .xl {
        font-size: 2.5rem;
        font-weight: 900;
        font-family: 'Lato', sans-serif;
        transition: 0.3s ease;
    }
    .section-cards, .prices-cards {
        display: grid;
        overflow-x: scroll;
        -ms-overflow-style: none;  /* Internet Explorer 10+ */
        scrollbar-width: none; /* Firefox */
        gap: 2rem;
    }
    .section-cards::-webkit-scrollbar, .prices-cards::-webkit-scrollbar { 
        display: none;  /* Older Safari and Chromium */
    }
    .prices-cards {
        grid-template-columns: repeat(5, 1fr);
        grid-template-rows: repeat(2, auto);
        max-width: 868px;
        padding: 0.5rem;
        margin-block: 2rem;
        & .card {
            padding: 0.5rem;
            display:grid;
            grid-template-rows: subgrid;
            grid-row:1 / 5
        }
        & p {
            margin-block-start: -15px;
            font-size: 0.5rem;
            & span {
                font-weight: 400;
            }
        }
    }
    .prices-cards .card {
        transition: 0.3s ease;
    }
    .prices-cards .card:hover {
        box-shadow: 0 0 5px var(--primary), 0 0 10px var(--primary) inset;
        transform: scale(1.05);
        transition: all 0.3s ease;
    }
    html, body {
        height: 100%;
        margin: 0;
        color-scheme:dark;
        scroll-behavior: smooth;
    }
    main {
        min-height: 100dvh;
        padding-inline: 0;
        padding-top: 0;
        color: var(--text-light);
        width: 100%;
        max-width: 100%;
        height: 100%;
        margin: 0 auto;
        overflow-y: scroll;
        scroll-snap-type: y mandatory;
    }
    section {
        padding-inline:10dvw;
        padding-top: 65px;
        min-height: 100dvh;
        max-width: 100%;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        scroll-snap-align: start;
    }
    h1 {
        line-height: 5rem;
        margin-bottom: 3rem;
    }
    h2 {
        font-size: 3rem;
    }
    h3 {
        font-size: 2rem;
    }
    h5 {
        color: var(--primary);
        border: 1px solid var(--primary);
        width: max-content;
        margin: auto;
        padding: .2rem .5rem;
        font-size: 0.8rem;
    }
    p {
        font-size: 1rem;
        letter-spacing: 2px;
        font-family: sans-serif;
        font-weight: 900;
    }
    .hero {
        padding-top:calc(65px + 2rem);
        padding-bottom: 2rem;
    }
    .hero:before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("images/abstract.svg");
        background-size: cover;
        background-position: center;
        opacity: 0.85;
        z-index: -1;
    }
    .last:after {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("images/vector.svg");
        background-size: cover;
        background-position: center;
        opacity: 0.85;
        transform: rotate(180deg);
        filter: hue-rotate(220deg);
        z-index: -1;
    }
    .stat p {
        margin:0;
        font-size:1rem;
        margin:0;
    }
    section {
        min-height: calc(100dvh - 65px);
    }
    section:not(.last, .hero) {
        background: black;
    }
    section > span {
        display: inline-flex;
        align-items: center;
        font-size: 1.25rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 10px;
        color: var(--primary);
        margin-bottom: 2rem;
    }
    section > span::before {
        content: "";
        display: inline-block;
        width: 5rem;
        height: 2px;
        background: var(--primary);
        margin-right: 0.5rem;
    }
    section:has(.hero-right) {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .section-cards {
        grid-template-columns: repeat(auto-fit, minmax(max-content, 1fr));
        grid-template-rows: repeat(4, auto);
    }
    .section-cards div {
        padding: 1.5rem;
        text-align: left;
    }
    .section-cards h4 {
        font-size: 1.25rem;
        color: var(--text-light);
    }
    .section-cards .card {
        display:grid;
        grid-template-rows: subgrid;
        grid-row:1 / 5
    }
    .hero-right, .hero-left {
        display: grid;
        gap: 2rem;
        width:50%
    }
    .uppercase {
        text-transform: uppercase;
    }
    .line {
        display: block;
        width: 5rem;
        height: 2px;
        background: var(--primary);
        margin: 1rem auto;
    }
    .font-mono {
        font-family: monospace;
        font-size: 0.65rem;
        color: var(--ok);
        text-align: right;
    }
    .arrows {
        display: none;
        justify-content: center;
        gap: 1rem;
        margin-top: 0rem;
    }
    .move-right {
        animation: move-right 2s ease-in-out infinite;
    }
    @keyframes move-right {
        0%, 100% { transform: translateX(0); opacity: 1; }
        50% { transform: translateX(10px); }
        
    }
    @media only screen and (max-width: 768px) {
        .arrows {
            display: flex;
        }
        .prices-cards {
            margin-block: 0;
            gap: 0.5rem;
            grid-template-columns: repeat(5, 170px);
            & .card {
                grid-column: span 1;
                gap: 0.5rem;
            }
        } 
            
        .grid-sm {
            display: grid;
            justify-content: center;
        } 
        section {
            padding-inline: 4vw;
        }
        section > span {
            font-size: 0.9rem;
            letter-spacing: 5px;
        }
        .stat p {
            font-size: 0.8rem;
        }
    }


</style>

<section class="hero">
    <span>Advanced Heat Management</span>
    <h1 class="text-left uppercase"><span class="gradient">Elevate every<br></span>score</h1>
    <p>The ultimate digital dashboard for head judges and competition directors. 
                    Manage heats, track real-time analytics, and ensure precision scoring for elite surfing events.</p>
    <div class="mt-3 flex justify-start gap-3 grid-sm">
        <a href="organizations" class="btn" style="font-size:1rem;padding:1rem;max-height: 37px;">Create Organization</a>
        <a href="users/login" class="btn invert" style="font-size:1rem;padding:1rem;max-height: 37px;">Log In Now</a>
    </div>
    <div class="mt-2 flex justify-end gap-2">
        <div class="stat">
            <p class="text-secondary" style="color: lawngreen;">24+</p>
            <p class="uppercase">Heat Formats</p>
        </div>
        <div class="stat">
            <p class="text-primary" style="color: skyblue;">0.1s</p>
            <p class="uppercase">Live Sync Speed</p>
        </div>
        <div class="stat">
            <p class="text-danger" style="color: red;">LIVE</p>
            <p class="uppercase">Event Tracking</p>
        </div>
    </div>
</section>

<section class="text-center" style="background: black;">
    <div class="section-title">
        <h3 class="uppercase mb-0" style="font-size: 2rem;">Engineered for <span style="color: var(--primary)">clarity</span></h3>
        <span class="line"></span>
        <p class="lead">Fast scoring, clear UI, and automation that prevents mistakes on the beach.</p>
    </div>
    <div class="section-cards mt-3">
        <div class="card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="skyblue" height="45px" width="45px" viewBox="0 0 640 640"><path d="M528 320C528 434.9 434.9 528 320 528C205.1 528 112 434.9 112 320C112 205.1 205.1 112 320 112C434.9 112 528 205.1 528 320zM64 320C64 461.4 178.6 576 320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320zM296 184L296 320C296 328 300 335.5 306.7 340L402.7 404C413.7 411.4 428.6 408.4 436 397.3C443.4 386.2 440.4 371.4 429.3 364L344 307.2L344 184C344 170.7 333.3 160 320 160C306.7 160 296 170.7 296 184z"/></svg>
            <h4 class="uppercase">Real-time Scoring</h4>
            <p>Designed for head judges, our dashboard offers a clean and intuitive interface that simplifies heat management and scoring.</p>
            <span class="font-mono">MODULE_01</span>
        </div>
        <div class="card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="lawngreen" height="45px" width="45px"  viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M524.2 444.7L327.7 542.5C325.4 543.5 322.9 544 320.4 544C317.9 544 315.4 543.5 313.1 542.5L116.5 444.7C112.5 442.7 112.5 439.4 116.5 437.4L163.6 414C165.9 413 168.4 412.5 170.9 412.5C173.4 412.5 175.9 413 178.2 414L313 481C315.3 482 317.8 482.5 320.3 482.5C322.8 482.5 325.3 482 327.6 481L462.4 414C464.7 413 467.2 412.5 469.7 412.5C472.2 412.5 474.7 413 477 414L524.1 437.4C528.1 439.4 528.1 442.6 524.1 444.6zM524.2 308.2L477.1 284.8C474.8 283.8 472.3 283.3 469.8 283.3C467.3 283.3 464.8 283.8 462.5 284.8L327.7 351.8C325.4 352.8 322.9 353.3 320.4 353.3C317.9 353.3 315.4 352.8 313.1 351.8L178.3 284.7C176 283.7 173.5 283.2 171 283.2C168.5 283.2 166 283.7 163.7 284.7L116.5 308.1C112.5 310.1 112.5 313.4 116.5 315.4L313 413.2C315.3 414.2 317.8 414.7 320.3 414.7C322.8 414.7 325.3 414.2 327.6 413.2L524.1 315.4C528.1 313.4 528.1 310.1 524.1 308.1zM116.5 194.4L313 284.7C317.7 286.6 323 286.6 327.7 284.7L524.2 194.4C528.2 192.5 528.2 189.5 524.2 187.7L327.7 97.4C323 95.5 317.7 95.5 313 97.4L116.5 187.7C112.5 189.5 112.5 192.6 116.5 194.4z"/></svg>
            <h4 class="uppercase">Heat Management</h4>
            <p>Complete control over athlete priority, heat duration, and progression rules. Manage multiple pods simultaneously.</p>
            <span class="font-mono">CORE_ENGINE</span>
        </div>
        <div class="card">
            <svg xmlns="http://www.w3.org/2000/svg" fill="red" height="45px" width="45px"  viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M96 96C113.7 96 128 110.3 128 128L128 464C128 472.8 135.2 480 144 480L544 480C561.7 480 576 494.3 576 512C576 529.7 561.7 544 544 544L144 544C99.8 544 64 508.2 64 464L64 128C64 110.3 78.3 96 96 96zM208 288C225.7 288 240 302.3 240 320L240 384C240 401.7 225.7 416 208 416C190.3 416 176 401.7 176 384L176 320C176 302.3 190.3 288 208 288zM352 224L352 384C352 401.7 337.7 416 320 416C302.3 416 288 401.7 288 384L288 224C288 206.3 302.3 192 320 192C337.7 192 352 206.3 352 224zM432 256C449.7 256 464 270.3 464 288L464 384C464 401.7 449.7 416 432 416C414.3 416 400 401.7 400 384L400 288C400 270.3 414.3 256 432 256zM576 160L576 384C576 401.7 561.7 416 544 416C526.3 416 512 401.7 512 384L512 160C512 142.3 526.3 128 544 128C561.7 128 576 142.3 576 160z"/></svg>                <h4 class="uppercase">Judge Analytics</h4>
            <p>In-depth variance tracking to ensure scoring consistency. Post-heat reviews with video integration and historical data.</p>
            <span class="font-mono">DATA_VIZ</span>
        </div>
    </div>
    <div class="arrows move-right">
        <svg xmlns="http://www.w3.org/2000/svg" fill="lawngreen" height="20px" width="20px" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M403.7 107.1C392.1 96 375 92.9 360.3 99.2C345.6 105.5 336 120 336 136L336 272.3L163.7 107.2C152.1 96 135 92.9 120.3 99.2C105.6 105.5 96 120 96 136L96 504C96 520 105.6 534.5 120.3 540.8C135 547.1 152.1 544 163.7 532.9L336 367.7L336 504C336 520 345.6 534.5 360.3 540.8C375 547.1 392.1 544 403.7 532.9L595.7 348.9C603.6 341.4 608 330.9 608 320C608 309.1 603.5 298.7 595.7 291.1L403.7 107.1z"/></svg>
        <svg xmlns="http://www.w3.org/2000/svg" fill="lawngreen" height="20px" width="20px" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M403.7 107.1C392.1 96 375 92.9 360.3 99.2C345.6 105.5 336 120 336 136L336 272.3L163.7 107.2C152.1 96 135 92.9 120.3 99.2C105.6 105.5 96 120 96 136L96 504C96 520 105.6 534.5 120.3 540.8C135 547.1 152.1 544 163.7 532.9L336 367.7L336 504C336 520 345.6 534.5 360.3 540.8C375 547.1 392.1 544 403.7 532.9L595.7 348.9C603.6 341.4 608 330.9 608 320C608 309.1 603.5 298.7 595.7 291.1L403.7 107.1z"/></svg>
        <svg xmlns="http://www.w3.org/2000/svg" fill="lawngreen" height="20px" width="20px" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M403.7 107.1C392.1 96 375 92.9 360.3 99.2C345.6 105.5 336 120 336 136L336 272.3L163.7 107.2C152.1 96 135 92.9 120.3 99.2C105.6 105.5 96 120 96 136L96 504C96 520 105.6 534.5 120.3 540.8C135 547.1 152.1 544 163.7 532.9L336 367.7L336 504C336 520 345.6 534.5 360.3 540.8C375 547.1 392.1 544 403.7 532.9L595.7 348.9C603.6 341.4 608 330.9 608 320C608 309.1 603.5 298.7 595.7 291.1L403.7 107.1z"/></svg>
    </div>
</section>
<section class="text-center grid">
    <div style="margin:auto;max-width:800px;">    
        <h2 class="uppercase">What size is your wave?</h2>
        <p class="lead">Scaling with your competition size. Cyber-retro pricing for the modern surf official. High performance scoring, minimal latency.</p>
        <h4 class="gradient"><i class="fa fa-money mr-1" aria-hidden="true"></i> ORGANISERS PAY AT HEAT GENERATION</h4>
    </div>
    <!-- Simplified Pricing Grid -->
    <div class="justify-center prices-cards">
        <!-- Tier 1: club -->
        <div class="card">
            <span class="text-primary text-center uppercase mb-1">Surfclub</span>
            <div>
                <h3 class="text-white text-center xl uppercase italic">FREE</h3>
                <p class="text-slate-400 text-sm font-medium uppercase tracking-tight"><span>&#8804;</span>12 Participants</p>
            </div>
        </div>
        <!-- Tier 2: Regional -->
        <div class="card">
            <span class="text-primary text-center uppercase mb-1">Regional</span>
            <div>
                <h3 class="text-white text-center xl font-black uppercase italic">29 <i class="fa fa-euro"></i></h3>
                <p class="text-slate-400 text-sm font-medium uppercase tracking-tight"><span>&#8804;</span>24 Participants</p>
            </div>
        </div>
        <!-- Tier 3: National -->
        <div class="card">
            <span class="text-primary text-center uppercase mb-1">National</span>
            <div>
                <h3 class="text-white text-center xl font-black uppercase italic">59 <i class="fa fa-euro"></i></h3>
                <p class="text-slate-400 text-sm font-medium uppercase tracking-tight"><span>&#8804;</span>50 Participants</p>
            </div>
        </div>
        <!-- Tier 4: International -->
        <div class="card">
            <span class="text-primary text-center uppercase mb-1">Inter</span>
            <div>
                <h3 class="text-white text-center xl font-black uppercase italic">79 <i class="fa fa-euro"></i></h3>
                <p class="text-slate-400 text-sm font-medium uppercase tracking-tight"><span>&#8804;</span>75 Participants</p>
            </div>
        </div>
        <!-- Tier 5: World -->
        <div class="card">
            <span class="text-primary text-center uppercase mb-1">World</span>
            <div>
                <h3 class="text-white text-center xl font-black uppercase italic">99 <i class="fa fa-euro"></i></h3>
                <p class="text-slate-400 text-sm font-medium uppercase tracking-tight"><span>&#62;</span>75 Participants</p>
            </div>
        </div>
    </div>
</section>
<section class="text-center grid last">
    <div style="margin:auto;max-width:800px;">    
        <h5 class="uppercase" style="color:var(--primary); border:1px solid var(--primary);">system status: operational</h5>
        <h2 class="uppercase">Ready for the next swell?</h2>
        <p class="lead">Join the future of surf competition management. Sign up now to experience the power of precision scoring and seamless heat management powered by SurfPan cutting edge technology.</p>
        <div class="mt-3 grid-sm flex justify-center gap-3">
            <a href="organizations" class="btn invert uppercase" style="font-size:1rem;padding:1rem;max-height: 37px;">Get Started</a>
            <a href="heats" class="btn uppercase" style="font-size:1rem;padding:1rem;max-height: 37px;">Search Events</a>
        </div>
    </div>
</section>