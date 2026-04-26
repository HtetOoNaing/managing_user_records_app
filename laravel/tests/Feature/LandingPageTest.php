<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Landing Page', function (): void {
    test('landing page loads successfully', function (): void {
        $response = $this->get('/');

        $response->assertStatus(200);
    });

    test('landing page shows app name', function (): void {
        $response = $this->get('/');

        $response->assertSee(config('app.name'));
    });

    test('landing page shows admin login button', function (): void {
        $response = $this->get('/');

        $response->assertSee('Admin Login');
    });

    test('admin login button links to correct url', function (): void {
        $response = $this->get('/');

        $response->assertSee('href="/admin/login"', false);
    });

    test('landing page does not show broken login routes', function (): void {
        $response = $this->get('/');

        $response->assertDontSee('route(\'login\')', false);
        $response->assertDontSee('route(\'register\')', false);
    });

    test('landing page shows user management description', function (): void {
        $response = $this->get('/');

        $response->assertSee('User Management System');
    });
});
