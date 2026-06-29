@extends('layouts.app')

@section('title', 'Payment successful | ' . config('app.name', 'VIU'))

@section('body_class', 'checkout-success-page')

@section('content')
    <section class="alignfull bg-primary viu-legal__hero">
        <div class="container">
            <div class="viu-legal__col">
                <span class="viu-badge viu-badge--white">Payment confirmed</span>
                <h1 class="viu-legal__title">Payment Successful</h1>
                <p class="viu-legal__meta">Thank you for your purchase!</p>
            </div>
        </div>
    </section>

    <section class="alignfull bg-surface">
        <div class="container section--lg">
            <div class="viu-legal__col viu-checkout-success">
                <div class="viu-checkout-success__intro">
                    <span class="viu-icon-box viu-icon-box--md viu-icon-box--success" aria-hidden="true">
                        <svg class="viu-icon viu-icon--lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
                    </span>
                    <p class="viu-intro__text">Your territory subscription is being activated. We will have your exclusive ZIP ready shortly.</p>
                </div>

                @if ($details)
                    <div class="viu-card viu-checkout-success__details">
                        <div class="viu-intro viu-intro--snug">
                            <span class="viu-badge viu-badge--orange">Receipt</span>
                            <h2 class="viu-h2">Payment details</h2>
                        </div>
                        <div class="viu-checkout-success__table-wrap">
                            <table class="viu-checkout-success__table">
                                <tbody>
                                    <tr>
                                        <th scope="row">Order number</th>
                                        <td>{{ $details['order_number'] }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Amount</th>
                                        <td>{{ $details['amount'] }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Payment method</th>
                                        <td>{{ $details['payment_method'] }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Transaction ID</th>
                                        <td>
                                            @php
                                                $transactionId = $details['transaction_id'];
                                                $transactionIdShort = strlen($transactionId) > 10
                                                    ? substr($transactionId, 0, 10).'…'
                                                    : $transactionId;
                                            @endphp
                                            @if (strlen($transactionId) > 10)
                                                <span class="viu-truncate-tooltip" tabindex="0" aria-label="Transaction ID: {{ $transactionId }}">
                                                    <span class="viu-truncate-tooltip__text">{{ $transactionIdShort }}</span>
                                                    <span class="viu-truncate-tooltip__tip" role="tooltip">{{ $transactionId }}</span>
                                                </span>
                                            @else
                                                {{ $transactionId }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Subscription start</th>
                                        <td>{{ $details['subscription_start'] }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Subscription end</th>
                                        <td>{{ $details['subscription_end'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="viu-checkout-success__footer">
                    <p class="viu-form-result viu-form-result--success">Check your email for account access instructions and next steps.</p>
                    <a class="viu-btn viu-btn--primary viu-btn--md" href="{{ route('user.login') }}">Sign in to your account</a>
                </div>
            </div>
        </div>
    </section>

    @include('components.cta-banner')
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.viu-truncate-tooltip').forEach(function (trigger) {
            var tip = trigger.querySelector('.viu-truncate-tooltip__tip');
            if (!tip) {
                return;
            }

            function positionTip() {
                var rect = trigger.getBoundingClientRect();
                tip.style.left = rect.left + 'px';
                tip.style.top = (rect.bottom + 6) + 'px';
            }

            function showTip() {
                positionTip();
                trigger.classList.add('is-active');
            }

            function hideTip() {
                trigger.classList.remove('is-active');
            }

            trigger.addEventListener('mouseenter', showTip);
            trigger.addEventListener('focus', showTip);
            trigger.addEventListener('mouseleave', hideTip);
            trigger.addEventListener('blur', hideTip);
            window.addEventListener('scroll', hideTip, { passive: true });
            window.addEventListener('resize', hideTip);
        });
    </script>
@endpush
