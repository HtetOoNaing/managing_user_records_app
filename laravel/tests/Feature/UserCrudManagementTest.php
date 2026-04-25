<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserCrudManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_from_filament_resource(): void
    {
        $admin = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->set('data.name', 'Alice Example')
            ->set('data.email', 'alice@example.com')
            ->set('data.password', 'super-secret')
            ->call('create')
            ->assertHasNoFormErrors();

        $createdUser = User::query()->where('email', 'alice@example.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertTrue(Hash::check('super-secret', $createdUser->password));
    }

    public function test_create_user_validation_failure_is_handled(): void
    {
        $admin = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->set('data.name', '')
            ->set('data.email', 'invalid-email')
            ->set('data.password', 'short')
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'email',
                'password' => 'min',
            ]);
    }

    public function test_duplicate_email_is_rejected_during_create(): void
    {
        $admin = User::factory()->create([
            'password' => 'password123',
        ]);
        User::factory()->create([
            'email' => 'duplicate@example.com',
            'password' => 'password123',
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->set('data.name', 'Duplicate User')
            ->set('data.email', 'duplicate@example.com')
            ->set('data.password', 'another-secret')
            ->call('create')
            ->assertHasFormErrors([
                'email' => 'unique',
            ]);
    }

    public function test_admin_can_update_user_and_keep_password_unchanged_when_blank(): void
    {
        $admin = User::factory()->create([
            'password' => 'password123',
        ]);
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => 'existing-password',
        ]);

        $oldPasswordHash = $user->password;

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->set('data.name', 'New Name')
            ->set('data.email', 'new@example.com')
            ->set('data.password', '')
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame($oldPasswordHash, $user->password);
    }

    public function test_admin_can_update_user_password_when_provided(): void
    {
        $admin = User::factory()->create([
            'password' => 'password123',
        ]);
        $user = User::factory()->create([
            'password' => 'original-password',
        ]);

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->set('data.name', $user->name)
            ->set('data.email', $user->email)
            ->set('data.password', 'updated-password')
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertTrue(Hash::check('updated-password', $user->password));
        $this->assertFalse(Hash::check('original-password', $user->password));
    }

    public function test_edit_form_never_prefills_password(): void
    {
        $admin = User::factory()->create([
            'password' => 'password123',
        ]);
        $user = User::factory()->create([
            'password' => 'existing-password',
        ]);

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->assertSet('data.password', null);
    }

    public function test_delete_user_success_via_service_layer(): void
    {
        $user = User::factory()->create([
            'password' => 'to-delete-password',
        ]);

        app(UserService::class)->deleteUser($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_missing_user_edit_route_returns_not_found(): void
    {
        $admin = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->actingAs($admin)
            ->get('/admin/users/999999/edit')
            ->assertNotFound();
    }

    public function test_user_list_does_not_expose_password_hash(): void
    {
        $admin = User::factory()->create([
            'password' => 'password123',
        ]);
        $user = User::factory()->create([
            'name' => 'Visible User',
            'email' => 'visible@example.com',
            'password' => 'visible-password',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Visible User');
        $response->assertSee('visible@example.com');
        $response->assertDontSee($user->password, false);
    }
}
