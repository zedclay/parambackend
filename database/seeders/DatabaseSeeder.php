<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            FiliereSeeder::class,
            SpecialitySeeder::class,
            YearSeeder::class,        // Creates years 1-5 for each speciality
            SemesterSeeder::class,   // Creates S1 and S2 for each year
            GroupSeeder::class,      // Creates groups (G1, G2, etc.) for each year
            ModuleSeeder::class,
            StudentModuleEnrollmentSeeder::class,
            NoteSeeder::class,
            CreatePlaceholderFiles::class,
            AnnouncementSeeder::class,
        ]);
    }
}
