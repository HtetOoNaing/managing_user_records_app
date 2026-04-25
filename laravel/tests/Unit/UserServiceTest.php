<?php

declare(strict_types=1);

use App\Jobs\WriteUserActivityLog;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;

uses()->group('unit', 'service');

describe('UserService', function (): void {
    beforeEach(function (): void {
        $this->service = app(UserService::class);
    });

    describe('createUser', function (): void {
        it('creates a user with valid data', function (): void {
            $user = $this->service->createUser([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'secure-password',
            ]);

            expect($user)->toBeInstanceOf(User::class)
                ->and($user->name)->toBe('Test User')
                ->and($user->email)->toBe('test@example.com')
                ->and($user->id)->toBeGreaterThan(0);
        });

        it('hashes the password on creation', function (): void {
            $user = $this->service->createUser([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'secure-password',
            ]);

            expect(\Hash::check('secure-password', $user->password))->toBeTrue();
        });

        it('throws validation exception for invalid email', function (): void {
            expect(fn () => $this->service->createUser([
                'name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'secure-password',
            ]))->toThrow(ValidationException::class);
        });

        it('throws validation exception for duplicate email', function (): void {
            User::factory()->create(['email' => 'duplicate@example.com']);

            expect(fn () => $this->service->createUser([
                'name' => 'Test User',
                'email' => 'duplicate@example.com',
                'password' => 'secure-password',
            ]))->toThrow(ValidationException::class);
        });

        it('throws validation exception for short password', function (): void {
            expect(fn () => $this->service->createUser([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'short',
            ]))->toThrow(ValidationException::class);
        });
    });

    describe('updateUser', function (): void {
        it('updates user name and email', function (): void {
            $user = User::factory()->create([
                'name' => 'Old Name',
                'email' => 'old@example.com',
            ]);

            $updated = $this->service->updateUser($user, [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'password' => '',
            ]);

            expect($updated->name)->toBe('New Name')
                ->and($updated->email)->toBe('new@example.com');
        });

        it('keeps password unchanged when blank', function (): void {
            $user = User::factory()->create([
                'password' => 'original-password',
            ]);
            $originalHash = $user->password;

            $this->service->updateUser($user, [
                'name' => $user->name,
                'email' => $user->email,
                'password' => '',
            ]);

            $user->refresh();
            expect($user->password)->toBe($originalHash);
        });

        it('updates password when provided', function (): void {
            $user = User::factory()->create([
                'password' => 'original-password',
            ]);

            $this->service->updateUser($user, [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'new-password',
            ]);

            $user->refresh();
            expect(\Hash::check('new-password', $user->password))->toBeTrue();
        });

        it('throws validation exception for duplicate email', function (): void {
            $user1 = User::factory()->create(['email' => 'user1@example.com']);
            User::factory()->create(['email' => 'user2@example.com']);

            expect(fn () => $this->service->updateUser($user1, [
                'name' => 'Updated Name',
                'email' => 'user2@example.com',
                'password' => '',
            ]))->toThrow(ValidationException::class);
        });
    });

    describe('deleteUser', function (): void {
        it('deletes the user', function (): void {
            $user = User::factory()->create();

            $this->service->deleteUser($user);

            expect(User::find($user->id))->toBeNull();
        });
    });

    describe('async logging dispatch', function (): void {
        it('dispatches USER_CREATED job after successful creation', function (): void {
            Queue::fake();

            $this->service->createUser([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'secure-password',
            ]);

            Queue::assertPushed(WriteUserActivityLog::class, function (WriteUserActivityLog $job): bool {
                return $job->event === WriteUserActivityLog::EVENT_USER_CREATED;
            });
        });

        it('dispatches USER_UPDATED job after successful update', function (): void {
            Queue::fake();
            $user = User::factory()->create();

            $this->service->updateUser($user, [
                'name' => 'Updated Name',
                'email' => $user->email,
                'password' => '',
            ]);

            Queue::assertPushed(WriteUserActivityLog::class, function (WriteUserActivityLog $job): bool {
                return $job->event === WriteUserActivityLog::EVENT_USER_UPDATED;
            });
        });

        it('dispatches USER_DELETED job after successful deletion', function (): void {
            Queue::fake();
            $user = User::factory()->create();

            $this->service->deleteUser($user);

            Queue::assertPushed(WriteUserActivityLog::class, function (WriteUserActivityLog $job) use ($user): bool {
                return $job->event === WriteUserActivityLog::EVENT_USER_DELETED
                    && $job->userId === (int) $user->id;
            });
        });
    });
});
