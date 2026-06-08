@extends('layout')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom_copy.css') }}"/>

<style>
    /* Premium layout spacing optimizations */
    .premium-card {
        border-radius: 16px !important;
        max-width: 650px;
        margin: 3rem auto !important;
    }
    .premium-banner {
        border-radius: 12px;
        background-color: #f3fdf5;
        border: 1px solid #c9e4d0;
    }
    .premium-banner-empty {
        border-radius: 12px;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    .btn-premium {
        font-size: 1.05rem !important;
        padding: 0.85rem 2.5rem !important;
        border-radius: 10px !important;
        transition: all 0.2s ease-in-out;
    }
    .btn-premium:hover {
        opacity: 0.95;
        transform: translateY(-2px);
    }
</style>

@section('content')
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="card premium-card w-100 shadow-sm border-0">
            <!-- Increased main wrapper padding to a spacious p-5 layout -->
            <div class="card-body p-4 p-md-3.5 text-center">
                
                <!-- Header block with spacious typography -->
                <div class="mb-4">
                    <!-- Elegant Document & Subscription Star Ribbon SVG -->
                    <svg xmlns="http://w3.org" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#2C6B2F" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="mb-3">
                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <circle cx="12" cy="14" r="3"></circle>
                        <path d="m14 16.5 1.5 2.5-2.5-1-2.5 1 1.5-2.5"></path>
                    </svg>
                    <h3 class="fw-bold mb-2" style="color: #1a1a1a; letter-spacing: -0.5px;">Subscription Management</h3>
                    <p class="text-muted mb-0" style="font-size: 1.05rem;">Manage your billing and access premium features.</p>
                </div>

                <hr class="my-4" style="opacity: 0.1;">

                @if(is_object($currentPlan) && isset($currentPlan->name))
                    <!-- Active Plan Banner (Generous Padding) -->
                    <div class="premium-banner p-4 text-start mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success mb-2 px-3 py-2" style="font-size: 0.8rem; border-radius: 6px;">Active Plan</span>
                                <h4 class="mb-1 fw-bold text-dark" style="font-size: 1.25rem;">Plan: {{ $currentPlan->name }}</h4>
                                <p class="mb-0 text-muted" style="font-size: 0.95rem;">{{ $currentPlan->short_description ?? 'Full access to premium features.' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <button onclick="gotoBilling()" class="btn btn-premium fw-bold" style="background-color: #fff; color: #2C6B2F; border: 2px solid #2C6B2F; padding: 12px !important; border-radius: 6px !important;">
                        Change Plan
                    </button>
                @else
                    <!-- No Active Plan Banner (Generous Padding) -->
                    <div class="premium-banner-empty p-4 text-center mb-4">
                        <h4 class="mb-2 fw-bold text-dark" style="font-size: 1.25rem;">No Active Subscription</h4>
                        <p class="mb-0 text-muted" style="font-size: 0.95rem;">Select a plan to unlock all application capabilities.</p>
                    </div>
                    
                    <button onclick="gotoBilling()" class="btn btn-premium fw-bold shadow-sm" style="background-color: #2C6B2F; color: #fff; border: none; padding: 12px !important; border-radius: 6px !important;">
                        Select Plan
                    </button>
                @endif
                
            </div>
        </div>
    </div>
</div>
@include('footer')
@endsection

@section('scripts')
@parent
<script type="text/javascript">
    var billingUrl = "{!! $billingUrl ?? '' !!}";
    function gotoBilling() {
        if (billingUrl) {
            window.open(billingUrl, "_top");
        } else {
            alert("Billing URL is missing. Please contact support.");
        }
    }
</script>
@endsection
