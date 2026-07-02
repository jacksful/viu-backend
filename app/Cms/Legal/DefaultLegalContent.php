<?php

namespace App\Cms\Legal;

class DefaultLegalContent
{
    public static function privacyHero(): array
    {
        return [
            'badge_text' => 'Legal',
            'title' => 'Privacy policy',
            'last_updated' => '17 June 2026',
        ];
    }

    public static function termsHero(): array
    {
        return [
            'badge_text' => 'Legal',
            'title' => 'Terms of service',
            'last_updated' => '17 June 2026',
        ];
    }

    public static function privacyContent(): array
    {
        return [
            'lead' => 'This policy explains how VIU ("we", "us", "our") collects, uses, and protects your information when you visit fullviu.com or use our territory services.',
            'body' => <<<'HTML'
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
HTML,
        ];
    }

    public static function termsContent(): array
    {
        return [
            'lead' => 'These terms govern your access to and use of VIU\'s website and territory services. By using the service, you agree to them.',
            'body' => <<<'HTML'
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
HTML,
        ];
    }
}
