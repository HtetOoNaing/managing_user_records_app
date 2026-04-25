<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\UserService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $user */
        $user = app(UserService::class)->createUser($data);

        return $user;
    }
}
