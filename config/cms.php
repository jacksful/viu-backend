<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Page builder
    |--------------------------------------------------------------------------
    |
    | When enabled, public routes render CMS pages from the pages / page_sections
    | tables instead of hardcoded views and singleton section models.
    |
    */

    'use_page_builder' => env('CMS_USE_PAGE_BUILDER', true),

    'reserved_slugs' => [
        'admin',
        'user',
        'stripe',
        'intake',
    ],

    'header_section_links' => [
        ['label' => 'The advantage', 'url' => '/#advantage'],
        ['label' => 'Territory', 'url' => '/#territory'],
        ['label' => 'Exclusivity', 'url' => '/#exclusivity'],
        ['label' => 'Pricing', 'url' => '/#pricing'],
    ],

    'footer_section_links' => [
        ['label' => 'The advantage', 'url' => '/#advantage'],
        ['label' => 'Territory', 'url' => '/#territory'],
        ['label' => 'Exclusivity', 'url' => '/#exclusivity'],
        ['label' => 'Pricing', 'url' => '/#pricing'],
    ],

];
