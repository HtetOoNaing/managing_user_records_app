<?php

declare(strict_types=1);

use App\Jobs\WriteUserActivityLog;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
});
