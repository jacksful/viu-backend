@php
    use App\Support\SiteSettings;

    $logoLightUrl = SiteSettings::logoLightUrl();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VIU Intake: Lock in your creative for ZIP {{ $zipCode }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#1A1C4F; --indigo:#2A2D7C; --orange:#F57F20; --orange-dk:#E06D10;
    --gold:#9A4E12; --paper:#F1F2F7; --line:#E5E7EB; --muted:#5F6677; --ink-soft:#3A3D4D;
    --err:#B42318; --ok:#15803D;
    --rail-line:#34376F; --rail-mute:#A9ADD8; --rail-soft:#C9CCE6; --ticket-border:#3D40A0;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html,body{height:100%}
  body{
    background:var(--ink);color:var(--ink);
    font-family:'Inter','Helvetica Neue',Helvetica,Arial,sans-serif;
    -webkit-font-smoothing:antialiased;line-height:1.5;overflow:hidden;
  }
  a{color:inherit}
  .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace}

  /* ---------- App shell ---------- */
  .app{height:100dvh;display:flex;overflow:hidden}

  /* ---------- Left rail ---------- */
  .rail{
    width:360px;flex:none;background:var(--ink);color:#fff;
    padding:36px 36px 30px;display:flex;flex-direction:column;position:relative;overflow:hidden;
  }
  .rail::after{content:"";position:absolute;right:-120px;top:-120px;width:300px;height:300px;
    background:radial-gradient(circle, rgba(245,127,32,.16), transparent 70%);pointer-events:none}
  .brandrow{display:flex;justify-content:space-between;align-items:center;
    border-bottom:1px solid var(--rail-line);padding-bottom:20px;margin-bottom:24px}
  .logo{width:54px;height:34px}
  .logo-fallback{font-weight:800;letter-spacing:2px;font-size:22px;color:#fff}
  .tagk{font-size:9.5px;letter-spacing:2.5px;text-transform:uppercase;color:var(--rail-mute)}

  .ticket{border:1px solid var(--ticket-border);display:flex}
  .ticket .cell{padding:16px 18px;display:flex;flex-direction:column;justify-content:center;align-items:flex-start}
  .ticket .cell.zip{flex:1}
  .ticket .cell.div{border-left:1px solid var(--ticket-border);padding-left:20px}
  .tk-label{font-size:8.5px;letter-spacing:2.5px;text-transform:uppercase;color:var(--rail-mute);margin-bottom:7px}
  .zip-num{font-size:30px;font-weight:500;letter-spacing:6px;color:#F8F9FD;line-height:1}
  .chip{display:inline-block;background:var(--orange);color:var(--ink);font-size:9px;font-weight:700;
    letter-spacing:1.4px;text-transform:uppercase;padding:6px 10px}

  /* ---------- Stepper ---------- */
  .stepper{margin-top:34px;flex:1;min-height:0}
  .stepper ol{list-style:none}
  .step-item{position:relative;display:flex;gap:15px;padding-bottom:26px;cursor:default}
  .step-item:last-child{padding-bottom:0}
  .step-item .node{position:relative;z-index:1;flex:none;width:30px;height:30px;border:1.5px solid var(--rail-line);
    display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;
    color:var(--rail-mute);background:var(--ink);transition:all .35s cubic-bezier(.4,0,.2,1)}
  .step-item:not(:last-child)::before{content:"";position:absolute;left:15px;top:30px;bottom:-1px;width:1.5px;
    background:var(--rail-line);transform:translateX(-50%);transition:background .4s ease}
  .step-item .meta{padding-top:5px}
  .step-item .s-k{font-size:9px;letter-spacing:2px;text-transform:uppercase;color:var(--rail-mute);transition:color .3s}
  .step-item .s-t{font-size:14.5px;font-weight:600;color:var(--rail-soft);margin-top:2px;transition:color .3s}
  .step-item.done::before{background:var(--orange)}
  .step-item.done .node{background:var(--orange);border-color:var(--orange);color:var(--ink)}
  .step-item.done .s-t{color:#fff}
  .step-item.done{cursor:pointer}
  .step-item.current .node{border-color:var(--orange);color:var(--orange);
    box-shadow:0 0 0 4px rgba(245,127,32,.16);transform:scale(1.04)}
  .step-item.current .s-k{color:var(--orange)}
  .step-item.current .s-t{color:#fff;font-weight:700}

  .rail-foot{margin-top:22px;padding-top:18px;border-top:1px solid var(--rail-line);
    font-size:11.5px;color:var(--rail-mute);line-height:1.6}
  .rail-foot b{color:#fff;font-weight:700}

  /* ---------- Right pane ---------- */
  .pane{flex:1;min-width:0;background:var(--paper);display:flex;flex-direction:column;overflow:hidden}
  .topbar{display:none}

  .head{padding:40px 56px 22px;flex:none}
  .eyebrow{font-size:11px;letter-spacing:3px;text-transform:uppercase;color:var(--gold);font-weight:700}
  .head h1{font-size:clamp(26px,3vw,34px);font-weight:800;letter-spacing:-.5px;line-height:1.08;
    color:var(--ink);margin:10px 0 7px}
  .head p{color:var(--muted);font-size:14.5px;max-width:54ch}

  .body{flex:1;min-height:0;overflow-y:auto;padding:6px 56px 30px;scrollbar-width:thin}
  .body::-webkit-scrollbar{width:8px}
  .body::-webkit-scrollbar-thumb{background:#D5D8E4}

  .step-panel{display:none}
  .step-panel.active{display:block}
  .step-panel.active .reveal{animation:rise .5s cubic-bezier(.2,.7,.2,1) both}
  .step-panel.active .reveal:nth-child(1){animation-delay:.02s}
  .step-panel.active .reveal:nth-child(2){animation-delay:.07s}
  .step-panel.active .reveal:nth-child(3){animation-delay:.12s}
  .step-panel.active .reveal:nth-child(4){animation-delay:.17s}
  .step-panel.active .reveal:nth-child(5){animation-delay:.22s}
  .step-panel.active .reveal:nth-child(6){animation-delay:.27s}
  @keyframes rise{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
  @media (prefers-reduced-motion:reduce){.step-panel.active .reveal{animation:none}}

  /* ---------- Fields ---------- */
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px 20px}
  .field{display:flex;flex-direction:column;gap:7px}
  .field.full{grid-column:1 / -1}
  label{font-size:12.5px;font-weight:600;color:var(--ink)}
  label .opt{color:var(--muted);font-weight:500}
  .req{color:var(--orange)}
  .hint{font-size:11.5px;color:var(--muted)}
  input[type=text],input[type=email],input[type=tel],input[type=url],input[type=number],textarea,select{
    font-family:inherit;font-size:14.5px;color:var(--ink);background:#fff;border:1px solid var(--line);
    padding:11px 13px;width:100%;outline:none;border-radius:0;-webkit-appearance:none;appearance:none;
    transition:border-color .15s,box-shadow .15s}
  textarea{resize:vertical;min-height:74px}
  select{background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'><path d='M1 1l5 5 5-5' stroke='%235F6677' stroke-width='1.6' fill='none'/></svg>");
    background-repeat:no-repeat;background-position:right 13px center;padding-right:34px}
  input:focus,textarea:focus,select:focus{border-color:var(--ink);box-shadow:0 0 0 3px rgba(42,45,124,.10)}
  input.bad,textarea.bad,select.bad,.drop.bad{border-color:var(--err);box-shadow:0 0 0 3px rgba(180,35,24,.10)}
  .err-msg{font-size:11.5px;color:var(--err);display:none}
  .err-msg.show{display:block}

  .drop{border:1px dashed #C7CBD8;background:#FAFAFD;padding:12px 14px;display:flex;align-items:center;gap:12px;
    cursor:pointer;transition:border-color .15s,background .15s}
  .drop:hover{border-color:var(--orange);background:#fff}
  .drop input[type=file]{display:none}
  .drop .ic{width:30px;height:30px;flex:none;border:1px solid var(--line);display:flex;align-items:center;
    justify-content:center;color:var(--gold);font-size:15px;background:#fff}
  .drop .dt{font-size:13px;font-weight:600;color:var(--ink);line-height:1.25}
  .drop .dd{font-size:11px;color:var(--muted)}
  .drop.has{border-style:solid;border-color:var(--gold);background:#fff}
  .drop.has .dt{color:var(--gold)}

  .colorrow{display:flex;gap:9px;align-items:center}
  .swatch{width:42px;height:42px;flex:none;border:1px solid var(--line);padding:0;cursor:pointer;border-radius:0}
  input[type=color]{-webkit-appearance:none;appearance:none;border:0}
  input[type=color]::-webkit-color-swatch-wrapper{padding:0}
  input[type=color]::-webkit-color-swatch{border:0}

  .check{display:flex;gap:11px;align-items:flex-start;font-size:13.5px;color:var(--ink-soft);
    border:1px solid var(--line);background:#fff;padding:14px 16px}
  .check input{margin-top:2px;width:17px;height:17px;accent-color:var(--orange);flex:none}

  .review{border:1px solid var(--line);background:#fff}
  .r-row{display:flex;justify-content:space-between;gap:18px;padding:13px 18px;border-bottom:1px solid var(--line);font-size:13.5px}
  .r-row:last-child{border-bottom:0}
  .r-k{color:var(--muted)}
  .r-v{color:var(--ink);font-weight:600;text-align:right;max-width:62%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .r-v.missing{color:var(--err)}

  /* ---------- Footer nav ---------- */
  .foot{flex:none;border-top:1px solid var(--line);background:#fff;padding:18px 56px}
  .progress{height:3px;background:var(--line);margin-bottom:16px;overflow:hidden}
  .progress > i{display:block;height:100%;width:20%;background:var(--orange);transition:width .45s cubic-bezier(.4,0,.2,1)}
  .nav{display:flex;align-items:center;justify-content:space-between;gap:14px}
  .count{font-size:12.5px;color:var(--muted);font-weight:600;letter-spacing:.3px}
  .nav-btns{display:flex;gap:10px}
  .btn{font-family:inherit;font-weight:700;font-size:14px;padding:13px 26px;border:0;cursor:pointer;border-radius:0;
    display:inline-flex;align-items:center;gap:8px;transition:background .15s,color .15s,opacity .15s;white-space:nowrap}
  .btn.primary{background:var(--orange);color:var(--ink)}
  .btn.primary:hover{background:var(--orange-dk)}
  .btn.ghost{background:transparent;color:var(--ink);border:1px solid var(--line)}
  .btn.ghost:hover{border-color:var(--ink)}
  .btn[disabled]{opacity:.4;cursor:not-allowed;pointer-events:none}

  /* ---------- Success ---------- */
  .done-wrap{display:none;flex:1;align-items:center;justify-content:center;padding:40px}
  .done-wrap.show{display:flex}
  .done-card{max-width:440px;text-align:center;animation:rise .5s cubic-bezier(.2,.7,.2,1) both}
  .done-card .seal{width:64px;height:64px;border:2px solid var(--orange);color:var(--gold);
    display:flex;align-items:center;justify-content:center;font-size:30px;margin:0 auto 22px}
  .done-card h2{font-size:28px;font-weight:800;color:var(--ink);margin-bottom:12px;letter-spacing:-.4px}
  .done-card p{color:var(--ink-soft);margin:0 auto 8px;max-width:42ch}

  /* ---------- Responsive ---------- */
  @media (max-width:900px){
    body{overflow:auto}
    .app{flex-direction:column;height:auto;min-height:100dvh}
    .rail{display:none}
    .topbar{display:block;background:var(--ink);color:#fff;padding:18px 22px 16px;position:sticky;top:0;z-index:30}
    .tb-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
    .tb-zip{font-size:11px;letter-spacing:2px;color:var(--rail-mute);text-transform:uppercase}
    .tb-zip b{color:#fff;font-family:ui-monospace,Menlo,monospace;letter-spacing:2px}
    .dots{display:flex;gap:7px;align-items:center}
    .dots .d{width:9px;height:9px;border:1.5px solid var(--rail-line)}
    .dots .d.done{background:var(--orange);border-color:var(--orange)}
    .dots .d.current{border-color:var(--orange);box-shadow:0 0 0 3px rgba(245,127,32,.2)}
    .tb-step{font-size:11px;color:var(--rail-mute);letter-spacing:1.5px;text-transform:uppercase}
    .tb-step b{color:#fff}
    .pane{overflow:visible}
    .head{padding:26px 22px 16px}
    .body{overflow:visible;padding:4px 22px 24px}
    .foot{padding:16px 22px;position:sticky;bottom:0}
    .grid{grid-template-columns:1fr;gap:14px}
    .count{display:none}
  }
</style>
</head>
<body>
<div class="app">

  <!-- ============ LEFT RAIL ============ -->
  <aside class="rail">
    <div class="brandrow">
      <span style="display:inline-flex;align-items:center">
        <img src="{{ $logoLightUrl }}" alt="VIU" class="logo"
             onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">
        <span class="logo-fallback" style="display:none">VIU</span>
      </span>
      <span class="tagk">Territory&nbsp;Exclusivity</span>
    </div>

    <div class="ticket">
      <div class="cell zip"><div class="tk-label">Territory</div><div class="zip-num mono">{{ $zipCode }}</div></div>
      <div class="cell div"><div class="tk-label">Status</div><span class="chip">Intake</span></div>
    </div>

    <nav class="stepper" aria-label="Progress"><ol id="stepper"></ol></nav>

    <div class="rail-foot"><b>About 10 minutes.</b> Your details build your ad creative, we start the moment you submit.</div>
  </aside>

  <!-- ============ RIGHT PANE ============ -->
  <main class="pane">

    <div class="topbar">
      <div class="tb-row">
        <span class="tb-zip">ZIP <b>{{ $zipCode }}</b></span>
        <span class="tb-step">Step <b id="tbCur">1</b> / 5</span>
      </div>
      <div class="dots" id="dots"></div>
    </div>

    <form id="intake" novalidate style="display:contents" method="post" action="{{ $submitUrl }}" enctype="multipart/form-data">
      @csrf

      <div class="head">
        <div class="eyebrow" id="stepEyebrow">Step 1 / 5</div>
        <h1 id="stepTitle">Brand assets</h1>
        <p id="stepDesc">High-res files render best. JPG, PNG, SVG, AI, or EPS.</p>
      </div>

      <div class="body">

        <!-- STEP 1: Brand assets -->
        <section class="step-panel active" data-step="0">
          <div class="grid">
            <div class="field reveal" data-required>
              <label>Professional headshot <span class="req">*</span></label>
              <label class="drop" data-file>
                <span class="ic" aria-hidden="true">&#8682;</span>
                <span><span class="dt">Upload headshot</span><br><span class="dd">Min 1500px long edge</span></span>
                <input type="file" name="headshot" accept=".jpg,.jpeg,.png" required>
              </label>
              <span class="err-msg">Please upload a headshot.</span>
            </div>
            <div class="field reveal" data-required>
              <label>Your logo <span class="req">*</span></label>
              <label class="drop" data-file>
                <span class="ic" aria-hidden="true">&#8682;</span>
                <span><span class="dt">Upload logo</span><br><span class="dd">Vector or transparent PNG</span></span>
                <input type="file" name="logo" accept=".svg,.ai,.eps,.png" required>
              </label>
              <span class="err-msg">Please upload your logo.</span>
            </div>
            <div class="field reveal">
              <label>Brokerage logo <span class="opt">(optional)</span></label>
              <label class="drop" data-file>
                <span class="ic" aria-hidden="true">&#8682;</span>
                <span><span class="dt">Upload brokerage logo</span><br><span class="dd">If required on creative</span></span>
                <input type="file" name="brokerage_logo" accept=".svg,.ai,.eps,.png">
              </label>
            </div>
            <div class="field reveal">
              <label>Lifestyle photo <span class="opt">(optional)</span></label>
              <label class="drop" data-file>
                <span class="ic" aria-hidden="true">&#8682;</span>
                <span><span class="dt">Upload lifestyle photo</span><br><span class="dd">Used for variant creative</span></span>
                <input type="file" name="lifestyle" accept=".jpg,.jpeg,.png">
              </label>
            </div>
            <div class="field reveal" data-required>
              <label>Brand color 1 <span class="req">*</span></label>
              <div class="colorrow">
                <input type="color" class="swatch" value="#1A1C4F" data-sync="c1">
                <input type="text" name="color1" id="c1" value="#1A1C4F" pattern="^#?[0-9A-Fa-f]{6}$" required>
              </div>
              <span class="err-msg">Enter a 6-digit hex color.</span>
            </div>
            <div class="field reveal" data-required>
              <label>Brand color 2 <span class="req">*</span></label>
              <div class="colorrow">
                <input type="color" class="swatch" value="#F57F20" data-sync="c2">
                <input type="text" name="color2" id="c2" value="#F57F20" pattern="^#?[0-9A-Fa-f]{6}$" required>
              </div>
              <span class="err-msg">Enter a 6-digit hex color.</span>
            </div>
          </div>
        </section>

        <!-- STEP 2: Bio & positioning -->
        <section class="step-panel" data-step="1">
          <div class="grid">
            <div class="field full reveal" data-required>
              <label>Full name (as it should appear) <span class="req">*</span></label>
              <input type="text" name="full_name" required placeholder="Jordan A. Rivera" value="{{ old('full_name', $defaults['full_name'] ?? '') }}">
              <span class="err-msg">Please enter your name.</span>
            </div>
            <div class="field full reveal" data-required>
              <label>Tagline <span class="req">*</span> <span class="opt">&middot; under 8 words</span></label>
              <input type="text" name="tagline" id="tagline" required placeholder="Selling the homes others can't.">
              <span class="hint" id="tagCount">0 words</span>
              <span class="err-msg">Add a tagline of 8 words or fewer.</span>
            </div>
            <div class="field full reveal" data-required>
              <label>Short bio <span class="req">*</span> <span class="opt">&middot; 2 to 3 sentences</span></label>
              <textarea name="bio" required placeholder="Who you are and why sellers choose you."></textarea>
              <span class="err-msg">Please add a short bio.</span>
            </div>
            <div class="field reveal" data-required>
              <label>Years in the business <span class="req">*</span></label>
              <input type="number" name="years" min="0" max="80" required placeholder="12">
              <span class="err-msg">Required.</span>
            </div>
            <div class="field reveal" data-required>
              <label>One notable credential <span class="req">*</span></label>
              <input type="text" name="credential" required placeholder="Top 1% in [City], 2024">
              <span class="err-msg">Required.</span>
            </div>
          </div>
        </section>

        <!-- STEP 3: Contact info -->
        <section class="step-panel" data-step="2">
          <div class="grid">
            <div class="field reveal" data-required>
              <label>Phone to display <span class="req">*</span></label>
              <input type="tel" name="phone" required placeholder="(555) 555-0123" value="{{ old('phone', $defaults['phone'] ?? '') }}">
              <span class="err-msg">Required.</span>
            </div>
            <div class="field reveal" data-required>
              <label>Email to display <span class="req">*</span></label>
              <input type="email" name="email" required placeholder="you@brokerage.com" value="{{ old('email', $defaults['email'] ?? '') }}">
              <span class="err-msg">Enter a valid email.</span>
            </div>
            <div class="field full reveal" data-required>
              <label>Website URL <span class="req">*</span></label>
              <input type="url" name="website" required placeholder="https://...">
              <span class="err-msg">Enter a valid URL.</span>
            </div>
            <div class="field reveal">
              <label>Instagram <span class="opt">(optional)</span></label>
              <input type="text" name="instagram" placeholder="@yourhandle">
            </div>
            <div class="field reveal">
              <label>Booking link <span class="opt">(optional)</span></label>
              <input type="url" name="booking" placeholder="https://calendly.com/...">
            </div>
          </div>
        </section>

        <!-- STEP 4: Brokerage & compliance -->
        <section class="step-panel" data-step="3">
          <div class="grid">
            <div class="field full reveal" data-required>
              <label>Brokerage name (as it must appear) <span class="req">*</span></label>
              <input type="text" name="brokerage" required>
              <span class="err-msg">Required.</span>
            </div>
            <div class="field full reveal" data-required>
              <label>Brokerage office address <span class="req">*</span></label>
              <input type="text" name="brokerage_address" required>
              <span class="err-msg">Required.</span>
            </div>
            <div class="field reveal" data-required>
              <label>License # <span class="req">*</span></label>
              <input type="text" name="license" required>
              <span class="err-msg">Required.</span>
            </div>
            <div class="field reveal" data-required>
              <label>State licensed in <span class="req">*</span></label>
              <select name="state" required><option value="">Select state</option></select>
              <span class="err-msg">Required.</span>
            </div>
            <div class="field full reveal">
              <label>Required disclaimers <span class="opt">(paste exact wording, if any)</span></label>
              <textarea name="disclaimers" placeholder="Exact disclaimer text your brokerage requires."></textarea>
            </div>
            <div class="field full reveal">
              <label class="check"><input type="checkbox" name="eho" required>
                <span>I understand VIU adds the <strong>Equal Housing Opportunity</strong> logo to all creative, as required.</span></label>
              <span class="err-msg">Please acknowledge to continue.</span>
            </div>
          </div>
        </section>

        <!-- STEP 5: Review & submit -->
        <section class="step-panel" data-step="4">
          <div class="review reveal" id="review"></div>
          <div class="field full reveal" data-required style="margin-top:18px">
            <label class="check"><input type="checkbox" name="confirm" required>
              <span>Everything above is accurate, and I authorize VIU to use these assets to build and run my ad creative in ZIP {{ $zipCode }}.</span></label>
            <span class="err-msg">Please confirm to submit.</span>
          </div>
        </section>

      </div>

      <div class="foot">
        <div class="progress"><i id="bar"></i></div>
        <div class="nav">
          <span class="count" id="count">Step 1 of 5</span>
          <div class="nav-btns">
            <button type="button" class="btn ghost" id="back" disabled>&larr;&nbsp; Back</button>
            <button type="button" class="btn primary" id="next">Next &nbsp;&rarr;</button>
          </div>
        </div>
      </div>
    </form>

    <div class="done-wrap" id="done" role="status" aria-live="polite">
      <div class="done-card">
        <div class="seal" aria-hidden="true">&#10003;</div>
        <h2>Your intake is in.</h2>
        <p>Thanks, {{ $firstName }}. We have everything we need to start building your creative for ZIP {{ $zipCode }}.</p>
        <p>Watch your inbox, your creative preview lands in a few days for one quick approval.</p>
      </div>
    </div>

  </main>
</div>

<script>
(function(){
  var STEPS=[
    {key:"Brand assets",            desc:"High-res files render best. JPG, PNG, SVG, AI, or EPS."},
    {key:"Bio & positioning",       desc:"How your name and message appear on the creative."},
    {key:"Contact info",            desc:"What we display so prospects can reach you."},
    {key:"Brokerage & compliance",  desc:"Real-estate creative passes compliance before it runs."},
    {key:"Review & submit",         desc:"One last look, then we start building."}
  ];
  var TOTAL=STEPS.length;
  var form=document.getElementById('intake');
  var panels=Array.prototype.slice.call(form.querySelectorAll('.step-panel'));
  var cur=0, maxReached=0;

  /* build stepper + dots with DOM (no innerHTML) */
  var ol=document.getElementById('stepper'), dots=document.getElementById('dots');
  function el(tag,cls,text){var e=document.createElement(tag);if(cls)e.className=cls;if(text!=null)e.textContent=text;return e;}
  STEPS.forEach(function(s,i){
    var li=el('li','step-item'); li.dataset.i=i;
    var node=el('div','node',String(i+1));
    var meta=el('div','meta');
    meta.appendChild(el('div','s-k','Step '+(i+1)));
    meta.appendChild(el('div','s-t',s.key));
    li.appendChild(node); li.appendChild(meta);
    li.addEventListener('click',function(){ if(li.classList.contains('done')) goTo(i); });
    ol.appendChild(li);
    dots.appendChild(el('span','d'));
  });
  var items=Array.prototype.slice.call(ol.children);
  var dotEls=Array.prototype.slice.call(dots.children);

  /* US states */
  var STATES=["AL","AK","AZ","AR","CA","CO","CT","DE","FL","GA","HI","ID","IL","IN","IA","KS","KY","LA","ME","MD","MA","MI","MN","MS","MO","MT","NE","NV","NH","NJ","NM","NY","NC","ND","OH","OK","OR","PA","RI","SC","SD","TN","TX","UT","VT","VA","WA","WV","WI","WY","DC"];
  var sel=form.querySelector('select[name=state]');
  STATES.forEach(function(s){sel.appendChild(el('option',null,s)).value=s;});

  /* color sync */
  form.querySelectorAll('input[type=color][data-sync]').forEach(function(p){
    var hex=document.getElementById(p.getAttribute('data-sync'));
    p.addEventListener('input',function(){hex.value=p.value.toUpperCase();});
    hex.addEventListener('input',function(){var v=hex.value.trim();if(/^#?[0-9A-Fa-f]{6}$/.test(v)){p.value=v[0]==='#'?v:'#'+v;}});
  });

  /* file chosen */
  form.querySelectorAll('.drop[data-file]').forEach(function(d){
    var inp=d.querySelector('input[type=file]'), dt=d.querySelector('.dt'), orig=dt.textContent;
    inp.addEventListener('change',function(){
      if(inp.files&&inp.files.length){d.classList.add('has');dt.textContent=inp.files[0].name;}
      else{d.classList.remove('has');dt.textContent=orig;}
    });
  });

  /* tagline counter */
  var tag=document.getElementById('tagline'), tagCount=document.getElementById('tagCount');
  function words(v){return v.trim()?v.trim().split(/\s+/).length:0;}
  tag.addEventListener('input',function(){
    var n=words(tag.value);
    tagCount.textContent=n+' word'+(n===1?'':'s')+(n>8?', trim to 8 or fewer':'');
    tagCount.style.color=n>8?'#B42318':'#5F6677';
  });

  /* validation */
  function reqOf(i){return Array.prototype.slice.call(panels[i].querySelectorAll('[required]'));}
  function validEmail(v){return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);}
  function ok(e){
    if(e.type==='checkbox')return e.checked;
    if(e.type==='file')return e.files&&e.files.length>0;
    if(e===tag)return e.value.trim()!==''&&words(e.value)<=8;
    if(e.type==='email')return validEmail(e.value);
    return e.value.trim()!=='';
  }
  function mark(e,bad){var f=e.closest('.field');if(!f)return;e.classList.toggle('bad',bad);var m=f.querySelector('.err-msg');if(m)m.classList.toggle('show',bad);}
  function validate(i){
    var first=null;
    reqOf(i).forEach(function(e){var b=!ok(e);mark(e,b);if(b&&!first)first=e;});
    if(first){ if(first.focus&&!first.classList.contains('drop'))first.focus(); (first.closest('.field')||first).scrollIntoView({behavior:'smooth',block:'center'}); }
    return !first;
  }
  form.addEventListener('input',function(e){ if(e.target.classList.contains('bad')&&ok(e.target)) mark(e.target,false); });
  form.addEventListener('change',function(e){ if(e.target.classList.contains('bad')&&ok(e.target)) mark(e.target,false); });

  /* review builder (DOM + textContent) */
  function val(n){var e=form.querySelector('[name='+n+']');return e?e.value.trim():'';}
  function fileName(n){var e=form.querySelector('[name='+n+']');return e&&e.files&&e.files.length?e.files[0].name:'';}
  function buildReview(){
    var files=['headshot','logo','brokerage_logo','lifestyle'].map(fileName).filter(Boolean).length;
    var rows=[
      ['Name', val('full_name')],
      ['Tagline', val('tagline')],
      ['Files attached', files+' file'+(files===1?'':'s')],
      ['Brand colors', (val('color1')||'?')+'  +  '+(val('color2')||'?')],
      ['Contact', [val('phone'),val('email')].filter(Boolean).join('   ')],
      ['Brokerage', val('brokerage')],
      ['License', [val('license'),val('state')].filter(Boolean).join('   ')]
    ];
    var box=document.getElementById('review');
    box.textContent='';
    rows.forEach(function(r){
      var row=el('div','r-row');
      row.appendChild(el('span','r-k',r[0]));
      row.appendChild(el('span','r-v'+(r[1]?'':' missing'), r[1]||'Missing'));
      box.appendChild(row);
    });
  }

  /* navigation */
  var nextBtn=document.getElementById('next'), backBtn=document.getElementById('back');
  function render(){
    panels.forEach(function(p,i){p.classList.toggle('active',i===cur);});
    document.getElementById('stepEyebrow').textContent='Step '+(cur+1)+' / '+TOTAL;
    document.getElementById('stepTitle').textContent=STEPS[cur].key;
    document.getElementById('stepDesc').textContent=STEPS[cur].desc;
    document.getElementById('count').textContent='Step '+(cur+1)+' of '+TOTAL;
    document.getElementById('tbCur').textContent=String(cur+1);
    document.getElementById('bar').style.width=((cur+1)/TOTAL*100)+'%';
    items.forEach(function(li,i){ li.classList.toggle('current',i===cur); li.classList.toggle('done',i<cur); });
    dotEls.forEach(function(d,i){ d.className='d'+(i<cur?' done':'')+(i===cur?' current':''); });
    backBtn.disabled=(cur===0);
    nextBtn.textContent=(cur===TOTAL-1)?'Submit intake':'Next';
    document.querySelector('.body').scrollTop=0;
    if(cur===TOTAL-1)buildReview();
  }
  function goTo(i){ cur=i; maxReached=Math.max(maxReached,cur); render(); }
  function next(){
    if(!validate(cur))return;
    if(cur===TOTAL-1){ submit(); return; }
    cur++; maxReached=Math.max(maxReached,cur); render();
  }
  function back(){ if(cur>0){cur--; render();} }
  nextBtn.addEventListener('click',next);
  backBtn.addEventListener('click',back);
  form.addEventListener('keydown',function(e){ if(e.key==='Enter'&&e.target.tagName!=='TEXTAREA'){e.preventDefault();next();} });

  /* submit */
  function submit(){
    for(var i=0;i<panels.length;i++){ if(!validate(i)){ goTo(i); return; } }
    nextBtn.disabled=true; nextBtn.textContent='Submitting…';
    var data=new FormData(form);
    fetch(@json($submitUrl),{
      method:'POST',
      body:data,
      headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
      credentials:'same-origin'
    }).then(function(res){
      return res.json().then(function(body){ return {ok:res.ok, body:body}; });
    }).then(function(result){
      if(!result.ok){
        nextBtn.disabled=false;
        nextBtn.textContent='Submit intake';
        alert(result.body.message||'Something went wrong. Please try again.');
        return;
      }
      form.style.display='none';
      document.getElementById('done').classList.add('show');
    }).catch(function(){
      nextBtn.disabled=false;
      nextBtn.textContent='Submit intake';
      alert('Something went wrong. Please try again.');
    });
  }

  @if($submitted)
  form.style.display='none';
  document.getElementById('done').classList.add('show');
  @else
  render();
  @endif
})();
</script>
</body>
</html>
