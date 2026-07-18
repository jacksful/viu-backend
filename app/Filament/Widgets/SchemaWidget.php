<?php

namespace App\Filament\Widgets;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;

abstract class SchemaWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament.widgets.schema-widget';

    protected function listCallout(?string $heading = null, ?string $description = null): Callout
    {
        $callout = Callout::make($heading);

        if (filled($description)) {
            $callout->description($description);
        }

        return $callout;
    }
}
