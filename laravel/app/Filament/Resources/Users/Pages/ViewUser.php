<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Jobs\WriteUserActivityLog;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->components([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $loadedRecord = $this->getRecord();
        $viewer = auth()->user();

        dispatch(new WriteUserActivityLog(
            userId: (int) $loadedRecord->id,
            event: WriteUserActivityLog::EVENT_USER_VIEWED,
            data: [
                'actor_id' => $viewer?->id,
                'user_name' => $loadedRecord->name,
                'timestamp' => now()->toIso8601String(),
            ],
        ));
    }
}
