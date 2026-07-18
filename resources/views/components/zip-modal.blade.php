<div class="viu-modal" data-viu-modal data-step="zip-search" hidden>
    <div class="viu-modal__backdrop" data-viu-modal-close></div>
    <div class="viu-modal__panel" role="dialog" aria-modal="true" aria-label="Check ZIP availability">
        <button type="button" class="viu-modal__close" data-viu-modal-close aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>

        <div class="viu-modal__step viu-modal__step--zip viu-modal__pad">
            <div class="viu-modal__head">
                <span class="viu-modal__icon viu-modal__icon--orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
                </span>
                <h3 class="viu-modal__title">Check ZIP availability</h3>
                <p class="viu-modal__sub">Enter your desired ZIP code to check territory availability.</p>
            </div>
            <div class="viu-modal__body">
                <div class="viu-field">
                    <label class="u-visually-hidden" for="modal-zip">ZIP code</label>
                    <input class="viu-modal__zip" id="modal-zip" type="text" inputmode="numeric" maxlength="5" placeholder="Enter ZIP code" data-viu-zip-input data-viu-modal-zip aria-describedby="modal-zip-error" />
                    <p class="viu-field-error" id="modal-zip-error" data-viu-field-error hidden role="alert"></p>
                </div>
                <button type="button" class="viu-btn viu-btn--primary viu-btn--md viu-btn--full" data-viu-modal-check>Check availability</button>
                <p class="viu-modal__error" data-viu-modal-error hidden></p>
            </div>
        </div>

        <div class="viu-modal__step viu-modal__step--available">
            <div class="viu-modal__price-head">
                <div class="viu-modal__avail">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
                    <span>ZIP <span data-viu-modal-zipout></span> is available!</span>
                </div>
                <div class="viu-modal__price"><b data-viu-modal-price></b><span data-viu-modal-price-suffix></span></div>
                <span class="viu-modal__per">Per ZIP code</span>
            </div>
            <div class="viu-modal__plans" data-viu-modal-plans hidden></div>
            <form class="viu-modal__lead" data-viu-modal-lead novalidate>
                <h3>Secure your territory</h3>
                <p class="viu-modal__lead-sub">Complete checkout to activate your exclusive ZIP territory subscription.</p>
                <div class="viu-field">
                    <input class="viu-input" name="name" type="text" placeholder="Full name *" aria-label="Full name" autocomplete="name" required />
                    <p class="viu-field-error" data-viu-field-error hidden role="alert"></p>
                </div>
                <div class="viu-field">
                    <input class="viu-input" name="email" type="email" placeholder="Email address *" aria-label="Email address" autocomplete="email" required />
                    <p class="viu-field-error" data-viu-field-error hidden role="alert"></p>
                </div>
                <div class="viu-field">
                    <input class="viu-input" name="phone" type="tel" placeholder="Phone number (US)" aria-label="Phone number" autocomplete="tel" inputmode="tel" />
                    <p class="viu-field-error" data-viu-field-error hidden role="alert"></p>
                </div>
                <div class="viu-field">
                    <input class="viu-input" name="company" type="text" placeholder="Company / brokerage" aria-label="Company or brokerage" autocomplete="organization" />
                    <p class="viu-field-error" data-viu-field-error hidden role="alert"></p>
                </div>
                <button type="submit" class="viu-btn viu-btn--primary viu-btn--md viu-btn--full">Subscribe &amp; claim territory</button>
                <p class="viu-modal__error" data-viu-modal-lead-error hidden></p>
                <p class="viu-modal__fine">Secure Stripe checkout. Locked-in pricing while your subscription is active. Cancel anytime.</p>
            </form>
        </div>

        <div class="viu-modal__step viu-modal__step--success viu-modal__pad">
            <span class="viu-modal__icon viu-modal__icon--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
            </span>
            <h3 class="viu-modal__title">Redirecting to checkout</h3>
            <p class="viu-modal__msg">Complete secure checkout for ZIP <strong data-viu-modal-zipout></strong> on Stripe.</p>
            <p class="viu-modal__msg viu-modal__msg--sm">Complete payment on Stripe to activate your territory subscription. Check your email for account access after payment.</p>
            <button type="button" class="viu-btn viu-btn--primary viu-btn--md viu-btn--full" data-viu-modal-close>Done</button>
        </div>

        <div class="viu-modal__step viu-modal__step--contact viu-modal__pad">
            <div class="viu-modal__head">
                <span class="viu-modal__icon viu-modal__icon--warn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                </span>
                <h3 class="viu-modal__title" data-viu-modal-contact-title>ZIP unavailable</h3>
                <p class="viu-modal__sub" data-viu-modal-contact-message></p>
            </div>
            <form class="viu-contact" data-viu-waitlist-form data-endpoint="{{ route('waitlists.store') }}" novalidate>
                <div class="viu-contact__grid">
                    <div class="viu-field">
                        <label class="u-visually-hidden" for="modal-contact-name">Full name</label>
                        <input class="viu-input" id="modal-contact-name" name="name" type="text" placeholder="Full name *" autocomplete="name" required />
                        <p class="viu-field-error" data-viu-field-error hidden role="alert"></p>
                    </div>
                    <div class="viu-field">
                        <label class="u-visually-hidden" for="modal-contact-email">Email address</label>
                        <input class="viu-input" id="modal-contact-email" name="email" type="email" placeholder="Email address *" autocomplete="email" required />
                        <p class="viu-field-error" data-viu-field-error hidden role="alert"></p>
                    </div>
                    <div class="viu-field">
                        <label class="u-visually-hidden" for="modal-contact-phone">Phone number</label>
                        <input class="viu-input" id="modal-contact-phone" name="phone" type="tel" placeholder="Phone number (US)" autocomplete="tel" inputmode="tel" />
                        <p class="viu-field-error" data-viu-field-error hidden role="alert"></p>
                    </div>
                    <div class="viu-field">
                        <label class="u-visually-hidden" for="modal-contact-zip">ZIP code of interest</label>
                        <input class="viu-input" id="modal-contact-zip" name="zipCode" type="text" inputmode="numeric" maxlength="5" placeholder="ZIP code of interest" data-viu-zip-input readonly />
                        <p class="viu-field-error" data-viu-field-error hidden role="alert"></p>
                    </div>
                </div>
                <div class="viu-field">
                    <label class="u-visually-hidden" for="modal-contact-message">Message</label>
                    <textarea class="viu-input viu-textarea" id="modal-contact-message" name="message" rows="3" placeholder="Tell us about your market (optional)"></textarea>
                    <p class="viu-field-error" data-viu-field-error hidden role="alert"></p>
                </div>
                <button type="submit" class="viu-btn viu-btn--primary viu-btn--md viu-btn--full">Submit to waitlist</button>
                <p class="viu-modal__error" data-viu-waitlist-error hidden></p>
            </form>
            <button type="button" class="viu-btn viu-btn--ghost viu-btn--md viu-btn--full viu-modal__retry" data-viu-modal-retry-contact>Try another ZIP</button>
        </div>

        <div class="viu-modal__step viu-modal__step--contact-success viu-modal__pad">
            <span class="viu-modal__icon viu-modal__icon--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
            </span>
            <h3 class="viu-modal__title">You're on the waitlist!</h3>
            <p class="viu-modal__msg viu-modal__msg--sm">Thank you. We'll notify you if ZIP <strong data-viu-modal-zipout></strong> becomes available.</p>
            <button type="button" class="viu-btn viu-btn--primary viu-btn--md viu-btn--full" data-viu-modal-close>Done</button>
        </div>

        <div class="viu-modal__step viu-modal__step--unavailable viu-modal__pad">
            <span class="viu-modal__icon viu-modal__icon--warn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            </span>
            <h3 class="viu-modal__title" data-viu-modal-unavailable-title>Outside coverage area</h3>
            <p class="viu-modal__msg viu-modal__msg--sm" data-viu-modal-unavailable-message></p>
            <button type="button" class="viu-btn viu-btn--primary viu-btn--md viu-btn--full" data-viu-modal-retry>Try another ZIP</button>
        </div>
    </div>
</div>
