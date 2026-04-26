<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Activity')
            ->query(UserActivityLog::query()->latest('created_at'))
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10])
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): array => match ($state) {
                        'USER_CREATED' => Color::Emerald,
                        'USER_UPDATED' => Color::Amber,
                        'USER_DELETED' => Color::Rose,
                        'USER_LOGIN'   => Color::Blue,
                        'USER_LOGOUT'  => Color::Gray,
                        'USER_VIEWED'  => Color::Teal,
                        default        => Color::Slate,
                    }),

                Tables\Columns\TextColumn::make('user_name')
                    ->label('User')
                    ->getStateUsing(function (UserActivityLog $record): string {
                        $user = User::find($record->user_id);
                        if ($user) {
                            return $user->name;
                        }
                        $data = $record->data ?? [];
                        return $data['user_name']
                            ?? $data['attributes']['name']
                            ?? $data['current_values']['name']
                            ?? $data['previous_values']['name']
                            ?? "Deleted User #{$record->user_id}";
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->formatStateUsing(fn ($state): string => $state instanceof Carbon
                        ? $state->diffForHumans()
                        : Carbon::parse($state)->diffForHumans()
                    )
                    ->tooltip(fn (UserActivityLog $record): string => $record->created_at instanceof Carbon
                        ? $record->created_at->format('M d, Y H:i:s')
                        : Carbon::parse($record->created_at)->format('M d, Y H:i:s')
                    )
                    ->sortable(),
            ])
            ->recordUrl(fn (UserActivityLog $record): string => url('/admin/user-activity-logs/' . $record->getKey()))
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading('No activity yet')
            ->emptyStateDescription('User activities will appear here once actions are performed.');
    }
}
