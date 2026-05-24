@php
    $statusStyles = match ($contact->status) {
        'new' => 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-300 dark:ring-danger-400/30',
        'read' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-300 dark:ring-warning-400/30',
        'replied' => 'bg-info-50 text-info-700 ring-info-600/20 dark:bg-info-400/10 dark:text-info-300 dark:ring-info-400/30',
        default => 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-500/10 dark:text-gray-300 dark:ring-gray-400/20',
    };
@endphp

<div class="space-y-5">
    {{-- Lead summary --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Interested person
            </p>
            <h2 class="mt-0.5 truncate text-lg font-semibold text-gray-950 dark:text-white">
                {{ $contact->name }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Submitted
                <time datetime="{{ $contact->created_at->toIso8601String() }}">
                    {{ $contact->created_at->format('M j, Y \a\t g:i A') }}
                </time>
            </p>
        </div>
        <span
            class="inline-flex shrink-0 items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusStyles }}"
        >
            {{ ucfirst($contact->status) }}
        </span>
    </div>

    <div
        class="h-px bg-gray-200 dark:bg-gray-700"
        aria-hidden="true"
    ></div>

    {{-- Contact fields --}}
    <section
        class="rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-950/40"
        aria-labelledby="contact-fields-heading"
    >
        <h3
            id="contact-fields-heading"
            class="mb-3 text-sm font-semibold text-gray-900 dark:text-white"
        >
            Contact details
        </h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Email</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    <a
                        href="mailto:{{ $contact->email }}"
                        class="break-all text-primary-600 underline decoration-primary-600/30 underline-offset-2 transition hover:text-primary-500 hover:decoration-primary-500 dark:text-primary-400 dark:decoration-primary-400/40 dark:hover:text-primary-300"
                    >
                        {{ $contact->email }}
                    </a>
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    @if($contact->phone)
                        <a
                            href="tel:{{ $contact->phone }}"
                            class="text-primary-600 underline decoration-primary-600/30 underline-offset-2 transition hover:text-primary-500 hover:decoration-primary-500 dark:text-primary-400 dark:decoration-primary-400/40 dark:hover:text-primary-300"
                        >
                            {{ $contact->phone }}
                        </a>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">—</span>
                    @endif
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">ZIP code of interest</dt>
                <dd class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ $contact->zip_of_interest ?: '—' }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Submission ID</dt>
                <dd class="font-mono text-sm font-medium text-gray-950 dark:text-white">
                    #{{ $contact->id }}
                </dd>
            </div>
            <div class="space-y-1 sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Last updated</dt>
                <dd class="text-sm text-gray-700 dark:text-gray-200">
                    <time datetime="{{ $contact->updated_at->toIso8601String() }}">
                        {{ $contact->updated_at->format('M j, Y g:i A') }}
                    </time>
                </dd>
            </div>
        </dl>
    </section>

    @if($contact->subject)
        <section
            class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/30"
            aria-labelledby="contact-subject-heading"
        >
            <h3
                id="contact-subject-heading"
                class="mb-2 text-sm font-semibold text-gray-900 dark:text-white"
            >
                Subject
            </h3>
            <p class="text-sm leading-relaxed text-gray-800 dark:text-gray-200">
                {{ $contact->subject }}
            </p>
        </section>
    @endif

    {{-- Message --}}
    <section
        class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/30"
        aria-labelledby="contact-message-heading"
    >
        <div
            class="border-b border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-950/50"
        >
            <h3
                id="contact-message-heading"
                class="text-sm font-semibold text-gray-900 dark:text-white"
            >
                How can we help you?
            </h3>
        </div>
        <div class="border-l-4 border-primary-500 px-4 py-4 dark:border-primary-400">
            @if($contact->message !== '')
                <p class="whitespace-pre-wrap text-sm leading-relaxed text-gray-800 dark:text-gray-200">
                    {{ $contact->message }}
                </p>
            @else
                <p class="text-sm italic text-gray-400 dark:text-gray-500">No message provided.</p>
            @endif
        </div>
    </section>
</div>
