<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Creates the three staff roles from the spec (section 3).
     * Customers deliberately get no role — they're just a plain User.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Event Manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Box Office', 'guard_name' => 'web']);
    }
}
