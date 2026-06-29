# VIU Design System

The single source of truth for the VIU marketing site. Every visual decision is
a **design token** (CSS custom property) declared in
[`assets/css/01-tokens.css`](assets/css/01-tokens.css). Components and sections
**only consume tokens** — never hard-coded values. Change a token once and it
propagates everywhere, including a future WordPress block theme via `theme.json`.

---

## 1. Principles

1. **Tokens first.** No raw hex/px in component or section CSS. If a value is
   missing, add a token, don't inline it.
2. **Namespaced BEM.** Components are `.viu-block__element--modifier`. State is
   expressed with WordPress-style `.is-*` classes (`.is-open`, `.is-scrolled`,
   `.is-visible`).
3. **WordPress-friendly structure.** Structural wrappers use WP conventions
   (`.site-header`, `.site-main`, `.site-footer`, `.container`, `.alignfull`)
   so conversion to a classic theme is mechanical.
4. **Layer order is fixed:** tokens → base → components → sections → utilities
   (linked in that order in `index.html`'s `<head>`). This avoids specificity
   wars and the heading-override cascade bug present in the original React build.
5. **Sharp by default.** Corners are square; only icon circles, pills (badges,
   progress) and the small checklist chips are rounded.
6. **Motion is optional.** All animation respects `prefers-reduced-motion`.
7. **Sentence case everywhere.** No `text-transform: uppercase`. Capitalize the
   first word of a heading/label only; keep acronyms (`VIU`, `ZIP`) and proper
   nouns capitalized. Eyebrows/labels use a subtle `--viu-ls-label` (0.3px), not
   wide all-caps tracking.

---

## 2. Color tokens

| Token | Value | Usage |
| --- | --- | --- |
| `--viu-color-primary` | `#2a2d7c` | Brand indigo — headings, dark bands, marks |
| `--viu-color-primary-dark` | `#1a1c4f` | Footer band |
| `--viu-color-ink` | `#121212` | Navbar bg, primary-button label |
| `--viu-color-accent` | `#f57f20` | CTAs, highlights, accent headline fragments |
| `--viu-color-accent-hover` | `#e06d10` | Primary-button hover |
| `--viu-color-accent-soft` | `rgba(245,127,32,.10)` | Orange badge / icon-box backgrounds |
| `--viu-color-accent-warm` | `#f9eee5` | Warm icon-box background |
| `--viu-color-surface` | `#ffffff` | Default section background |
| `--viu-color-surface-alt` | `#f8fafc` | Territory band |
| `--viu-color-surface-alt-2` | `#f9fafb` | FAQ band, checklist tiles |
| `--viu-color-text` | `#6a7282` | Body copy on light |
| `--viu-color-text-strong` | `#4a5565` | Emphasised small caps |
| `--viu-color-text-muted` | `#868c96` | Body copy on dark |
| `--viu-color-text-light` | `#99a1af` | Captions |
| `--viu-color-on-primary` | `#ffffff` | Text/icons on dark bands |
| `--viu-color-border` | `#f3f4f6` | FAQ dividers |
| `--viu-color-border-input` | `#e5e7eb` | Form inputs |
| `--viu-color-track` | `#ebebeb` | Progress-bar track |
| `--viu-color-white-05/10/20` | white @ 5/10/20% | On-dark surfaces & borders |
| `--viu-color-success / -error` | green / red | Form result states |

---

## 3. Typography

Font: **Inter** (400–900), loaded via `<link>` in `<head>`. Inter is an intentional brand
match to the production VIU site.

| Token | Size | Line / tracking | Used by |
| --- | --- | --- | --- |
| `--viu-fs-display` | `clamp(2.25rem, 5vw, 4.5rem)` | 1.1 / −0.06em / 900 | Hero H1 (`.viu-display`) |
| `--viu-fs-h2` | `clamp(1.75rem, 3.5vw, 3rem)` | 1.17 / −0.03em / 700 | Section H2 (`.viu-h2`) |
| `--viu-fs-h3` | `1.125rem` | 1.3 / −0.45px / 700 | Feature & card titles (`.viu-h3`) |
| `--viu-fs-price` | `3rem` | 1 / −2px / 900 | Pricing `$199` |
| `--viu-fs-stat` | `2rem` | 1.25 / −1.5px / 900 | Stat numbers |
| `--viu-fs-lg` | `1.125rem` | 1.6 | Lead paragraphs (`.viu-lead`) |
| `--viu-fs-base` | `1rem` | 1.6 | Body |
| `--viu-fs-sm` | `0.875rem` | — | Small text, captions |
| `--viu-fs-xs` | `0.75rem` | ls 0.3px / 600 | Eyebrows & labels (`.viu-eyebrow`) |

Heading styles are applied via the helper classes `.viu-display` / `.viu-h2` /
`.viu-h3` (not bare element selectors), so a utility class can always override
them.

---

## 4. Spacing — 8px base scale

`--viu-space-1 … --viu-space-20` = 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 72, 80 px.

- Section rhythm: `--viu-section-py` `clamp(48px → 80px)`, `--viu-section-py-lg`
  `clamp(64px → 80px)` (applied via `.section` / `.section--lg`).
- Container: `--viu-container-max` `1440px`; responsive gutters
  20 → 40 → 64 → 80 px via `.container` at 640 / 768 / 1024 breakpoints.

## 5. Radius / shadow / motion

- Radius: `--viu-radius-none` `0` (default), `--viu-radius-chip` `4px`,
  `--viu-radius-md` `6px`, `--viu-radius-pill` `9999px`.
- Shadow: `--viu-shadow-card`, `--viu-shadow-btn`, `--viu-shadow-btn-hover`,
  `--viu-shadow-nav`.
- Motion: `--viu-ease` `cubic-bezier(.16,1,.3,1)`; durations
  `--viu-dur-fast/base/slow/reveal/reveal-lg/hero` = 200/300/400/700/900/1000 ms.

---

## 6. Components (quick reference)

| Component | Class | Modifiers |
| --- | --- | --- |
| Button | `.viu-btn` | `--primary`, `--outline`, `--ghost`, `--sm/md/lg`, `--full` |
| Badge | `.viu-badge` | `--orange`, `--white` |
| Icon box | `.viu-icon-box` | `--orange/warm/primary/subtle`, `--sm/md/lg` |
| Icon (inline SVG) | `.viu-icon` | `--sm/md/lg`, `--thin` |
| Progress bar | `.viu-progress` + `__fill` | set `--viu-progress-value`; reveal adds `.is-visible` |
| Overlay card | `.viu-card` | composed with `.viu-figure__card` |
| Form control | `.viu-input` | `.viu-textarea` |
| Reveal wrapper | `.viu-reveal` | `--up/left/right/scale/fade`; set `--viu-reveal-delay` |
| Modal | `.viu-modal` + `__panel/__step/...` | step chosen by `[data-step]`; opened by any `[data-viu-modal-open]` |

Icons are inline `<svg>` (Lucide, `stroke="currentColor"`) so they inherit color
from their container. Source SVGs live in [`assets/icons/`](assets/icons/).

---

## 7. theme.json mapping (future block-theme migration)

When this becomes a WordPress block theme, port tokens into `theme.json`. The
custom-property names were chosen to flatten predictably:

```jsonc
{
  "version": 3,
  "settings": {
    "layout": { "contentSize": "1440px", "wideSize": "1440px" },
    "color": {
      "palette": [
        { "slug": "primary",      "color": "#2a2d7c", "name": "Primary" },
        { "slug": "primary-dark", "color": "#1a1c4f", "name": "Primary Dark" },
        { "slug": "accent",       "color": "#f57f20", "name": "Accent" },
        { "slug": "ink",          "color": "#121212", "name": "Ink" },
        { "slug": "text",         "color": "#6a7282", "name": "Body Text" }
      ]
    },
    "typography": {
      "fontFamilies": [
        { "slug": "sans", "name": "Inter", "fontFamily": "Inter, sans-serif" }
      ],
      "fontSizes": [
        { "slug": "base", "size": "1rem" },
        { "slug": "lg",   "size": "1.125rem" },
        { "slug": "h2",   "size": "clamp(1.75rem,3.5vw,3rem)" },
        { "slug": "display", "size": "clamp(2.25rem,5vw,4.5rem)" }
      ]
    },
    "spacing": {
      "spacingSizes": [
        { "slug": "40", "size": "1rem",   "name": "16" },
        { "slug": "60", "size": "2rem",   "name": "32" },
        { "slug": "80", "size": "5rem",   "name": "80" }
      ]
    }
  }
}
```

`theme.json` palette slugs generate `--wp--preset--color--<slug>`. Bridge them
to the existing tokens so component CSS keeps working unchanged:

```css
:root {
  --viu-color-primary: var(--wp--preset--color--primary, #2a2d7c);
  --viu-color-accent:  var(--wp--preset--color--accent,  #f57f20);
  /* …repeat for the rest… */
}
```

---

## 8. Adding/changing things — the rule

1. New color/size/spacing → **add a token** in `01-tokens.css` first.
2. New reusable UI → add a `.viu-*` component in `03-components.css`.
3. New page band → compose in `04-sections.css`, wrap in `.alignfull` +
   `.container .section`.
4. Update this document's tables so the system stays the source of truth.
