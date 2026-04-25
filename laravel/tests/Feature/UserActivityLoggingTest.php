<?php

namespace Tests\Feature;

use App\Jobs\WriteUserActivityLog;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UserActivityLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserActivityLog::query()->delete();
    }

    public function test_user_created_dispatches_async_logging_job(): void
    {
        Queue::fake();

        app(UserService::class)->createUser([
            'name' => 'Created User',
            'email' => 'created@example.com',
            'password' => 'created-password',
        ]);

        Queue::assertPushed(WriteUserActivityLog::class, function (WriteUserActivityLog $job): bool {
            return $job->event === WriteUserActivityLog::EVENT_USER_CREATED
                && $job->userId > 0;
        });
    }

    public function test_user_updated_dispatches_async_logging_job(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'password' => 'existing-password',
        ]);

        app(UserService::class)->updateUser($user, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => '',
        ]);

        Queue::assertPushed(WriteUserActivityLog::class, function (WriteUserActivityLog $job) use ($user): bool {
            return $job->event === WriteUserActivityLog::EVENT_USER_UPDATED
                && $job->userId === (int) $user->id;
        });
    }

    public function test_user_deleted_dispatches_async_logging_job(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'password' => 'delete-password',
        ]);

        app(UserService::class)->deleteUser($user);

        Queue::assertPushed(WriteUserActivityLog::class, function (WriteUserActivityLog $job) use ($user): bool {
            return $job->event === WriteUserActivityLog::EVENT_USER_DELETED
                && $job->userId === (int) $user->id;
        });
    }

    public function test_failed_validation_does_not_dispatch_logging_job(): void
    {
        Queue::fake();

        try {
            app(UserService::class)->createUser([
                'name' => '',
                'email' => 'invalid-email',
                'password' => 'short',
            ]);
        } catch (\Throwable) {
            // Expected validation exception.
        }

        Queue::assertNotPushed(WriteUserActivityLog::class);
    }

    public function test_logging_job_persists_to_mongodb(): void
    {
        $job = new WriteUserActivityLog(
            userId: 123,
            event: WriteUserActivityLog::EVENT_USER_CREATED,
            data: [
                'actor_id' => 999,
                'attributes' => [
                    'name' => 'Mongo User',
                    'email' => 'mongo@example.com',
                ],
            ],
            idempotencyKey: 'phase5-mongo-persist-key',
        );

        $job->handle();

        $log = UserActivityLog::query()
            ->where('idempotency_key', 'phase5-mongo-persist-key')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(123, (int) $log->user_id);
        $this->assertSame(WriteUserActivityLog::EVENT_USER_CREATED, $log->event);
        $this->assertSame('mongo@example.com', $log->data['attributes']['email']);
    }

    public function test_logging_job_is_idempotent_on_retries(): void
    {
        $job = new WriteUserActivityLog(
            userId: 222,
            event: WriteUserActivityLog::EVENT_USER_UPDATED,
            data: [
                'changed_fields' => ['email'],
                'current_values' => ['email' => 'updated@example.com'],
            ],
            idempotencyKey: 'phase5-idempotency-key',
        );

        $job->handle();
        $job->handle();

        $count = UserActivityLog::query()
            ->where('idempotency_key', 'phase5-idempotency-key')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_sensitive_fields_are_not_logged(): void
    {
        $job = new WriteUserActivityLog(
            userId: 444,
            event: WriteUserActivityLog::EVENT_USER_UPDATED,
            data: [
                'actor_id' => 10,
                'password' => 'secret',
                'token' => 'sensitive-token',
                'changed_fields' => ['email'],
            ],
            idempotencyKey: 'phase5-sensitive-key',
        );

        $job->handle();

        $log = UserActivityLog::query()
            ->where('idempotency_key', 'phase5-sensitive-key')
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayNotHasKey('password', $log->data);
        $this->assertArrayNotHasKey('token', $log->data);
    }

    public function test_transaction_rollback_prevents_log_dispatch(): void
    {
        Queue::fake();

        $uniqueEmail = 'transaction-test-' . uniqid() . '@example.com';

        try {
            DB::transaction(function () use ($uniqueEmail): void {
                User::factory()->create([
                    'name' => 'Transaction Test',
                    'email' => $uniqueEmail,
                    'password' => 'password123',
                ]);

                // Simulate failure after user creation but before commit
                throw new \RuntimeException('Simulated transaction failure');
            });
        } catch (\RuntimeException) {
            // Expected exception
        }

        // Verify user was NOT persisted
        $this->assertDatabaseMissing('users', ['email' => $uniqueEmail]);

        // Verify logging job was NOT dispatched
        Queue::assertNotPushed(WriteUserActivityLog::class);
    }
}
