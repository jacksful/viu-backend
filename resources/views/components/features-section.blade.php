<section id="solutions" class="bg-light py-5 px-3" style="background: #F7F7F7 !important;">
    <div class="container">
        <div class="text-center mb-5" style="max-width: 60%; margin: 0 auto;">
            <div class="small text-primary fw-medium mb-2 f-title rounded-pill">Platform Features</div>
            <h2 class="display-6 fw-bold text-dark mb-3">Everything you need to stay ahead in the real estate market.</h2>
            <p class="lead text-muted mx-auto" style="max-width: 42rem;">
                Explore tools that help finance teams move money securely, stay compliant, and scale operations globally.
            </p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <x-feature-card 
                    icon="{{ asset('image/icon.svg') }}" 
                    title="ZIP-Based Data" 
                    description="Monthly datasets analyzing property likelihood to enter the market." 
                />
            </div>
            <div class="col-md-4">
                <x-feature-card 
                    icon="{{ asset('image/icon2.svg') }}" 
                    title="Real Predictions" 
                    description="Real-time insights and trend visualization for your coverage areas." 
                />
            </div>
            <div class="col-md-4">
                <x-feature-card 
                    icon="{{ asset('image/icon3.svg') }}" 
                    title="Easy CSV Exports" 
                    description="Download and integrate data seamlessly with your existing tools." 
                />
            </div>
            <div class="col-md-4">
                <x-feature-card 
                    icon="{{ asset('image/icon4.svg') }}" 
                    title="Reliable Admin Controls" 
                    description="Manage access, monitor usage, and maintain data integrity." 
                />
            </div>
            <div class="col-md-4">
                <x-feature-card 
                    icon="{{ asset('image/icon5.svg') }}" 
                    title="Proactive Alerts" 
                    description="Get notified when high-probability listings are identified." 
                />
            </div>
            <div class="col-md-4">
                <x-feature-card 
                    icon="{{ asset('image/icon6.svg') }}"   
                    title="Invite-Only Access" 
                    description="Exclusive platform with curated client dashboard experience." 
                />
            </div>
        </div>
    </div>
</section>

<style>
    .f-title{
        color: #5C5C5C !important;
        background: #FFFFFF;
        width: max-content;
        padding: 2px 20px;
        border-radius: 6px;
        margin: 0 auto;
    }
</style>