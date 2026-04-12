<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSchool = School::updateOrCreate(
            ['email' => 'buksu@eduprofile.test'],
            [
                'name' => 'Bukidnon State University / BukSU',
                'school_type' => 'University',
                'address' => 'Fortich Street, Malaybalay City, Bukidnon',
                'email' => 'buksu@eduprofile.test',
                'contact_number' => '+63-88-813-5661',
            ]
        );

        $legacySchoolIds = School::where('id', '!=', $defaultSchool->id)
            ->whereIn('email', ['maincampus@eduprofile.test', 'tech@eduprofile.test'])
            ->pluck('id');

        if ($legacySchoolIds->isNotEmpty()) {
            User::whereIn('school_id', $legacySchoolIds)->update(['school_id' => $defaultSchool->id]);
            Student::whereIn('school_id', $legacySchoolIds)->update(['school_id' => $defaultSchool->id]);
            School::whereIn('id', $legacySchoolIds)->delete();
        }
    }
}
