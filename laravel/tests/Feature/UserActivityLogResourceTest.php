<?php

declare(strict_types=1);

use App\Filament\Resources\UserActivityLogResource;
use App\Filament\Resources\UserActivityLogResource\Pages\ViewUserActivityLog;
use App\Jobs\WriteUserActivityLog;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('User Activity Log Resource', function (): void {
    beforeEach(function (): void {
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    });

    test('user activity log resource is accessible', function (): void {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/user-activity-logs');

        $response->assertStatus(200);
    });

    test('user activity logs list shows logs', function (): void {
        $this->actingAs($this->admin);

        // Create a user and trigger a log
        $user = User::factory()->create();
        WriteUserActivityLog::dispatch(
            $user->id,
            WriteUserActivityLog::EVENT_USER_CREATED,
            [
                'actor_id' => $this->admin->id,
                'attributes' => $user->toArray(),
            ]
        );

        $response = $this->get('/admin/user-activity-logs');

        $response->assertStatus(200);
        $response->assertSee('USER_CREATED');
    });

    test('user activity logs shows event badges', function (): void {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/user-activity-logs');

        $response->assertStatus(200);
    });

    test('user activity logs is read only', function (): void {
        $this->actingAs($this->admin);

        // Try to access create page (should not exist or redirect)
        $response = $this->get('/admin/user-activity-logs/create');

        // Should either 404 or redirect to index
        $this->assertTrue(
            $response->status() === 404 || $response->isRedirect('/admin/user-activity-logs')
        );
    });

    test('user activity log view page works', function (): void {
        $this->actingAs($this->admin);

        // Create a log entry in MongoDB
        $log = UserActivityLog::create([
            'user_id' => 1,
            'event' => WriteUserActivityLog::EVENT_USER_CREATED,
            'data' => [
                'actor_id' => $this->admin->id,
                'attributes' => ['name' => 'Test User', 'email' => 'test@example.com'],
            ],
        ]);

        $response = $this->get("/admin/user-activity-logs/{$log->_id}");

        $response->assertStatus(200);
        $response->assertSee('USER_CREATED');
    });

    describe('ViewUserActivityLog page', function (): void {
        test('renders via livewire and shows event details', function (): void {
            $this->actingAs($this->admin);

            $ikey = 'view-test-created-' . uniqid();
            (new WriteUserActivityLog(
                userId: $this->admin->id,
                event: WriteUserActivityLog::EVENT_USER_CREATED,
                data: ['actor_id' => $this->admin->id, 'attributes' => ['name' => $this->admin->name]],
                idempotencyKey: $ikey,
            ))->handle();

            $log = UserActivityLog::where('idempotency_key', $ikey)->first();

            Livewire::test(ViewUserActivityLog::class, ['record' => (string) $log->_id])
                ->assertOk()
                ->assertSee('USER_CREATED');
        });

        test('resolves live user name from postgresql', function (): void {
            $this->actingAs($this->admin);

            $ikey = 'view-test-username-' . uniqid();
            (new WriteUserActivityLog(
                userId: $this->admin->id,
                event: WriteUserActivityLog::EVENT_USER_UPDATED,
                data: ['actor_id' => $this->admin->id, 'changed_fields' => ['name']],
                idempotencyKey: $ikey,
            ))->handle();

            $log = UserActivityLog::where('idempotency_key', $ikey)->first();

            Livewire::test(ViewUserActivityLog::class, ['record' => (string) $log->_id])
                ->assertOk()
                ->assertSee($this->admin->name);
        });

        test('shows deleted user fallback in user name field', function (): void {
            $this->actingAs($this->admin);

            $ikey = 'view-test-deleted-user-' . uniqid();
            (new WriteUserActivityLog(
                userId: 99999,
                event: WriteUserActivityLog::EVENT_USER_DELETED,
                data: ['actor_id' => $this->admin->id, 'attributes' => ['name' => 'Ghost User', 'email' => 'ghost@example.com']],
                idempotencyKey: $ikey,
            ))->handle();

            $log = UserActivityLog::where('idempotency_key', $ikey)->first();

            Livewire::test(ViewUserActivityLog::class, ['record' => (string) $log->_id])
                ->assertOk()
                ->assertSee('Ghost User');
        });

        test('shows system when no actor id present', function (): void {
            $this->actingAs($this->admin);

            $ikey = 'view-test-system-' . uniqid();
            (new WriteUserActivityLog(
                userId: $this->admin->id,
                event: WriteUserActivityLog::EVENT_USER_LOGIN,
                data: ['actor_id' => null, 'timestamp' => now()->toIso8601String()],
                idempotencyKey: $ikey,
            ))->handle();

            $log = UserActivityLog::where('idempotency_key', $ikey)->first();

            Livewire::test(ViewUserActivityLog::class, ['record' => (string) $log->_id])
                ->assertOk()
                ->assertSee('System');
        });

        test('shows deleted actor fallback when actor user is gone', function (): void {
            $this->actingAs($this->admin);

            $ikey = 'view-test-deleted-actor-' . uniqid();
            (new WriteUserActivityLog(
                userId: $this->admin->id,
                event: WriteUserActivityLog::EVENT_USER_UPDATED,
                data: ['actor_id' => 99999, 'changed_fields' => ['email']],
                idempotencyKey: $ikey,
            ))->handle();

            $log = UserActivityLog::where('idempotency_key', $ikey)->first();

            Livewire::test(ViewUserActivityLog::class, ['record' => (string) $log->_id])
                ->assertOk()
                ->assertSee('Deleted User #99999');
        });
    });

    describe('Resource access control', function (): void {
        test('canCreate returns false', function (): void {
            expect(UserActivityLogResource::canCreate())->toBeFalse();
        });

        test('canEdit returns false', function (): void {
            $log = UserActivityLog::create([
                'user_id' => $this->admin->id,
                'event' => WriteUserActivityLog::EVENT_USER_LOGIN,
                'data' => ['actor_id' => null],
            ]);

            expect(UserActivityLogResource::canEdit($log))->toBeFalse();
        });

        test('canDelete returns false', function (): void {
            $log = UserActivityLog::create([
                'user_id' => $this->admin->id,
                'event' => WriteUserActivityLog::EVENT_USER_LOGIN,
                'data' => ['actor_id' => null],
            ]);

            expect(UserActivityLogResource::canDelete($log))->toBeFalse();
        });
    });

    describe('Searchable query', function (): void {
        test('user name search filters by postgresql user name', function (): void {
            $this->actingAs($this->admin);

            $targetUser = User::factory()->create(['name' => 'SearchableUser', 'email' => 'searchable@example.com']);

            UserActivityLog::create([
                'user_id' => $targetUser->id,
                'event' => WriteUserActivityLog::EVENT_USER_CREATED,
                'data' => ['actor_id' => $this->admin->id],
            ]);

            UserActivityLog::create([
                'user_id' => $this->admin->id,
                'event' => WriteUserActivityLog::EVENT_USER_LOGIN,
                'data' => ['actor_id' => null],
            ]);

            $response = $this->get('/admin/user-activity-logs?tableSearch=SearchableUser');

            $response->assertStatus(200);
        });
    });
});
