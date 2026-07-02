@extends('layouts.app')

@section('title', $page->seo($preview ?? false)->metaTitle())
@section('body_class', $page->resolvedBodyClass())

@section('meta')
    @include('components.page-meta', ['page' => $page, 'preview' => $preview ?? false])
@endsection

@push('styles')
    @if ($preview ?? false)
        <style>
            .cms-preview-banner {
                position: fixed;
                bottom: 1rem;
                right: 1rem;
                z-index: 9999;
                background: #111827;
                color: #fff;
                padding: 0.75rem 1rem;
                border-radius: 0.5rem;
                font-size: 0.875rem;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            }
        </style>
    @endif
@endpush

@section('content')
    @foreach ($sections as $section)
        @include($section['view'], $section['data'])
    @endforeach

    @if ($preview ?? false)
        <div class="cms-preview-banner" role="status">Preview mode — draft content may be visible.</div>
    @endif
@endsection
