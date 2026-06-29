# VIU — Static HTML/CSS (WordPress-theme source)

A faithful, framework-free replica of the live VIU site
(<https://fullviu.com/>), built as the source markup for a **custom classic
WordPress theme**. Content mirrors the live site with three corrected bugs
(see *Fidelity notes* below).

## Preview locally

Opening `index.html` directly (double-click → `file://`) works and shows all
content — but browsers block ES-module scripts on `file://`, so the scroll
**animations and interactions are disabled** in that mode (content is shown
statically). For the full experience, serve over HTTP:

```bash
cd viu-html
python3 -m http.server 4321
# open http://localhost:4321
```

> Note: the page is built progressive-enhancement style — sections are visible
> by default and only opt into reveal animations when JS is available
> (`<html class="js">`). So a JS failure never hides content.

## Structure

```
viu-html/
├── index.html              # full page; sections fenced with <!-- template-part: … --> markers
├── DESIGN-SYSTEM.md        # tokens, components, theme.json mapping — read this first
├── assets/
│   ├── css/
│   │   ├── 01-tokens.css    # design system (all custom properties)
│   │   ├── 02-base.css      # reset, base typography, type-scale helpers
│   │   ├── 03-components.css # buttons, badges, icon-box, progress, cards, forms, faq, reveal
│   │   ├── 04-sections.css   # header, hero, stats, splits, pricing, faq, cta, footer
│   │   └── 05-utilities.css  # .container, .alignfull, .section, a11y, reduced-motion
│   │                          # (linked individually in <head> in cascade order; the Inter
│   │                          #  webfont is loaded via <link> in <head>, not @import)
│   ├── js/main.js            # single vanilla classic script (nav, faq, reveal, countup, forms, modal) — runs from file:// too
│   ├── images/               # hero-bg + section-1..4 + logos (copied from production)
│   └── icons/                # inline Lucide SVGs (source of truth for the inlined icons)
```

## Interactions (all vanilla JS, zero dependencies)

- Sticky navbar with scrolled state + animated mobile menu.
- Single-open FAQ accordion (grid-rows animation).
- Scroll-reveal + animated progress bars + stat count-up (IntersectionObserver).
- **Shared ZIP-availability modal** (matches the live site): a single modal,
  opened by **every** CTA — nav "Check Territory", pricing "Check ZIP
  Availability", footer "Claim Your ZIP Now", and the hero "Secure Territory".
  None of them jump to another section. The hero passes its typed ZIP into the
  modal and auto-runs the check. Modal funnel:
  `zip-search → available (pricing + lead form) → success`, plus an
  `unavailable` branch and an error state.
- Expandable contact form in the CTA band.
- Network calls go to `data-endpoint` (modal lead form) / `data-zip-endpoint`
  (modal element, for the availability check) / `data-endpoint` (contact form);
  when empty they return a **mock** response so the static page is fully
  demoable. Set these to your real API during integration.

## Converting to a classic WordPress theme

1. Create `wp-content/themes/viu/` with: `style.css` (theme header),
   `functions.php`, `index.php`, `header.php`, `footer.php`, `front-page.php`,
   and `template-parts/`.
2. Split `index.html` at the `<!-- template-part: … -->` markers:
   - `header` block → `header.php`
   - `footer` block → `footer.php`
   - each `<section>` → a file in `template-parts/` pulled in by `front-page.php`
     via `get_template_part()`.
3. Move `assets/` into the theme and **enqueue** in `functions.php`. Register the
   CSS layers in cascade order (or bundle them into one file at build time):
   ```php
   add_action('wp_enqueue_scripts', function () {
     foreach (['01-tokens','02-base','03-components','04-sections','05-utilities'] as $i => $name) {
       wp_enqueue_style("viu-$name", get_theme_file_uri("assets/css/$name.css"), [], '1.0');
     }
     wp_enqueue_script('viu-main', get_theme_file_uri('assets/js/main.js'), [], '1.0', true);
   });
   ```
   (`main.js` is a plain classic script — no `type="module"` needed. The Inter
   webfont is loaded with a `<link>` in `<head>`; add it via `wp_enqueue_style`
   or the `wp_head` hook with `preconnect`.)
4. Make editable content dynamic with **ACF** (or block bindings): hero copy,
   the predictive-signal/territory cards, pricing, and the FAQ repeater map
   cleanly to fields. Replace the static strings with `the_field()` calls.
5. Wire forms to a WP REST route (or a plugin) and set each form's
   `data-endpoint`. Keep CAPTCHA/anti-spam server-side.
6. Optimize `section-*.jpg` (they are 1–2 MB) — convert to WebP/AVIF and add
   `srcset` / `loading="lazy"` during integration.

See **DESIGN-SYSTEM.md** for the token reference and the `theme.json` path if you
later migrate to a block (FSE) theme.

## Fidelity notes (live site → this build)

Replicates the live site's content and layout, with three live-site bugs fixed:

- **"Be first scene" → "Be first seen."** (typo corrected).
- **FAQ #1** now has a real answer instead of the placeholder paragraph.
- **Email unified** to `support@fullviu.com` (the live site mixed
  `support@viu.com` in the FAQ with `support@fullviu.com` in the footer).
