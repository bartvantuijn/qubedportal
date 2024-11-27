<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use Filament\Widgets\ChartWidget;

class CustomerOverview extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
