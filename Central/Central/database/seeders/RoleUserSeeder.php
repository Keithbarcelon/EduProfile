<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSchool = School::where('email', 'buksu@eduprofile.test')->first();

        User::updateOrCreate(
            ['email' => 'developer@eduprofile.test'],
            [
                'name' => 'Developer User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'developer',
                'school_id' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@eduprofile.test'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'school_id' => $defaultSchool?->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'faculty@eduprofile.test'],
            [
                'name' => 'Faculty User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'faculty',
                'school_id' => $defaultSchool?->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'student@eduprofile.test'],
            [
                'name' => 'Student User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'student',
                'school_id' => $defaultSchool?->id,
            ]
        );
    }
}
