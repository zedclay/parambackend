<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Module;
use Illuminate\Database\Seeder;

class StudentModuleEnrollmentSeeder extends Seeder
{
    /**
     * Assign modules to students
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $modules = Module::where('is_active', true)->get();

        if ($modules->isEmpty()) {
            $this->command->warn('No active modules found. Please run ModuleSeeder first.');
            return;
        }

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run UserSeeder first.');
            return;
        }

        $enrollmentCount = 0;

        foreach ($students as $student) {
            // Assign 3-6 random modules to each student
            $modulesToAssign = $modules->random(min(rand(3, 6), $modules->count()));
            
            foreach ($modulesToAssign as $module) {
                // Use syncWithoutDetaching to avoid duplicates
                $student->enrolledModules()->syncWithoutDetaching([$module->id => [
                    'enrolled_at' => now(),
                ]]);
                $enrollmentCount++;
            }

            $this->command->info("Assigned {$modulesToAssign->count()} modules to {$student->name}");
        }

        $this->command->info("Total enrollments created: {$enrollmentCount}");
    }
}

