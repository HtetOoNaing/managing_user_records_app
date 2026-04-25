<?php

declare(strict_types=1);

use App\Jobs\WriteUserActivityLog;
use App\Models\UserActivityLog;

uses()->group('unit', 'job');

describe('WriteUserActivityLog Job', function (): void {
    beforeEach(function (): void {
        UserActivityLog::query()->delete();
    });

    it('persists log to mongodb', function (): void {
        $job = new WriteUserActivityLog(
            userId: 123,
            event: WriteUserActivityLog::EVENT_USER_CREATED,
            data: ['actor_id' => 1, 'attributes' => ['name' => 'Test']],
            idempotencyKey: 'test-persist-key',
        );

        $job->handle();

        $log = UserActivityLog::query()->where('idempotency_key', 'test-persist-key')->first();
        expect($log)->not->toBeNull()
            ->and((int) $log->user_id)->toBe(123)
            ->and($log->event)->toBe(WriteUserActivityLog::EVENT_USER_CREATED)
            ->and($log->data['attributes']['name'])->toBe('Test');
    });

    it('is idempotent on retry', function (): void {
        $job = new WriteUserActivityLog(
            userId: 456,
            event: WriteUserActivityLog::EVENT_USER_UPDATED,
            data: ['changed_fields' => ['email']],
            idempotencyKey: 'test-idempotent-key',
        );

        $job->handle();
        $job->handle();
        $job->handle();

        $count = UserActivityLog::query()->where('idempotency_key', 'test-idempotent-key')->count();
        expect($count)->toBe(1);
    });

    it('generates idempotency key when not provided', function (): void {
        $job = new WriteUserActivityLog(
            userId: 789,
            event: WriteUserActivityLog::EVENT_USER_DELETED,
            data: ['actor_id' => 1],
        );

        expect($job->idempotencyKey)->not->toBeNull()
            ->and(strlen($job->idempotencyKey))->toBeGreaterThan(0);
    });

    it('has retry configuration', function (): void {
        $job = new WriteUserActivityLog(
            userId: 999,
            event: WriteUserActivityLog::EVENT_USER_CREATED,
            data: [],
        );

        expect($job->tries)->toBe(3)
            ->and($job->backoff)->toBe([5, 15, 30]);
    });

    it('strips sensitive fields from data', function (): void {
        $job = new WriteUserActivityLog(
            userId: 111,
            event: WriteUserActivityLog::EVENT_USER_UPDATED,
            data: [
                'actor_id' => 1,
                'password' => 'secret123',
                'password_hash' => 'hash123',
                'token' => 'token123',
                'tokens' => ['access' => 'abc'],
                'changed_fields' => ['email'],
            ],
            idempotencyKey: 'test-sanitize-key',
        );

        $job->handle();

        $log = UserActivityLog::query()->where('idempotency_key', 'test-sanitize-key')->first();
        expect($log->data)->not->toHaveKey('password')
            ->and($log->data)->not->toHaveKey('password_hash')
            ->and($log->data)->not->toHaveKey('token')
            ->and($log->data)->not->toHaveKey('tokens')
            ->and($log->data)->toHaveKey('changed_fields');
    });

    it('stores all event types correctly', function (string $event): void {
        $key = "test-event-" . uniqid();
        $job = new WriteUserActivityLog(
            userId: 1,
            event: $event,
            data: ['test' => true],
            idempotencyKey: $key,
        );

        $job->handle();

        $log = UserActivityLog::query()->where('idempotency_key', $key)->first();
        expect($log)->not->toBeNull()
            ->and($log->event)->toBe($event);
    })->with([
        WriteUserActivityLog::EVENT_USER_CREATED,
        WriteUserActivityLog::EVENT_USER_UPDATED,
        WriteUserActivityLog::EVENT_USER_DELETED,
        WriteUserActivityLog::EVENT_USER_LOGIN,
    ]);
});
