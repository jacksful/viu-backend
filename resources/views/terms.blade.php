@extends('layouts.app')

@section('title', 'Terms of service | ' . config('app.name', 'VIU'))

@section('body_class', 'legal-page')

@section('content')
    <section class="alignfull bg-primary viu-legal__hero">
        <div class="container">
            <div class="viu-legal__col">
                <span class="viu-badge viu-badge--white">Legal</span>
                <h1 class="viu-legal__title">Terms of service</h1>
                <p class="viu-legal__meta">Last updated 17 June 2026</p>
            </div>
        </div>
    </section>

    <section class="alignfull bg-surface">
        <div class="container section--lg">
            <article class="viu-legal__prose">
                <p class="viu-legal__lead">These terms govern your access to and use of VIU's website and territory services. By using the service, you agree to them.</p>

                <h2>1. Acceptance of terms</h2>
                <p>By accessing fullviu.com or claiming a ZIP territory, you confirm that you have read, understood, and agree to be bound by these terms. If you do not agree, please do not use the service.</p>

                <h2>2. The service</h2>
                <p>VIU provides predictive brand positioning for real estate professionals, placing your brand in front of likely sellers across the platforms they already visit. Each ZIP code is assigned to a single active subscriber at a time.</p>

                <h2>3. Territory and exclusivity</h2>
                <p>While your subscription for a ZIP is active, that ZIP is reserved exclusively for you and no other subscriber can enter it. If you cancel, the ZIP is released and becomes available to other agents. Exclusivity applies only for the period your account remains active and in good standing.</p>

                <h2>4. Billing and cancellation</h2>
                <ul>
                    <li>Pricing starts at <strong>$199 per month, per ZIP code</strong>.</li>
                    <li>Your rate is locked in for as long as your subscription stays active.</li>
                    <li>You may cancel at any time; there are no long-term contracts or cancellation fees.</li>
                    <li>After cancellation you retain access through the end of your current billing period, then coverage for that ZIP is released.</li>
                </ul>

                <h2>5. Acceptable use</h2>
                <p>You agree not to misuse the service, attempt to gain unauthorised access, interfere with its operation, or use it for any unlawful purpose. We may suspend accounts that violate these terms.</p>

                <h2>6. Intellectual property</h2>
                <p>The VIU platform, brand, and content are owned by VIU Real Estate Solutions and protected by applicable laws. Your subscription grants you a limited right to use the service, not ownership of it.</p>

                <h2>7. Disclaimers</h2>
                <p>The service is provided "as is" and "as available". While our predictive model is designed to position your brand early, we do not guarantee specific leads, listings, or results.</p>

                <h2>8. Limitation of liability</h2>
                <p>To the maximum extent permitted by law, VIU is not liable for indirect, incidental, or consequential damages arising from your use of the service. Our total liability will not exceed the amount you paid in the three months before the claim.</p>

                <h2>9. Changes to these terms</h2>
                <p>We may update these terms from time to time. Continued use of the service after changes take effect constitutes acceptance of the revised terms.</p>

                <h2>10. Contact</h2>
                <p>Questions about these terms? Email us at <a href="mailto:support@fullviu.com">support@fullviu.com</a>.</p>
            </article>
        </div>
    </section>
@endsection
