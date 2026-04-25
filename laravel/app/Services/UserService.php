<?php

namespace App\Services;

use App\Jobs\WriteUserActivityLog;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

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

        return DB::transaction(function () use ($data): User {
            $user = User::create($data);

            $this->dispatchActivityLogAfterCommit(
                event: WriteUserActivityLog::EVENT_USER_CREATED,
                user: $user,
                data: [
                    'actor_id' => Auth::id(),
                    'attributes' => Arr::except($user->only(['id', 'name', 'email']), ['password']),
                ],
            );

            return $user;
        });
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
            $before = $user->only(['name', 'email']);
            $user->fill($data);
            $user->save();

            $updatedUser = $user->refresh();
            $after = $updatedUser->only(['name', 'email']);
            $changedFields = array_keys(array_diff_assoc($after, $before));

            $this->dispatchActivityLogAfterCommit(
                event: WriteUserActivityLog::EVENT_USER_UPDATED,
                user: $updatedUser,
                data: [
                    'actor_id' => Auth::id(),
                    'changed_fields' => $changedFields,
                    'previous_values' => Arr::only($before, $changedFields),
                    'current_values' => Arr::only($after, $changedFields),
                ],
            );

            return $updatedUser;
        });
    }

    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $snapshot = $user->only(['id', 'name', 'email']);
            $user->delete();

            $this->dispatchActivityLogAfterCommit(
                event: WriteUserActivityLog::EVENT_USER_DELETED,
                user: $snapshot,
                data: [
                    'actor_id' => Auth::id(),
                    'attributes' => $snapshot,
                ],
            );
        });
    }

    /**
     * @param User|array{id:int,name:string,email:string} $user
     */
    private function dispatchActivityLogAfterCommit(string $event, User|array $user, array $data): void
    {
        $userId = $user instanceof User ? (int) $user->id : (int) $user['id'];
        $safeData = Arr::except($data, ['password', 'password_hash', 'token', 'tokens']);

        DB::afterCommit(function () use ($event, $safeData, $userId): void {
            try {
                dispatch(new WriteUserActivityLog(
                    userId: $userId,
                    event: $event,
                    data: $safeData,
                ));
            } catch (Throwable $throwable) {
                Log::warning('Failed to dispatch user activity log job.', [
                    'event' => $event,
                    'user_id' => $userId,
                    'exception' => $throwable->getMessage(),
                ]);
            }
        });
    }
}
