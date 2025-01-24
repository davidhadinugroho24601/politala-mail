<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TimelineWidget extends Widget
{
    protected static string $view = 'filament.widgets.timeline-widget';

    public function getData(): array
    {
        return [
            'events' => [
                ['year' => '1280', 'color' => 'blue', 'description' => 'Event 1'],
                ['year' => '1649', 'color' => 'red', 'description' => 'Event 2'],
                ['year' => '1831', 'color' => 'green', 'description' => 'Event 3'],
                ['year' => '1992', 'color' => 'blue', 'description' => 'Event 4'],
            ],
        ];
    }
}
