<div class="viu-modal" data-viu-contact-modal data-step="form" hidden>
    <div class="viu-modal__backdrop" data-viu-modal-close></div>
    <div class="viu-modal__panel" role="dialog" aria-modal="true" aria-label="Contact a specialist">
        <button type="button" class="viu-modal__close" data-viu-modal-close aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>

        <div class="viu-modal__step viu-modal__step--form viu-modal__pad">
            <div class="viu-modal__head">
                <span class="viu-modal__icon viu-modal__icon--orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                </span>
                <h3 class="viu-modal__title">Contact a specialist</h3>
                <p class="viu-modal__sub">Tell us about your market and our team will be in touch shortly.</p>
            </div>
            <form class="viu-contact" data-viu-contact-form>
                <div class="viu-contact__grid">
                    <label class="u-visually-hidden" for="c-name">Full name</label>
                    <input class="viu-input" id="c-name" name="name" type="text" placeholder="Full name *" required />
                    <label class="u-visually-hidden" for="c-email">Email address</label>
                    <input class="viu-input" id="c-email" name="email" type="email" placeholder="Email address *" required />
                    <label class="u-visually-hidden" for="c-phone">Phone number</label>
                    <input class="viu-input" id="c-phone" name="phone" type="tel" placeholder="Phone number" />
                    <label class="u-visually-hidden" for="c-zip">ZIP code of interest</label>
                    <input class="viu-input" id="c-zip" name="zipCode" type="text" inputmode="numeric" maxlength="5" placeholder="ZIP code of interest" data-viu-zip-input />
                </div>
                <label class="u-visually-hidden" for="c-message">Message</label>
                <textarea class="viu-input viu-textarea" id="c-message" name="message" rows="4" placeholder="How can we help you?"></textarea>
                <button class="viu-btn viu-btn--primary viu-btn--md viu-btn--full" type="submit">Send Message</button>
                <p class="viu-modal__error" data-viu-contact-error hidden></p>
            </form>
        </div>

        <div class="viu-modal__step viu-modal__step--success viu-modal__pad">
            <span class="viu-modal__icon viu-modal__icon--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
            </span>
            <h3 class="viu-modal__title">Message sent!</h3>
            <p class="viu-modal__msg viu-modal__msg--sm">Thank you, a territory specialist will contact you shortly.</p>
            <button type="button" class="viu-btn viu-btn--primary viu-btn--md viu-btn--full" data-viu-modal-close>Done</button>
        </div>
    </div>
</div>
