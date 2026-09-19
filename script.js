/* =====================================================================
   SmartShelf — global client-side script
   Handles: theme toggle, mobile nav, user dropdown, scroll effects,
   scroll-reveal animations, form validation, live catalog search,
   QR modal, animated counters and the password strength meter.
   No external libraries — vanilla JS only.
   ===================================================================== */
(function () {
    'use strict';

    /* ---------------- Theme (dark / light) toggle ---------------- */
    const themeToggle = document.getElementById('themeToggle');
    const root = document.documentElement;

    function syncThemeIcon() {
        if (!themeToggle) return;
        const isDark = root.getAttribute('data-theme') === 'dark';
        themeToggle.querySelector('i').className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
    syncThemeIcon();

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            syncThemeIcon();
        });
    }

    /* ---------------- Mobile navigation toggle ------------------- */
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');
            const open = navLinks.classList.contains('open');
            navToggle.querySelector('i').className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        });
    }

    /* ---------------- User dropdown menu ------------------------- */
    const userChip = document.getElementById('userChip');
    const userMenu = document.getElementById('userMenu');
    if (userChip && userMenu) {
        userChip.addEventListener('click', function (e) {
            e.stopPropagation();
            userMenu.classList.toggle('open');
        });
        document.addEventListener('click', function () {
            userMenu.classList.remove('open');
        });
    }

    /* ---------------- Dashboard sidebar toggle ------------------- */
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    /* ---------------- Navbar shadow + scroll-to-top -------------- */
    const navbar = document.getElementById('navbar');
    const toTop = document.getElementById('toTop');
    window.addEventListener('scroll', function () {
        if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 8);
        if (toTop) toTop.classList.toggle('show', window.scrollY > 400);
    });
    if (toTop) {
        toTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---------------- Scroll-reveal animations ------------------- */
    const revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealEls.length) {
        const obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry, i) {
                if (entry.isIntersecting) {
                    setTimeout(() => entry.target.classList.add('visible'), i * 60);
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach(el => obs.observe(el));
    } else {
        revealEls.forEach(el => el.classList.add('visible'));
    }

    /* ---------------- Animated number counters ------------------ */
    const counters = document.querySelectorAll('[data-count]');
    if ('IntersectionObserver' in window && counters.length) {
        const cObs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const target = parseFloat(el.getAttribute('data-count'));
                const suffix = el.getAttribute('data-suffix') || '';
                let cur = 0;
                const steps = 45;
                const inc = target / steps;
                const timer = setInterval(function () {
                    cur += inc;
                    if (cur >= target) { cur = target; clearInterval(timer); }
                    el.textContent = (Number.isInteger(target) ? Math.floor(cur) : cur.toFixed(1)) + suffix;
                }, 22);
                cObs.unobserve(el);
            });
        }, { threshold: 0.5 });
        counters.forEach(el => cObs.observe(el));
    }

    /* ---------------- Live catalog search ------------------------ */
    const liveSearch = document.getElementById('liveSearch');
    if (liveSearch) {
        const cards = document.querySelectorAll('[data-search]');
        const noResults = document.getElementById('noResults');
        liveSearch.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let visible = 0;
            cards.forEach(function (card) {
                const hay = card.getAttribute('data-search').toLowerCase();
                const show = hay.indexOf(q) !== -1;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
        });
    }

    /* ---------------- Category filter chips ---------------------- */
    const chips = document.querySelectorAll('[data-filter]');
    if (chips.length) {
        const cards = document.querySelectorAll('[data-category]');
        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                chips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                const cat = chip.getAttribute('data-filter');
                cards.forEach(function (card) {
                    const show = cat === 'all' || card.getAttribute('data-category') === cat;
                    card.style.display = show ? '' : 'none';
                });
            });
        });
    }

    /* ---------------- QR code modal ------------------------------ */
    // Uses a free QR image API to render the code on demand.
    window.showQR = function (text, title) {
        let overlay = document.getElementById('qrModal');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            overlay.id = 'qrModal';
            overlay.innerHTML =
                '<div class="modal">' +
                '<button class="modal-close" onclick="document.getElementById(\'qrModal\').classList.remove(\'open\')">' +
                '<i class="fa-solid fa-xmark"></i></button>' +
                '<h3 id="qrTitle" style="margin-bottom:6px"></h3>' +
                '<p class="text-mute" style="margin-bottom:18px;font-size:.88rem">Scan at the counter to issue or return</p>' +
                '<img id="qrImg" alt="QR code" style="margin:0 auto;border-radius:14px;border:1px solid var(--border)">' +
                '<p id="qrCode" style="margin-top:14px;font-weight:600;letter-spacing:.05em"></p>' +
                '</div>';
            document.body.appendChild(overlay);
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) overlay.classList.remove('open');
            });
        }
        document.getElementById('qrTitle').textContent = title || 'Book QR Code';
        document.getElementById('qrCode').textContent = text;
        document.getElementById('qrImg').src =
            'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=10&data=' + encodeURIComponent(text);
        overlay.classList.add('open');
    };

    /* ---------------- Confirm dialogs ---------------------------- */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!window.confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    /* ---------------- Password strength meter -------------------- */
    const pwInput = document.getElementById('password');
    const pwMeter = document.getElementById('pwMeterBar');
    if (pwInput && pwMeter) {
        pwInput.addEventListener('input', function () {
            const v = pwInput.value;
            let score = 0;
            if (v.length >= 8) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const pct = [0, 25, 50, 75, 100][score];
            const colors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'];
            pwMeter.style.width = pct + '%';
            pwMeter.style.background = colors[score] || '#ef4444';
        });
    }

    /* ---------------- Generic form validation -------------------- */
    // Any <form data-validate> gets client-side validation before submit.
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;

            form.querySelectorAll('[required]').forEach(function (field) {
                const errEl = field.parentElement.querySelector('.field-error')
                    || field.closest('.form-group')?.querySelector('.field-error');
                let msg = '';

                if (!field.value.trim()) {
                    msg = 'This field is required.';
                } else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                    msg = 'Please enter a valid email address.';
                } else if (field.dataset.minlength && field.value.length < +field.dataset.minlength) {
                    msg = 'Must be at least ' + field.dataset.minlength + ' characters.';
                } else if (field.dataset.match) {
                    const other = document.getElementById(field.dataset.match);
                    if (other && field.value !== other.value) msg = 'Values do not match.';
                } else if (field.type === 'tel' && field.value && !/^[0-9+\-\s()]{7,15}$/.test(field.value)) {
                    msg = 'Please enter a valid phone number.';
                }

                if (msg) {
                    valid = false;
                    field.classList.add('invalid');
                    if (errEl) { errEl.textContent = msg; errEl.classList.add('show'); }
                } else {
                    field.classList.remove('invalid');
                    if (errEl) errEl.classList.remove('show');
                }
            });

            if (!valid) {
                e.preventDefault();
                const firstErr = form.querySelector('.invalid');
                if (firstErr) firstErr.focus();
            }
        });

        // Clear error styling as the user fixes a field.
        form.querySelectorAll('[required]').forEach(function (field) {
            field.addEventListener('input', function () {
                field.classList.remove('invalid');
                const errEl = field.closest('.form-group')?.querySelector('.field-error');
                if (errEl) errEl.classList.remove('show');
            });
        });
    });

    /* ---------------- Auto-dismiss flash alerts ------------------ */
    document.querySelectorAll('.alert[data-auto]').forEach(function (a) {
        setTimeout(function () {
            a.style.transition = 'opacity .4s, transform .4s';
            a.style.opacity = '0';
            a.style.transform = 'translateY(-8px)';
            setTimeout(() => a.remove(), 400);
        }, 4500);
    });

})();
