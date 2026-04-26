<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserActivityLogResource\Pages;

use App\Filament\Resources\UserActivityLogResource;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;

class ViewUserActivityLog extends ViewRecord
{
    protected static string $resource = UserActivityLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Activity Details')
                    ->components([
                        TextEntry::make('event')
                            ->badge()
                            ->color(fn (string $state): array => match ($state) {
                                'USER_CREATED' => Color::Emerald,
                                'USER_UPDATED' => Color::Amber,
                                'USER_DELETED' => Color::Rose,
                                'USER_LOGIN' => Color::Blue,
                                'USER_LOGOUT' => Color::Gray,
                                'USER_VIEWED' => Color::Teal,
                                default => Color::Gray,
                            }),

                        TextEntry::make('user_name')
                            ->getStateUsing(function ($record): string {
                                $user = User::find($record->user_id);
                                return $user?->name ?? "User #{$record->user_id}";
                            }),

                        TextEntry::make('actor_name')
                            ->getStateUsing(function ($record): string {
                                $actorId = $record->data['actor_id'] ?? null;
                                if (!$actorId) {
                                    return 'System';
                                }
                                $actor = User::find($actorId);
                                return $actor?->name ?? "User #{$actorId}";
                            }),

                        TextEntry::make('created_at')
                            ->dateTime('F j, Y g:i:s A'),

                        TextEntry::make('idempotency_key')
                            ->fontFamily('mono')
                            ->copyable(),
                    ])
                    ->columns(2),

                Section::make('Metadata')
                    ->components([
                        TextEntry::make('_id'),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Event Data')
                    ->components([
                        TextEntry::make('data')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                            ->fontFamily('mono')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
