/* SurfPan landing — "Night Swell"
   three.js wireframe ocean + GSAP choreography.
   Degrades gracefully: no WebGL / reduced-motion / CDN blocked → static page. */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isMobile     = window.matchMedia('(max-width: 768px)').matches;
    var scroller     = document.querySelector('main.public_area');

    /* ---------------- three.js ocean ---------------- */
    function initOcean() {
        if (reduceMotion || typeof THREE === 'undefined') return;
        var canvas = document.getElementById('ocean-canvas');
        if (!canvas) return;
        var stage = canvas.parentElement;

        var renderer;
        try {
            renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: !isMobile });
        } catch (e) { return; }
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

        var scene = new THREE.Scene();
        scene.fog = new THREE.FogExp2(0x000208, 0.0022);

        var camera = new THREE.PerspectiveCamera(55, 1, 1, 2000);
        camera.position.set(0, 85, 320);

        var segs = isMobile ? 48 : 100;
        var geo  = new THREE.PlaneGeometry(1700, 1700, segs, segs);
        geo.rotateX(-Math.PI / 2);
        var pos  = geo.attributes.position;
        var base = Float32Array.from(pos.array);

        var sea = new THREE.Mesh(geo, new THREE.MeshBasicMaterial({
            color: 0x17e1ef, wireframe: true, transparent: true, opacity: 0.22
        }));
        scene.add(sea);

        var crests = new THREE.Points(geo, new THREE.PointsMaterial({
            color: 0xbdfbff, size: isMobile ? 1.6 : 2.2,
            transparent: true, opacity: 0.45, sizeAttenuation: true
        }));
        scene.add(crests);

        var mouseX = 0, t = 0, raf = null;

        function resize() {
            var w = stage.clientWidth, h = stage.clientHeight;
            renderer.setSize(w, h, false);
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
        }
        resize();
        window.addEventListener('resize', resize);

        if (!isMobile) {
            window.addEventListener('pointermove', function (e) {
                mouseX = e.clientX / window.innerWidth - 0.5;
            }, { passive: true });
        }

        function frame() {
            t += 0.006;
            for (var i = 0; i < pos.count; i++) {
                var x = base[i * 3], z = base[i * 3 + 2];
                pos.array[i * 3 + 1] =
                    Math.sin(x * 0.012 + t * 2.0) * 9 +
                    Math.sin(z * 0.016 - t * 2.6) * 14 +
                    Math.sin((x + z) * 0.008 + t * 1.4) * 7;
            }
            pos.needsUpdate = true;
            camera.position.x += (mouseX * 55 - camera.position.x) * 0.04;
            camera.lookAt(0, 12, 0);
            renderer.render(scene, camera);
            raf = requestAnimationFrame(frame);
        }
        frame();

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                if (raf) { cancelAnimationFrame(raf); raf = null; }
            } else if (!raf) {
                frame();
            }
        });
    }

    /* ---------------- GSAP choreography ---------------- */
    function initMotion() {
        var revealEls = document.querySelectorAll('[data-reveal]');

        if (reduceMotion || typeof gsap === 'undefined') {
            // Static fallback: ensure nothing stays hidden.
            revealEls.forEach(function (el) { el.style.opacity = 1; });
            document.querySelectorAll('[data-count]').forEach(function (el) {
                el.textContent = el.dataset.count + (el.dataset.suffix || '');
            });
            return;
        }

        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);
            ScrollTrigger.defaults({ scroller: scroller });
        }

        /* Hero entrance */
        gsap.timeline({ defaults: { ease: 'power3.out' } })
            .from('.hero .kicker',         { y: 26, opacity: 0, duration: 0.6, delay: 0.15 })
            .from('.hero .h-line',         { yPercent: 115, duration: 0.9, stagger: 0.14 }, '-=0.25')
            .from('.hero .lead',           { y: 24, opacity: 0, duration: 0.6 }, '-=0.45')
            .from('.hero .cta .btn',       { y: 18, opacity: 0, duration: 0.5, stagger: 0.1 }, '-=0.3')
            .from('.hero .stat',           { y: 18, opacity: 0, duration: 0.5, stagger: 0.12 }, '-=0.25')
            .from('.hero .scroll-hint',    { opacity: 0, duration: 0.8 }, '-=0.1');

        /* Stat counters */
        document.querySelectorAll('[data-count]').forEach(function (el) {
            var target = parseFloat(el.dataset.count);
            var dec    = parseInt(el.dataset.decimals || '0', 10);
            var suffix = el.dataset.suffix || '';
            var state  = { v: 0 };
            gsap.to(state, {
                v: target, duration: 1.8, delay: 1.0, ease: 'power2.out',
                onUpdate: function () { el.textContent = state.v.toFixed(dec) + suffix; }
            });
        });

        if (typeof ScrollTrigger === 'undefined') {
            revealEls.forEach(function (el) { el.style.opacity = 1; });
            return;
        }

        /* Section headings / blocks */
        revealEls.forEach(function (el) {
            gsap.from(el, {
                y: 44, opacity: 0, duration: 0.85, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 82%' }
            });
        });

        /* Feature cards — staggered rise */
        gsap.from('.section-cards .card', {
            y: 60, opacity: 0, duration: 0.8, stagger: 0.14, ease: 'power3.out',
            scrollTrigger: { trigger: '.section-cards', start: 'top 80%' }
        });

        /* Pricing tiers — swell rolls in left to right */
        gsap.from('.prices-cards .card', {
            y: 70, opacity: 0, rotateX: 18, duration: 0.7, stagger: 0.1,
            ease: 'back.out(1.4)', transformOrigin: '50% 100%',
            scrollTrigger: { trigger: '.prices-cards', start: 'top 82%' }
        });
    }

    function boot() {
        initOcean();
        initMotion();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
