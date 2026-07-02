<?php

namespace App\Filament\Pages\Cms;

use App\Filament\Resources\CmsAboutHeroSectionResource;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AboutHub extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'About';

    protected static ?string $title = 'About';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 20;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';

    protected string $view = 'filament.pages.cms-section-hub';

    public function mount(): void
    {
        $this->redirect(CmsAboutHeroSectionResource::getUrl(), navigate: true);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
