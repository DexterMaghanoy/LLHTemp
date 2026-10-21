<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HotelTicketSeeder extends Seeder
{
    public function run(): void
    {
        $departments = collect(['Front Desk', 'Housekeeping', 'Maintenance', 'IT Support', 'Food & Beverage'])
            ->map(fn ($name) => Department::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]));

        $admin = User::firstOrCreate(['email' => 'admin@hotel.test'], [
            'name' => 'Hotel Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department_id' => $departments->first()->id,
        ]);

        // Each department has its own login account. The account name is the
        // department name so tickets show the responsible department clearly.
        $departmentAccounts = $departments->mapWithKeys(function ($department) {
            $email = Str::slug($department->name, '.').'@hotel.test';

            $account = User::firstOrCreate(['email' => $email], [
                'name' => $department->name,
                'password' => Hash::make('password'),
                'role' => 'staff',
                'department_id' => $department->id,
            ]);

            return [$department->name => $account];
        });

        $guest = User::firstOrCreate(['email' => 'guest@hotel.test'], [
            'name' => 'Guest User',
            'password' => Hash::make('password'),
            'role' => 'guest',
        ]);

        $samples = [
            [
                'subject' => 'Air conditioning not working in room 305',
                'description' => 'The AC unit in room 305 is blowing warm air. Please fix urgently.',
                'priority' => 'high',
                'category' => 'Maintenance',
                'department' => 'Maintenance',
                'requester' => 'Front Desk',
            ],
            [
                'subject' => 'WiFi password request',
                'description' => 'Need the WiFi password for the conference room.',
                'priority' => 'low',
                'category' => 'IT',
                'department' => 'IT Support',
                'requester' => 'Front Desk',
            ],
            [
                'subject' => 'Extra towels needed',
                'description' => 'Please send 2 extra bath towels to room 112.',
                'priority' => 'low',
                'category' => 'Housekeeping',
                'department' => 'Housekeeping',
                'requester' => 'Front Desk',
            ],
            [
                'subject' => 'Late checkout request',
                'description' => 'Requesting late checkout until 2 PM for room 208.',
                'priority' => 'medium',
                'category' => 'Front Desk',
                'department' => 'Front Desk',
                'requester' => 'Guest User',
            ],
        ];

        foreach ($samples as $sample) {
            $department = $departments->firstWhere('name', $sample['department']);
            $assignee = $departmentAccounts->get($sample['department']);
            $requester = $sample['requester'] === 'Guest User'
                ? $guest
                : $departmentAccounts->get($sample['requester']);

            Ticket::firstOrCreate(['subject' => $sample['subject']], [
                'ticket_number' => 'TKT-'.strtoupper(Str::random(8)),
                'description' => $sample['description'],
                'status' => 'open',
                'priority' => $sample['priority'],
                'category' => $sample['category'],
                'department_id' => $department?->id,
                'requester_id' => $requester->id,
                'assignee_id' => $assignee?->id,
            ]);
        }
    }
}
