@php
    $statusStyles = $intake->isSubmitted()
        ? 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-300 dark:ring-success-400/30'
        : 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-300 dark:ring-warning-400/30';

    $assetUrl = fn (?string $path): ?string => filled($path) ? asset('storage/'.$path) : null;

    $photos = [
        'Headshot' => $assetUrl($intake->headshot_path),
        'Logo' => $assetUrl($intake->logo_path),
        'Brokerage logo' => $assetUrl($intake->brokerage_logo_path),
        'Lifestyle photo' => $assetUrl($intake->lifestyle_photo_path),
    ];
@endphp

<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Client intake
            </p>
            <h2 class="mt-0.5 truncate text-lg font-semibold text-gray-950 dark:text-white">
                {{ $intake->full_name }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                @if($intake->submitted_at)
                    Submitted
                    <time datetime="{{ $intake->submitted_at->toIso8601String() }}">
                        {{ $intake->submitted_at->format('M j, Y \a\t g:i A') }}
                    </time>
                @else
                    Saved but not yet submitted
                @endif
            </p>
        </div>
        <span
            class="inline-flex shrink-0 items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusStyles }}"
        >
            {{ $intake->isSubmitted() ? 'Submitted' : 'Draft' }}
        </span>
    </div>

    <div class="h-px bg-gray-200 dark:bg-gray-700" aria-hidden="true"></div>

    <section
        class="rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-950/40"
        aria-labelledby="intake-territory-heading"
    >
        <h3 id="intake-territory-heading" class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
            Territory
        </h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">ZIP code</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ $intake->zipcode ? 'ZIP '.$intake->zipcode->code : '—' }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Location</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    @if($intake->zipcode?->city && $intake->zipcode?->state)
                        {{ $intake->zipcode->city }}, {{ $intake->zipcode->state }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Client account</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ $subscription->user?->name ?? '—' }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Subscription ID</dt>
                <dd class="font-mono text-sm font-medium text-gray-950 dark:text-white">
                    #{{ $subscription->id }}
                </dd>
            </div>
        </dl>
    </section>

    <section
        class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/30"
        aria-labelledby="intake-brand-heading"
    >
        <h3 id="intake-brand-heading" class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
            Brand assets
        </h3>
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="space-y-1">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Primary color</p>
                <div class="flex items-center gap-2">
                    <span
                        class="inline-block h-6 w-6 rounded border border-gray-200 dark:border-gray-600"
                        style="background-color: {{ $intake->brand_color_1 }}"
                    ></span>
                    <span class="font-mono text-sm text-gray-950 dark:text-white">{{ $intake->brand_color_1 }}</span>
                </div>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Secondary color</p>
                <div class="flex items-center gap-2">
                    <span
                        class="inline-block h-6 w-6 rounded border border-gray-200 dark:border-gray-600"
                        style="background-color: {{ $intake->brand_color_2 }}"
                    ></span>
                    <span class="font-mono text-sm text-gray-950 dark:text-white">{{ $intake->brand_color_2 }}</span>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach($photos as $label => $url)
                <div class="space-y-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                    @if($url)
                        <a
                            href="{{ $url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-950/50"
                        >
                            <img
                                src="{{ $url }}"
                                alt="{{ $label }}"
                                class="aspect-square w-full object-cover"
                            >
                        </a>
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500">—</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section
        class="rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-950/40"
        aria-labelledby="intake-profile-heading"
    >
        <h3 id="intake-profile-heading" class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
            Profile
        </h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-1 sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Tagline</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $intake->tagline }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Credential</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $intake->credential }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Years in business</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $intake->years_in_business }}</dd>
            </div>
            <div class="space-y-1 sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Bio</dt>
                <dd class="whitespace-pre-wrap text-sm leading-relaxed text-gray-800 dark:text-gray-200">{{ $intake->bio }}</dd>
            </div>
        </dl>
    </section>

    <section
        class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/30"
        aria-labelledby="intake-contact-heading"
    >
        <h3 id="intake-contact-heading" class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
            Contact & links
        </h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Display phone</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    <a href="tel:{{ $intake->display_phone }}" class="text-primary-600 hover:text-primary-500 dark:text-primary-400">
                        {{ $intake->display_phone }}
                    </a>
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Display email</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    <a href="mailto:{{ $intake->display_email }}" class="break-all text-primary-600 hover:text-primary-500 dark:text-primary-400">
                        {{ $intake->display_email }}
                    </a>
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Website</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    <a href="{{ $intake->website_url }}" target="_blank" rel="noopener noreferrer" class="break-all text-primary-600 hover:text-primary-500 dark:text-primary-400">
                        {{ $intake->website_url }}
                    </a>
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Instagram</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ $intake->instagram ?: '—' }}
                </dd>
            </div>
            <div class="space-y-1 sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Booking URL</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    @if($intake->booking_url)
                        <a href="{{ $intake->booking_url }}" target="_blank" rel="noopener noreferrer" class="break-all text-primary-600 hover:text-primary-500 dark:text-primary-400">
                            {{ $intake->booking_url }}
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    <section
        class="rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-950/40"
        aria-labelledby="intake-brokerage-heading"
    >
        <h3 id="intake-brokerage-heading" class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
            Brokerage & licensing
        </h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-1 sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Brokerage name</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $intake->brokerage_name }}</dd>
            </div>
            <div class="space-y-1 sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Brokerage address</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $intake->brokerage_address }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">License number</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $intake->license_number }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">License state</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $intake->license_state }}</dd>
            </div>
        </dl>
    </section>

    @if($intake->disclaimers)
        <section
            class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/30"
            aria-labelledby="intake-disclaimers-heading"
        >
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-950/50">
                <h3 id="intake-disclaimers-heading" class="text-sm font-semibold text-gray-900 dark:text-white">
                    Disclaimers
                </h3>
            </div>
            <div class="border-l-4 border-primary-500 px-4 py-4 dark:border-primary-400">
                <p class="whitespace-pre-wrap text-sm leading-relaxed text-gray-800 dark:text-gray-200">
                    {{ $intake->disclaimers }}
                </p>
            </div>
        </section>
    @endif

    <section
        class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/30"
        aria-labelledby="intake-acknowledgements-heading"
    >
        <h3 id="intake-acknowledgements-heading" class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
            Acknowledgements
        </h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Equal housing</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ $intake->equal_housing_acknowledged ? 'Acknowledged' : 'Not acknowledged' }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Confirmed</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ $intake->confirmed ? 'Yes' : 'No' }}
                </dd>
            </div>
        </dl>
    </section>
</div>
