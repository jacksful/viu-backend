<?php

namespace App\Filament\Pages\Cms;

use App\Filament\Resources\CmsHeroSectionResource;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class HomePageHub extends Page
{
    protected static ?string $navigationLabel = 'Home Page';

    protected static ?string $title = 'Home Page';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 10;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.cms-section-hub';

    public function mount(): void
    {
        $this->redirect(CmsHeroSectionResource::getUrl(), navigate: true);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
