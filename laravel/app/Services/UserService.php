<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * @throws ValidationException
     */
    public function createUser(array $input): User
    {
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ])->validate();

        return DB::transaction(fn () => User::create($data));
    }

    /**
     * @throws ValidationException
     */
    public function updateUser(User $user, array $input): User
    {
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ])->validate();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return DB::transaction(function () use ($user, $data): User {
            $user->fill($data);
            $user->save();

            return $user->refresh();
        });
    }

    public function deleteUser(User $user): void
    {
        DB::transaction(static fn () => $user->delete());
    }
}
