<?php

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->department = Department::create(['name' => 'Front Desk', 'slug' => 'front-desk']);
});

test('public signup route is not available', function () {
    $this->get('/signup')->assertNotFound();
    $this->post('/signup')->assertNotFound();
});

test('guests are redirected to login for user management', function () {
    $this->get('/users')->assertRedirect('/signin');
});

test('non-admin users cannot manage users', function () {
    $staff = User::factory()->create([
        'email' => 'staff@hotel.test',
        'password' => bcrypt('password'),
        'role' => 'staff',
    ]);

    $this->actingAs($staff)->get('/users')->assertForbidden();
});

test('admins can create users', function () {
    $admin = User::factory()->create([
        'email' => 'admin@hotel.test',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post('/users', [
            'name' => 'New Staff',
            'email' => 'newstaff@hotel.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
            'department_id' => $this->department->id,
        ])
        ->assertRedirect('/users');

    $this->assertDatabaseHas('users', [
        'email' => 'newstaff@hotel.test',
        'role' => 'staff',
        'department_id' => $this->department->id,
    ]);
});

test('admins can update and delete users', function () {
    $admin = User::factory()->create([
        'email' => 'admin@hotel.test',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $user = User::factory()->create([
        'email' => 'guest@hotel.test',
        'password' => bcrypt('password'),
        'role' => 'guest',
    ]);

    $this->actingAs($admin)
        ->put("/users/{$user->id}", [
            'name' => 'Updated Guest',
            'email' => 'guest@hotel.test',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'guest',
            'department_id' => '',
        ])
        ->assertRedirect('/users');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Guest',
    ]);

    $this->actingAs($admin)
        ->delete("/users/{$user->id}")
        ->assertRedirect('/users');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
