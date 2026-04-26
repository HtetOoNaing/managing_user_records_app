<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserActivityLogResource\Pages\ListUserActivityLogs;
use App\Filament\Resources\UserActivityLogResource\Pages\ViewUserActivityLog;
use App\Models\User;
use App\Models\UserActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;

class UserActivityLogResource extends Resource
{
    protected static ?string $model = UserActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'User Activities';

    protected static ?string $modelLabel = 'User Activity';

    protected static ?string $pluralModelLabel = 'User Activities';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('_id')
                    ->label('ID')
                    ->formatStateUsing(fn (string $state): string => substr($state, -8))
                    ->sortable(),

                Tables\Columns\TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): array => match ($state) {
                        'USER_CREATED' => Color::Emerald,
                        'USER_UPDATED' => Color::Amber,
                        'USER_DELETED' => Color::Rose,
                        'USER_LOGIN' => Color::Blue,
                        'USER_LOGOUT' => Color::Gray,
                        'USER_VIEWED' => Color::Teal,
                        default => Color::Slate,
                    })
                    ->sortable()
                    ->searchable(),

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
                    })
                    ->searchable(query: function ($query, $search) {
                        $userIds = User::where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->pluck('id');
                        return $query->whereIn('user_id', $userIds);
                    }),

                Tables\Columns\TextColumn::make('actor_name')
                    ->label('Actor')
                    ->getStateUsing(function (UserActivityLog $record): string {
                        $actorId = $record->data['actor_id'] ?? null;
                        if (!$actorId) {
                            return 'System';
                        }
                        $actor = User::find($actorId);
                        return $actor?->name ?? "User #{$actorId}";
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'USER_CREATED' => 'User Created',
                        'USER_UPDATED' => 'User Updated',
                        'USER_DELETED' => 'User Deleted',
                        'USER_LOGIN' => 'User Login',
                        'USER_LOGOUT' => 'User Logout',
                        'USER_VIEWED' => 'User Viewed',
                    ])
                    ->label('Event Type'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q) => $q->where('created_at', '>=', new \DateTime($data['created_from'])))
                            ->when($data['created_until'], fn ($q) => $q->where('created_at', '<=', new \DateTime($data['created_until'] . ' 23:59:59')));
                    })
                    ->label('Date Range'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->icon(Heroicon::OutlinedEye),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No activity logs found')
            ->emptyStateDescription('User activities will appear here when users are created, updated, deleted, viewed, or log in/out.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserActivityLogs::route('/'),
            'view' => ViewUserActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}
