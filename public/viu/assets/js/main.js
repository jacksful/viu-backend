/* ==========================================================================
   VIU — site behavior (Laravel-adapted from viu-html)
   ========================================================================== */
(function () {
  'use strict';

  var config = window.VIU_CONFIG || {};
  var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $  = function (sel, ctx) { return (ctx || document).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

  function csrfHeaders() {
    var headers = {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    };
    if (config.csrfToken) {
      headers['X-CSRF-TOKEN'] = config.csrfToken;
    }
    return headers;
  }

  function parseJsonResponse(r) {
    return r.json().then(function (d) {
      if (!r.ok) {
        var msg = d.message || d.error || 'Request failed.';
        if (d.errors) {
          var first = Object.keys(d.errors)[0];
          if (first && d.errors[first] && d.errors[first][0]) {
            msg = d.errors[first][0];
          }
        }
        throw new Error(msg);
      }
      return d;
    });
  }

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

  /* ---------- Button loading helper ------------------------------------- */
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

  /* ---------- Shared modal accessibility -------------------------------- */
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

  function updateModalPrice(modal, zipcode, billingInterval) {
    if (!zipcode) return;
    var priceEl = $('[data-viu-modal-price]', modal);
    var suffixEl = $('[data-viu-modal-price-suffix]', modal);
    var plans = zipcode.billing_plans || [];
    var selected = plans.find(function (plan) { return plan.interval === billingInterval; }) || plans[0];
    if (priceEl && selected) {
      priceEl.textContent = '$' + Math.round(parseFloat(selected.amount)).toLocaleString();
    }
    if (suffixEl && selected) {
      suffixEl.textContent = selected.suffix || '/mo';
    }
  }

  function renderBillingPlans(modal, zipcode, onSelect) {
    var container = $('[data-viu-modal-plans]', modal);
    if (!container) return zipcode && zipcode.default_billing_interval ? zipcode.default_billing_interval : 'month';
    container.innerHTML = '';
    var plans = (zipcode && zipcode.billing_plans) || [];
    if (plans.length <= 1) {
      container.hidden = true;
      var interval = plans[0] ? plans[0].interval : 'month';
      updateModalPrice(modal, zipcode, interval);
      return interval;
    }
    container.hidden = false;
    var selected = zipcode.selected_billing_interval || zipcode.default_billing_interval || plans[0].interval;
    plans.forEach(function (plan) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'viu-modal__plan' + (plan.interval === selected ? ' is-active' : '');
      btn.setAttribute('data-viu-billing-interval', plan.interval);
      btn.innerHTML = '<span class="viu-modal__plan-label">' + plan.label + '</span><span class="viu-modal__plan-amount">$' + Math.round(parseFloat(plan.amount)).toLocaleString() + (plan.suffix || '') + '</span>';
      btn.addEventListener('click', function () {
        zipcode.selected_billing_interval = plan.interval;
        $$('.viu-modal__plan', container).forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        updateModalPrice(modal, zipcode, plan.interval);
        if (onSelect) onSelect(plan.interval);
      });
      container.appendChild(btn);
    });
    updateModalPrice(modal, zipcode, selected);
    return selected;
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
      var fd = new FormData(form);
      var data = {};
      fd.forEach(function (v, k) { data[k] = v; });
      if (!data.name || !data.email) { showErr('Name and email are required.'); return; }
      showErr('');
      var endpoint = config.contactStoreUrl || form.getAttribute('data-endpoint');
      withLoading(button, function () {
        if (!endpoint) return Promise.resolve();
        var body = new FormData();
        body.append('_token', config.csrfToken || '');
        body.append('name', data.name || '');
        body.append('email', data.email || '');
        body.append('phone', data.phone || '');
        body.append('zip_of_interest', data.zipCode || data.zip_of_interest || '');
        body.append('message', data.message || '');
        return fetch(endpoint, {
          method: 'POST',
          headers: csrfHeaders(),
          body: body,
        }).then(parseJsonResponse);
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
    var contactForm  = $('[data-viu-modal-contact]', modal);
    var contactError = $('[data-viu-modal-contact-error]', modal);
    var contactBtn   = contactForm ? $('button[type="submit"]', contactForm) : null;
    var contactMsgEl = $('[data-viu-modal-contact-message]', modal);
    var unavailableMsgEl = $('[data-viu-modal-unavailable-message]', modal);
    var contactZipInput = contactForm ? $('input[name="zipCode"]', contactForm) : null;
    var currentZipcode = null;
    var currentZip = '';
    var selectedBillingInterval = 'month';

    var setStep = function (step) { modal.setAttribute('data-step', step); };
    var setZipOut = function (zip) {
      $$('[data-viu-modal-zipout]', modal).forEach(function (el) { el.textContent = zip; });
    };
    var showErr = function (el, msg) { if (el) { el.textContent = msg || ''; el.hidden = !msg; } };

    function check() {
      var zip = (zipInput && zipInput.value || '').trim();
      if (!/^\d{5}$/.test(zip)) { showErr(errorEl, 'Please enter a valid 5-digit ZIP code.'); return; }
      showErr(errorEl, '');
      var endpoint = config.zipCheckUrl || modal.getAttribute('data-zip-endpoint');
      withLoading(checkBtn, function () {
        if (!endpoint) return Promise.resolve({ available: true });
        return fetch(endpoint, {
          method: 'POST',
          headers: Object.assign({ 'Content-Type': 'application/json' }, csrfHeaders()),
          body: JSON.stringify({ zipcode: zip }),
        }).then(parseJsonResponse);
      }).then(function (data) {
        currentZip = zip;
        setZipOut(zip);
        if (data && data.available && data.zipcode) {
          currentZipcode = data.zipcode;
          selectedBillingInterval = renderBillingPlans(modal, currentZipcode, function (interval) {
            selectedBillingInterval = interval;
          });
          setStep('available');
        } else if (data && data.is_in_coverage_area) {
          currentZipcode = null;
          if (contactMsgEl) {
            contactMsgEl.textContent = data.message || ('ZIP code ' + zip + ' is currently unavailable. Contact us and we will follow up.');
          }
          if (contactZipInput) contactZipInput.value = zip;
          if (contactForm) contactForm.reset();
          if (contactZipInput) contactZipInput.value = zip;
          showErr(contactError, '');
          setStep('contact');
        } else {
          currentZipcode = null;
          if (unavailableMsgEl) {
            unavailableMsgEl.textContent = (data && data.message)
              ? data.message
              : 'ZIP code ' + zip + ' is not in our coverage area. Try a different ZIP code.';
          }
          setStep('unavailable');
        }
      }).catch(function (err) { showErr(errorEl, err.message || 'Network error. Please try again.'); });
    }

    var resetTimer = null;
    var open = function (prefill) {
      if (resetTimer) { clearTimeout(resetTimer); resetTimer = null; }
      currentZipcode = null;
      currentZip = '';
      selectedBillingInterval = 'month';
      modalEnter();
      modal.hidden = false;
      document.body.classList.add('viu-modal-open');
      setStep('zip-search');
      showErr(errorEl, ''); showErr(leadError, ''); showErr(contactError, '');
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
        currentZipcode = null;
        currentZip = '';
        selectedBillingInterval = 'month';
        if (zipInput) zipInput.value = '';
        if (leadForm) leadForm.reset();
        if (contactForm) contactForm.reset();
        showErr(errorEl, ''); showErr(leadError, ''); showErr(contactError, '');
        resetTimer = null;
      }, 250);
    };

    trapFocus(modal);
    $$('[data-viu-modal-open]').forEach(function (btn) {
      btn.addEventListener('click', function () { open(); });
    });
    var heroForm = $('[data-viu-hero-form]');
    if (heroForm) heroForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var hz = $('[data-viu-hero-zip]', heroForm);
      open(hz ? hz.value.trim() : '');
    });

    if (checkBtn) checkBtn.addEventListener('click', check);
    if (zipInput) zipInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); check(); } });

    if (leadForm) leadForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(leadForm);
      var data = {};
      fd.forEach(function (v, k) { data[k] = v; });
      if (!data.name || !data.email) { showErr(leadError, 'Name and email are required.'); return; }
      if (!currentZipcode || !currentZipcode.id) { showErr(leadError, 'Please check ZIP availability first.'); return; }
      showErr(leadError, '');
      var checkoutEndpoint = config.stripeCheckoutUrl || config.leadStoreUrl || leadForm.getAttribute('data-endpoint');
      withLoading(leadBtn, function () {
        if (!checkoutEndpoint) return Promise.resolve();
        if (config.stripeCheckoutUrl) {
          return fetch(checkoutEndpoint, {
            method: 'POST',
            headers: Object.assign({ 'Content-Type': 'application/json', 'Accept': 'application/json' }, csrfHeaders()),
            body: JSON.stringify({
              name: data.name || '',
              email: data.email || '',
              phone: data.phone || '',
              company: data.company || '',
              zipcode_id: currentZipcode.id,
              billing_interval: selectedBillingInterval,
            }),
          }).then(parseJsonResponse).then(function (response) {
            if (response && response.checkout_url) {
              window.location.href = response.checkout_url;
              return response;
            }
            throw new Error((response && response.message) || 'Unable to start checkout.');
          });
        }
        var body = new FormData();
        body.append('_token', config.csrfToken || '');
        body.append('name', data.name || '');
        body.append('email', data.email || '');
        body.append('phone', data.phone || '');
        body.append('initial_notes', data.company || '');
        body.append('zipcodes[]', String(currentZipcode.id));
        return fetch(checkoutEndpoint, {
          method: 'POST',
          headers: csrfHeaders(),
          body: body,
        }).then(parseJsonResponse);
      }).then(function (response) {
        if (response && response.checkout_url) return;
        setStep('success');
      }).catch(function (err) { showErr(leadError, err.message || 'Network error. Please try again.'); });
    });

    if (contactForm) contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(contactForm);
      var data = {};
      fd.forEach(function (v, k) { data[k] = v; });
      if (!data.name || !data.email) { showErr(contactError, 'Name and email are required.'); return; }
      showErr(contactError, '');
      var endpoint = config.contactStoreUrl || contactForm.getAttribute('data-endpoint');
      withLoading(contactBtn, function () {
        if (!endpoint) return Promise.resolve();
        var body = new FormData();
        body.append('_token', config.csrfToken || '');
        body.append('name', data.name || '');
        body.append('email', data.email || '');
        body.append('phone', data.phone || '');
        body.append('zip_of_interest', data.zipCode || currentZip || '');
        body.append('subject', 'ZIP territory inquiry');
        body.append('message', data.message || ('Interested in ZIP ' + (data.zipCode || currentZip || '') + ' which is currently unavailable.'));
        return fetch(endpoint, {
          method: 'POST',
          headers: csrfHeaders(),
          body: body,
        }).then(parseJsonResponse);
      }).then(function () { setStep('contact-success'); })
        .catch(function (err) { showErr(contactError, err.message || 'Network error. Please try again.'); });
    });

    $$('[data-viu-modal-close]', modal).forEach(function (el) { el.addEventListener('click', close); });
    var retryToZipSearch = function () {
      setStep('zip-search');
      currentZipcode = null;
      currentZip = '';
      selectedBillingInterval = 'month';
      showErr(contactError, '');
      if (zipInput) { zipInput.value = ''; zipInput.focus(); }
    };
    var retry = $('[data-viu-modal-retry]', modal);
    if (retry) retry.addEventListener('click', retryToZipSearch);
    var retryContact = $('[data-viu-modal-retry-contact]', modal);
    if (retryContact) retryContact.addEventListener('click', retryToZipSearch);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) close();
    });
  }

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
