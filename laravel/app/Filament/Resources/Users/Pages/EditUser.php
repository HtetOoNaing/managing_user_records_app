<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\UserService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->using(function (Model $record): void {
                    if (! $record instanceof User) {
                        throw new InvalidArgumentException('EditUser delete action expects a User record.');
                    }

                    app(UserService::class)->deleteUser($record);
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof User) {
            throw new InvalidArgumentException('EditUser update action expects a User record.');
        }

        return app(UserService::class)->updateUser($record, $data);
    }
}
