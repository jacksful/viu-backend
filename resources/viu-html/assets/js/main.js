/* ==========================================================================
   VIU — site behavior (classic script, no ES modules so it also runs from
   file://). In the WordPress theme this is enqueued via functions.php.
   Sections: nav · faq · reveal · count-up · inputs · parallax · contact ·
   modal (ZIP availability funnel).
   ========================================================================== */
(function () {
  'use strict';

  var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $  = function (sel, ctx) { return (ctx || document).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

  /* ---------- Navbar ---------------------------------------------------- */
  function initNav() {
    var header = $('[data-viu-header]');
    if (!header) return;
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 50); };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    var toggle = $('[data-viu-nav-toggle]', header);
    var setOpen = function (open) {
      header.classList.toggle('is-menu-open', open);
      if (toggle) toggle.setAttribute('aria-expanded', String(open));
    };
    if (toggle) toggle.addEventListener('click', function () {
      setOpen(!header.classList.contains('is-menu-open'));
    });
    $$('[data-viu-nav-close]', header).forEach(function (link) {
      link.addEventListener('click', function () { setOpen(false); });
    });
  }

  /* ---------- FAQ accordion --------------------------------------------- */
  function initFaq() {
    $$('[data-viu-faq]').forEach(function (group) {
      var items = $$('.viu-faq__item', group);
      items.forEach(function (item) {
        var trigger = $('.viu-faq__trigger', item);
        if (!trigger) return;
        trigger.addEventListener('click', function () {
          var isOpen = item.classList.contains('is-open');
          items.forEach(function (other) {
            var open = other === item && !isOpen;
            other.classList.toggle('is-open', open);
            var t = $('.viu-faq__trigger', other);
            if (t) t.setAttribute('aria-expanded', String(open));
          });
        });
      });
    });
  }

  /* ---------- Scroll reveal --------------------------------------------- */
  function initReveal() {
    var targets = $$('.viu-reveal, [data-viu-progress]');
    if (!targets.length) return;
    if (!('IntersectionObserver' in window)) {
      targets.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08 });
    targets.forEach(function (el) { observer.observe(el); });
  }

  /* ---------- Count-up -------------------------------------------------- */
  function initCountUp() {
    var els = $$('[data-viu-count]');
    if (!els.length) return;
    var animate = function (el) {
      var target = el.getAttribute('data-viu-count');
      if (prefersReduced || target.indexOf('/') !== -1) { el.textContent = target; return; }
      var isPct = target.indexOf('%') !== -1;
      var value = parseInt(target.replace(/[^0-9]/g, ''), 10);
      var suffix = isPct ? '%' : '';
      var start = performance.now();
      var step = function (now) {
        var p = Math.min((now - start) / 1800, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(eased * value) + suffix;
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };
    if (!('IntersectionObserver' in window)) { els.forEach(animate); return; }
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { animate(e.target); observer.unobserve(e.target); }
      });
    }, { threshold: 0.5 });
    els.forEach(function (el) { observer.observe(el); });
  }

  /* ---------- Numeric ZIP inputs + hero parallax ------------------------ */
  function initInputs() {
    $$('[data-viu-zip-input]').forEach(function (input) {
      input.addEventListener('input', function () {
        input.value = input.value.replace(/\D/g, '').slice(0, 5);
      });
    });
    var img = $('[data-viu-parallax]');
    if (img && !prefersReduced) {
      var ticking = false;
      var update = function () { img.style.transform = 'translateY(' + (window.scrollY * 0.3) + 'px) scale(1.1)'; ticking = false; };
      window.addEventListener('scroll', function () {
        if (!ticking) { requestAnimationFrame(update); ticking = true; }
      }, { passive: true });
    }
  }

  /* ---------- Button loading helper (DOM-only, no innerHTML) ------------ */
  function withLoading(btn, run) {
    if (!btn) return Promise.resolve().then(run);
    var saved = Array.prototype.slice.call(btn.childNodes);
    var spinner = document.createElement('span');
    spinner.className = 'viu-spinner';
    spinner.setAttribute('aria-hidden', 'true');
    btn.disabled = true;
    while (btn.firstChild) btn.removeChild(btn.firstChild);
    btn.appendChild(spinner);
    var restore = function () {
      btn.disabled = false;
      while (btn.firstChild) btn.removeChild(btn.firstChild);
      saved.forEach(function (n) { btn.appendChild(n); });
    };
    return Promise.resolve().then(run).then(
      function (v) { restore(); return v; },
      function (e) { restore(); throw e; }
    );
  }

  /* ---------- Shared modal accessibility (focus trap + inert bg) -------- */
  var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
  var modalLastFocused = null;
  function bgEls() {
    return [$('.site-header'), $('.site-main'), $('.site-footer')].filter(Boolean);
  }
  function modalEnter() {
    modalLastFocused = document.activeElement;
    bgEls().forEach(function (el) { el.setAttribute('inert', ''); });
  }
  function modalExit() {
    bgEls().forEach(function (el) { el.removeAttribute('inert'); });
    if (modalLastFocused && modalLastFocused.focus) modalLastFocused.focus();
    modalLastFocused = null;
  }
  function trapFocus(modal) {
    modal.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab') return;
      var f = $$(FOCUSABLE, modal).filter(function (el) { return el.offsetParent !== null; });
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
  }

  /* ---------- Contact modal --------------------------------------------- */
  function initContact() {
    var modal = $('[data-viu-contact-modal]');
    if (!modal) return;
    var form    = $('[data-viu-contact-form]', modal);
    var errorEl = $('[data-viu-contact-error]', modal);
    var button  = form ? $('button[type="submit"]', form) : null;
    var setStep = function (step) { modal.setAttribute('data-step', step); };
    var showErr = function (msg) { if (errorEl) { errorEl.textContent = msg || ''; errorEl.hidden = !msg; } };

    var resetTimer = null;
    var open = function () {
      if (resetTimer) { clearTimeout(resetTimer); resetTimer = null; }
      modalEnter();
      modal.hidden = false;
      document.body.classList.add('viu-modal-open');
      setStep('form');
      showErr('');
      var first = form ? $('input', form) : null;
      if (first) setTimeout(function () { first.focus(); }, 50);
    };
    var close = function () {
      modal.hidden = true;
      document.body.classList.remove('viu-modal-open');
      modalExit();
      if (resetTimer) clearTimeout(resetTimer);
      resetTimer = setTimeout(function () {
        setStep('form');
        if (form) form.reset();
        showErr('');
        resetTimer = null;
      }, 250);
    };

    trapFocus(modal);
    $$('[data-viu-contact-open]').forEach(function (btn) {
      btn.addEventListener('click', open);
    });
    $$('[data-viu-modal-close]', modal).forEach(function (el) { el.addEventListener('click', close); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) close();
    });

    if (form) form.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = {};
      new FormData(form).forEach(function (v, k) { data[k] = v; });
      if (!data.name || !data.email) { showErr('Name and email are required.'); return; }
      showErr('');
      var endpoint = form.getAttribute('data-endpoint');
      withLoading(button, function () {
        if (!endpoint) return;
        return fetch(endpoint, {
          method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
        }).then(function (r) { return r.json().then(function (d) { if (!r.ok) throw new Error(d.error || 'Error'); }); });
      }).then(function () { setStep('success'); })
        .catch(function (err) { showErr(err.message || 'Network error. Please try again.'); });
    });
  }

  /* ---------- Modal: ZIP availability funnel ---------------------------- */
  function initModal() {
    var modal = $('[data-viu-modal]');
    if (!modal) return;

    var zipInput  = $('[data-viu-modal-zip]', modal);
    var checkBtn  = $('[data-viu-modal-check]', modal);
    var errorEl   = $('[data-viu-modal-error]', modal);
    var leadForm  = $('[data-viu-modal-lead]', modal);
    var leadError = $('[data-viu-modal-lead-error]', modal);
    var leadBtn   = leadForm ? $('button[type="submit"]', leadForm) : null;

    var setStep = function (step) { modal.setAttribute('data-step', step); };
    var setZipOut = function (zip) {
      $$('[data-viu-modal-zipout]', modal).forEach(function (el) { el.textContent = zip; });
    };
    var showErr = function (el, msg) { if (el) { el.textContent = msg || ''; el.hidden = !msg; } };

    function check() {
      var zip = (zipInput && zipInput.value || '').trim();
      if (!/^\d{5}$/.test(zip)) { showErr(errorEl, 'Please enter a valid 5-digit ZIP code.'); return; }
      showErr(errorEl, '');
      var endpoint = modal.getAttribute('data-zip-endpoint');
      withLoading(checkBtn, function () {
        if (!endpoint) return { available: true };
        return fetch(endpoint, {
          method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ zipCode: zip })
        }).then(function (r) { return r.json().then(function (d) { if (!r.ok) throw new Error(d.error || 'Error'); return d; }); });
      }).then(function (data) {
        setZipOut(zip);
        setStep(data && data.available ? 'available' : 'unavailable');
      }).catch(function (err) { showErr(errorEl, err.message || 'Network error. Please try again.'); });
    }

    var resetTimer = null;
    var open = function (prefill) {
      if (resetTimer) { clearTimeout(resetTimer); resetTimer = null; }
      modalEnter();
      modal.hidden = false;
      document.body.classList.add('viu-modal-open');
      setStep('zip-search');
      showErr(errorEl, ''); showErr(leadError, '');
      if (zipInput && typeof prefill === 'string') zipInput.value = prefill.replace(/\D/g, '').slice(0, 5);
      if (zipInput) setTimeout(function () { zipInput.focus(); }, 50);
      if (zipInput && /^\d{5}$/.test(zipInput.value)) check();
    };
    var close = function () {
      modal.hidden = true;
      document.body.classList.remove('viu-modal-open');
      modalExit();
      if (resetTimer) clearTimeout(resetTimer);
      resetTimer = setTimeout(function () {
        setStep('zip-search');
        if (zipInput) zipInput.value = '';
        if (leadForm) leadForm.reset();
        showErr(errorEl, ''); showErr(leadError, '');
        resetTimer = null;
      }, 250);
    };

    trapFocus(modal);
    // Open triggers (nav, pricing, footer)
    $$('[data-viu-modal-open]').forEach(function (btn) {
      btn.addEventListener('click', function () { open(); });
    });
    // Hero form → open modal with its ZIP (auto-checks when 5 digits)
    var heroForm = $('[data-viu-hero-form]');
    if (heroForm) heroForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var hz = $('[data-viu-hero-zip]', heroForm);
      open(hz ? hz.value.trim() : '');
    });

    if (checkBtn) checkBtn.addEventListener('click', check);
    if (zipInput) zipInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); check(); } });

    // Lead form
    if (leadForm) leadForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = {};
      new FormData(leadForm).forEach(function (v, k) { data[k] = v; });
      if (!data.name || !data.email) { showErr(leadError, 'Name and email are required.'); return; }
      showErr(leadError, '');
      var endpoint = leadForm.getAttribute('data-endpoint');
      withLoading(leadBtn, function () {
        if (!endpoint) return;
        return fetch(endpoint, {
          method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
        }).then(function (r) { return r.json().then(function (d) { if (!r.ok) throw new Error(d.error || 'Error'); }); });
      }).then(function () { setStep('success'); })
        .catch(function (err) { showErr(leadError, err.message || 'Network error. Please try again.'); });
    });

    // Close + retry
    $$('[data-viu-modal-close]', modal).forEach(function (el) { el.addEventListener('click', close); });
    var retry = $('[data-viu-modal-retry]', modal);
    if (retry) retry.addEventListener('click', function () {
      setStep('zip-search');
      if (zipInput) { zipInput.value = ''; zipInput.focus(); }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) close();
    });
  }

  /* ---------- Boot ------------------------------------------------------ */
  function boot() {
    initNav();
    initFaq();
    initReveal();
    initCountUp();
    initInputs();
    initContact();
    initModal();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
