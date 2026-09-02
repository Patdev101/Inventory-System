<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
         * Create the default test users, one per role.
         */
        $defaultUsers = [
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'role' => User::ROLE_MANAGER,
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'role' => User::ROLE_STAFF,
            ],
        ];

        foreach ($defaultUsers as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );
        }

        /*
         * Seed the global measurement units.
         *
         * UnitOfMeasureSeeder uses updateOrCreate(),
         * so existing units will not be duplicated.
         */
        $this->call([
            UnitOfMeasureSeeder::class,
        ]);
    }
}
