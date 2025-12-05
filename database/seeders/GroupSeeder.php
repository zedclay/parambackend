<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Speciality;
use App\Models\Year;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates groups (G1, G2, G3, etc.) for each year in each speciality
     * Default: Creates G1 and G2 for each year
     */
    public function run(): void
    {
        $specialities = Speciality::all();

        foreach ($specialities as $speciality) {
            $years = $speciality->years;

            foreach ($years as $year) {
                // Create default groups: G1 and G2
                // You can modify this to create more groups
                $numberOfGroups = 2; // Change this to create more groups

                for ($groupNumber = 1; $groupNumber <= $numberOfGroups; $groupNumber++) {
                    $groupName = "G{$groupNumber}";
                    // Create unique code: speciality_id-year_number-groupName
                    $groupCode = "SPEC{$speciality->id}-Y{$year->year_number}-{$groupName}";

                    Group::firstOrCreate(
                        [
                            'speciality_id' => $speciality->id,
                            'year_id' => $year->id,
                            'name' => $groupName,
                        ],
                        [
                            'code' => $groupCode,
                            'capacity' => 30, // Default capacity, adjust as needed
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}

