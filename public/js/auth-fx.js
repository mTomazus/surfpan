/* SurfPan auth pages — Night Swell backdrop + entrance choreography.
   Self-contained; degrades to static page without WebGL/GSAP/motion. */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isMobile     = window.matchMedia('(max-width: 768px)').matches;

    /* ---------- calm ocean backdrop ---------- */
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
        scene.fog = new THREE.FogExp2(0x000208, 0.0024);

        var camera = new THREE.PerspectiveCamera(55, 1, 1, 2000);
        camera.position.set(0, 70, 340);

        var segs = isMobile ? 40 : 84;
        var geo  = new THREE.PlaneGeometry(1700, 1700, segs, segs);
        geo.rotateX(-Math.PI / 2);
        var pos  = geo.attributes.position;
        var base = Float32Array.from(pos.array);

        scene.add(new THREE.Mesh(geo, new THREE.MeshBasicMaterial({
            color: 0x17e1ef, wireframe: true, transparent: true, opacity: 0.16
        })));
        scene.add(new THREE.Points(geo, new THREE.PointsMaterial({
            color: 0xbdfbff, size: isMobile ? 1.4 : 1.9,
            transparent: true, opacity: 0.35, sizeAttenuation: true
        })));

        var t = 0, raf = null;

        function resize() {
            var w = stage.clientWidth, h = stage.clientHeight;
            renderer.setSize(w, h, false);
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
        }
        resize();
        window.addEventListener('resize', resize);

        function frame() {
            t += 0.0045; // calmer than the landing swell
            for (var i = 0; i < pos.count; i++) {
                var x = base[i * 3], z = base[i * 3 + 2];
                pos.array[i * 3 + 1] =
                    Math.sin(x * 0.011 + t * 2.0) * 7 +
                    Math.sin(z * 0.015 - t * 2.4) * 10 +
                    Math.sin((x + z) * 0.008 + t * 1.3) * 5;
            }
            pos.needsUpdate = true;
            camera.lookAt(0, 14, 0);
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

    /* ---------- entrance ---------- */
    function visibleFields(scope) {
        // Only stagger fields the user can actually see (hidden form-wraps excluded).
        return Array.prototype.filter.call(
            (scope || document).querySelectorAll('.fld, .agree, .remember, .auth-actions, .form-head, .foot'),
            function (el) { return el.offsetParent !== null; }
        );
    }

    function initEntrance() {
        if (reduceMotion || typeof gsap === 'undefined') return;

        var card = document.querySelector('.fx-card');
        if (card) {
            gsap.from(card, { y: 28, opacity: 0, scale: 0.985, duration: 0.7, ease: 'power3.out' });
        }
        gsap.from(visibleFields(card), {
            y: 16, opacity: 0, duration: 0.5, stagger: 0.06, delay: 0.25, ease: 'power2.out'
        });

        var promo = document.querySelector('.promo-inner');
        if (promo) {
            gsap.from(promo.children, {
                y: 18, opacity: 0, duration: 0.6, stagger: 0.1, delay: 0.35, ease: 'power2.out'
            });
            var logo = promo.querySelector('img');
            if (logo) {
                gsap.to(logo, { y: -8, duration: 2.6, yoyo: true, repeat: -1, ease: 'sine.inOut' });
            }
        }
    }

    /* ---------- login/signup switch (used by login.php) ---------- */
    window.authSwitch = function (name) {
        var wraps = document.querySelectorAll('.form-wrap');
        var show  = document.getElementById(name);
        if (!show) return;

        if (reduceMotion || typeof gsap === 'undefined') {
            wraps.forEach(function (w) { w.style.display = (w.id === name) ? 'block' : 'none'; });
            return;
        }
        var current = Array.prototype.find.call(wraps, function (w) {
            return w.style.display !== 'none' && w.id !== name;
        });
        var reveal = function () {
            wraps.forEach(function (w) { w.style.display = (w.id === name) ? 'block' : 'none'; });
            gsap.fromTo(show, { opacity: 0, x: 14 }, { opacity: 1, x: 0, duration: 0.35, ease: 'power2.out' });
            gsap.from(visibleFields(show), { y: 12, opacity: 0, duration: 0.35, stagger: 0.045, ease: 'power2.out' });
        };
        if (current) {
            gsap.to(current, { opacity: 0, x: -14, duration: 0.22, ease: 'power2.in', onComplete: function () {
                gsap.set(current, { clearProps: 'opacity,transform' });
                reveal();
            }});
        } else {
            reveal();
        }
    };

    function boot() {
        initOcean();
        initEntrance();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
