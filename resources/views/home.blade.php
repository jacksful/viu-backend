@extends('layouts.app')

@section('title', config('app.name', 'VIU') . ' | Own the market before they sell')

@section('body_class', 'home-page')

@section('content')
    @include('components.hero', ['hero' => $hero])
    @include('components.stats-bar')
    @include('components.feature-be-first', ['section' => $strategicWindow])
    @include('components.feature-one-zip', ['section' => $territoryZip])
    @include('components.recognition-section', ['section' => $recognition])
    <x-pricing-section :zipcodes="$zipcodes" :section="$pricing" />
    @include('components.faq-section', ['section' => $qa])
    @include('components.cta-banner')
@endsection
