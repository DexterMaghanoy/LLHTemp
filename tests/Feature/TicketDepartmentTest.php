<?php

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->department = Department::create(['name' => 'Front Desk', 'slug' => 'front-desk']);
    $this->maintenance = Department::create(['name' => 'Maintenance', 'slug' => 'maintenance']);
});

test('guests are redirected to login when trying to create a ticket', function () {
    $response = $this->post('/tickets', [
        'subject' => 'Test Subject',
        'description' => 'Test description',
        'priority' => 'medium',
        'department_id' => $this->department->id,
    ]);

    $response->assertRedirect('/signin');
});

test('authenticated user can create a ticket with a department assigned', function () {
    $staff = User::factory()->create([
        'name' => 'Staff User',
        'email' => 'staff@hotel.test',
        'role' => 'staff',
    ]);

    $response = $this->actingAs($staff)->post('/tickets', [
        'subject' => 'Room 305 AC not working',
        'description' => 'The AC unit in room 305 is blowing warm air.',
        'priority' => 'high',
        'department_id' => $this->maintenance->id,
    ]);

    $response->assertRedirect(fn ($url) => str_starts_with($url, '/tickets/'));

    $this->assertDatabaseHas('tickets', [
        'subject' => 'Room 305 AC not working',
        'department_id' => $this->maintenance->id,
        'requester_id' => $staff->id,
        'status' => 'open',
    ]);
});

test('creating a ticket without a department fails validation', function () {
    $staff = User::factory()->create([
        'name' => 'Staff User',
        'email' => 'staff@hotel.test',
        'role' => 'staff',
    ]);

    $response = $this->actingAs($staff)->from('/tickets/create')->post('/tickets', [
        'subject' => 'Room 305 AC not working',
        'description' => 'The AC unit in room 305 is blowing warm air.',
        'priority' => 'high',
        'department_id' => '',
    ]);

    $response->assertSessionHasErrors(['department_id' => fn ($errors) => $errors === 'The department_id field is required.']);
    $response->assertRedirect('/tickets/create');

    $this->assertDatabaseMissing('tickets', [
        'subject' => 'Room 305 AC not working',
    ]);
});

test('creating a ticket with a non-existent department fails validation', function () {
    $staff = User::factory()->create([
        'name' => 'Staff User',
        'email' => 'staff@hotel.test',
        'role' => 'staff',
    ]);

    $response = $this->actingAs($staff)->from('/tickets/create')->post('/tickets', [
        'subject' => 'Room 305 AC not working',
        'description' => 'The AC unit in room 305 is blowing warm air.',
        'priority' => 'high',
        'department_id' => 999,
    ]);

    $response->assertSessionHasErrors('department_id');
    $this->assertDatabaseMissing('tickets', [
        'subject' => 'Room 305 AC not working',
    ]);
});
