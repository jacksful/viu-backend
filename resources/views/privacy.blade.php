@extends('layouts.app')

@section('title', 'Privacy policy | ' . config('app.name', 'VIU'))

@section('body_class', 'legal-page')

@section('content')
    <section class="alignfull bg-primary viu-legal__hero">
        <div class="container">
            <div class="viu-legal__col">
                <span class="viu-badge viu-badge--white">Legal</span>
                <h1 class="viu-legal__title">Privacy policy</h1>
                <p class="viu-legal__meta">Last updated 17 June 2026</p>
            </div>
        </div>
    </section>

    <section class="alignfull bg-surface">
        <div class="container section--lg">
            <article class="viu-legal__prose">
                <p class="viu-legal__lead">This policy explains how VIU ("we", "us", "our") collects, uses, and protects your information when you visit fullviu.com or use our territory services.</p>

                <h2>1. Information we collect</h2>
                <p>We collect information you provide directly and information gathered automatically as you use the service:</p>
                <ul>
                    <li><strong>Contact details</strong>: your name, email, phone number, and brokerage when you check availability or claim a ZIP.</li>
                    <li><strong>Territory interest</strong>: the ZIP codes you search or reserve.</li>
                    <li><strong>Usage data</strong>: device, browser, referring page, and the pages you view, collected through privacy-respecting analytics.</li>
                </ul>

                <h2>2. How we use your information</h2>
                <p>We use your information to check and reserve ZIP territory, set up and manage your account, respond to your enquiries, deliver and improve the service, and send service-related updates. We do not use it for unrelated advertising.</p>

                <h2>3. Cookies and analytics</h2>
                <p>We use essential cookies to operate the site and lightweight analytics to understand how it is used. You can control or block cookies in your browser settings; some features may not work without them.</p>

                <h2>4. How we share information</h2>
                <p>We do not sell your personal information. We share it only with trusted service providers who help us operate the platform (for example, hosting and email delivery), and only when required by law or to protect our rights.</p>

                <h2>5. Data retention</h2>
                <p>We keep your information for as long as your account is active or as needed to provide the service, and then for a reasonable period to meet legal and operational requirements.</p>

                <h2>6. Your rights</h2>
                <p>You may request access to, correction of, or deletion of your personal information at any time. To make a request, email <a href="mailto:support@fullviu.com">support@fullviu.com</a> and we will respond within a reasonable timeframe.</p>

                <h2>7. Security</h2>
                <p>We use reasonable technical and organisational safeguards to protect your information. No method of transmission or storage is completely secure, so we cannot guarantee absolute security.</p>

                <h2>8. Changes to this policy</h2>
                <p>We may update this policy from time to time. When we do, we will revise the "last updated" date above and, where appropriate, notify you.</p>

                <h2>9. Contact</h2>
                <p>Questions about this policy? Email us at <a href="mailto:support@fullviu.com">support@fullviu.com</a>.</p>
            </article>
        </div>
    </section>
@endsection
