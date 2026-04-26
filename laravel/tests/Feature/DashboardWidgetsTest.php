<?php

declare(strict_types=1);

use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Jobs\WriteUserActivityLog;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Dashboard Widgets', function (): void {
    beforeEach(function (): void {
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    });

    test('dashboard is accessible by authenticated user', function (): void {
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk();
    });

    test('dashboard redirects unauthenticated users to login', function (): void {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    });

    describe('StatsOverviewWidget', function (): void {
        test('renders with correct total user count', function (): void {
            User::factory()->count(4)->create();

            $this->actingAs($this->admin);

            Livewire::test(StatsOverviewWidget::class)
                ->assertSee('5');
        });

        test('renders with correct new users today count', function (): void {
            $this->actingAs($this->admin);

            Livewire::test(StatsOverviewWidget::class)
                ->assertSee('Total Users')
                ->assertSee('New Users Today')
                ->assertSee('Activities Today');
        });

        test('activities today count reflects mongodb logs', function (): void {
            $this->actingAs($this->admin);

            UserActivityLog::create([
                'user_id'   => $this->admin->id,
                'event'     => WriteUserActivityLog::EVENT_USER_LOGIN,
                'data'      => ['actor_id' => null, 'timestamp' => now()->toIso8601String()],
            ]);

            Livewire::test(StatsOverviewWidget::class)
                ->assertSee('1');
        });

        test('shows zero activity count gracefully when no logs exist', function (): void {
            $this->actingAs($this->admin);

            Livewire::test(StatsOverviewWidget::class)
                ->assertSee('0');
        });
    });

    describe('RecentActivityWidget', function (): void {
        test('renders without errors', function (): void {
            $this->actingAs($this->admin);

            Livewire::test(RecentActivityWidget::class)
                ->assertOk();
        });

        test('renders without error when no logs exist', function (): void {
            $this->actingAs($this->admin);

            Livewire::test(RecentActivityWidget::class)
                ->assertOk();
        });

        test('shows log entries from mongodb', function (): void {
            $this->actingAs($this->admin);

            UserActivityLog::create([
                'user_id' => $this->admin->id,
                'event'   => WriteUserActivityLog::EVENT_USER_LOGIN,
                'data'    => ['actor_id' => null, 'timestamp' => now()->toIso8601String()],
            ]);

            Livewire::test(RecentActivityWidget::class)
                ->assertSee('USER_LOGIN');
        });

        test('resolves user name from postgresql for live users', function (): void {
            $this->actingAs($this->admin);

            UserActivityLog::create([
                'user_id' => $this->admin->id,
                'event'   => WriteUserActivityLog::EVENT_USER_LOGIN,
                'data'    => ['actor_id' => null, 'timestamp' => now()->toIso8601String()],
            ]);

            Livewire::test(RecentActivityWidget::class)
                ->assertSee($this->admin->name);
        });

        test('falls back to log payload name when user is deleted', function (): void {
            $this->actingAs($this->admin);

            UserActivityLog::create([
                'user_id' => 9999,
                'event'   => WriteUserActivityLog::EVENT_USER_DELETED,
                'data'    => [
                    'actor_id'   => $this->admin->id,
                    'attributes' => ['id' => 9999, 'name' => 'Ghost User', 'email' => 'ghost@example.com'],
                ],
            ]);

            Livewire::test(RecentActivityWidget::class)
                ->assertSee('Ghost User');
        });

        test('shows deleted user fallback when no name in payload', function (): void {
            $this->actingAs($this->admin);

            UserActivityLog::create([
                'user_id' => 9999,
                'event'   => WriteUserActivityLog::EVENT_USER_VIEWED,
                'data'    => ['actor_id' => $this->admin->id, 'timestamp' => now()->toIso8601String()],
            ]);

            Livewire::test(RecentActivityWidget::class)
                ->assertSee('Deleted User #9999');
        });
    });

    describe('QuickActionsWidget', function (): void {
        test('renders without errors', function (): void {
            $this->actingAs($this->admin);

            Livewire::test(QuickActionsWidget::class)
                ->assertOk();
        });

        test('contains create new user link', function (): void {
            $this->actingAs($this->admin);

            Livewire::test(QuickActionsWidget::class)
                ->assertSee('Create New User')
                ->assertSee(url('/admin/users/create'));
        });

        test('contains view all users link', function (): void {
            $this->actingAs($this->admin);

            Livewire::test(QuickActionsWidget::class)
                ->assertSee('View All Users')
                ->assertSee(url('/admin/users'));
        });

        test('contains view activity logs link', function (): void {
            $this->actingAs($this->admin);

            Livewire::test(QuickActionsWidget::class)
                ->assertSee('View Activity Logs')
                ->assertSee(url('/admin/user-activity-logs'));
        });
    });
});
