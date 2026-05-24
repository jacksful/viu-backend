@php
    $s = $section ?? \App\Models\CmsRecognitionSection::singleton();
    $pct = $s->progressPercentClamped();
@endphp
<section id="exclusivity" class="recognition-section text-white py-5">
    <style>
        .recognition-section {
            background-color: #2a2d7c;
            --recognition-box-bg: rgba(255, 255, 255, 0.1);
            --recognition-muted: rgba(255, 255, 255, 0.72);
        }

        .recognition-section__pill {
            display: inline-block;
            padding: 0.4rem 0.85rem;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        .recognition-section__headline {
            font-size: clamp(1.85rem, 4.2vw, 2.85rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .recognition-section__lead {
            color: var(--recognition-muted);
            font-size: 0.95rem;
            line-height: 1.55;
            max-width: 34rem;
        }

        .recognition-section__box {
            background: var(--recognition-box-bg);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1rem 1.1rem;
            height: 100%;
        }

        .recognition-section__box-accent {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--viu-orange, #f28527);
            margin-top: 0.85rem;
        }

        .recognition-section__visual {
            overflow: hidden;
        }

        .recognition-section__photo {
            display: block;
            width: 100%;
            height: auto;
            vertical-align: middle;
        }

        .recognition-section__card {
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
        }

        .recognition-section__card-kicker {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #5c6478;
        }

        .recognition-section__card-title {
            font-size: clamp(1.05rem, 2vw, 1.35rem);
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            line-height: 1.2;
            color: var(--viu-navy, #1a1e4b);
        }

        .recognition-section__card-footer-label {
            font-size: 0.6rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #8b93a7;
        }

        .recognition-section__card-bar-track {
            height: 8px;
            background: #e8e8ec;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 0.65rem;
        }

        .recognition-section__card-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--viu-orange, #f28527), #e07820);
        }

        .recognition-section__logo {
            height: 28px;
            width: auto;
            object-fit: contain;
            object-position: left center;
        }
    </style>

    <div class="container py-lg-2">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <span class="recognition-section__pill mb-3">{{ $s->badge_text }}</span>

                <h2 class="recognition-section__headline mb-3">
                    <span class="d-block">{{ $s->headline_line_1 }}</span>
                    <span class="d-block">{{ $s->headline_line_2 }}</span>
                    <span class="d-block">{{ $s->headline_line_3 }}</span>
                </h2>

                <p class="recognition-section__lead mb-4">
                    {{ $s->intro }}
                </p>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="recognition-section__box h-100 d-flex align-items-center">
                            <span class="small fw-semibold lh-sm mb-0">{{ $s->box_top_left }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="recognition-section__box h-100 d-flex align-items-center">
                            <span class="small fw-semibold lh-sm mb-0">{{ $s->box_top_right }}</span>
                        </div>
                    </div>
                </div>

                <div class="recognition-section__box recognition-section__box--wide">
                    <p class="small fw-semibold mb-0 lh-lg">
                        {{ $s->box_wide_body }}
                    </p>
                    <div class="recognition-section__box-accent">{{ $s->box_wide_accent }}</div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="recognition-section__visual position-relative">
                    <img
                        src="{{ $s->right_image_url }}"
                        alt=""
                        class="recognition-section__photo"
                        loading="lazy"
                        onerror="this.style.display='none'"
                    >
                    <div class="position-absolute bottom-0 start-0 end-0 p-3 p-sm-4">
                        <div class="recognition-section__card bg-white text-viu-navy p-3 p-sm-4">
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                <img
                                    src="{{ $s->card_logo_url }}"
                                    alt=""
                                    class="recognition-section__logo"
                                    loading="lazy"
                                    onerror="this.style.display='none'"
                                >
                            </div>
                            <div class="recognition-section__card-kicker mb-1">{{ $s->card_kicker }}</div>
                            <div class="recognition-section__card-title mb-3">{{ $s->card_title }}</div>
                            <div class="d-flex justify-content-between align-items-end gap-2 flex-wrap">
                                <span class="recognition-section__card-footer-label mb-0">{{ $s->card_progress_label_left }}</span>
                                <span class="recognition-section__card-footer-label mb-0">{{ $s->card_progress_label_right }}</span>
                            </div>
                            <div
                                class="recognition-section__card-bar-track"
                                role="progressbar"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="{{ $pct }}"
                            >
                                <div class="recognition-section__card-bar-fill" style="width: {{ $pct }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
