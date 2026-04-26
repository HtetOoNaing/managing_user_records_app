<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.quick-actions-widget';
}
