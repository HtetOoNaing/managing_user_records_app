<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: 'users',
                        column: 'email',
                        ignorable: fn (?object $record): ?object => $record,
                        modifyRuleUsing: static fn (Unique $rule): Unique => $rule->whereNotNull('email'),
                    ),
                TextInput::make('password')
                    ->password()
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->maxLength(255)
                    ->autocomplete('new-password')
                    ->dehydrated(static fn (?string $state): bool => filled($state)),
            ]);
    }
}
