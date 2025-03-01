<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(AdminsTableSeeder::class);
        $this->call(AttendanceStatusTableSeeder::class);
        $this->call(AttendanceRequestStatusTableSeeder::class);
        $this->call(EmployeesTableSeeder::class);
        $this->call(AttendancesTableSeeder::class);
    }
}
