<?php

namespace Database\Seeders;

use App\Models\Speciality;
use App\Models\Year;
use Illuminate\Database\Seeder;

class YearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates years (1-5) for each speciality based on duration
     * - 3-year programs: Years 1, 2, 3
     * - 5-year programs: Years 1, 2, 3, 4, 5
     */
    public function run(): void
    {
        $specialities = Speciality::all();

        foreach ($specialities as $speciality) {
            // Get duration from speciality (e.g., "3 ans" or "5 ans")
            $duration = $speciality->getDurationInYears() ?? 3; // Default to 3 years

            // Create years based on duration
            for ($yearNumber = 1; $yearNumber <= $duration; $yearNumber++) {
                Year::firstOrCreate(
                    [
                        'speciality_id' => $speciality->id,
                        'year_number' => $yearNumber,
                    ],
                    [
                        'name' => [
                            'fr' => $this->getYearName($yearNumber, 'fr'),
                            'ar' => $this->getYearName($yearNumber, 'ar'),
                        ],
                        'description' => [
                            'fr' => "Année {$yearNumber} de {$speciality->name['fr']}",
                            'ar' => "السنة {$yearNumber} من {$speciality->name['ar']}",
                        ],
                        'order' => $yearNumber,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * Get year name in specified language
     */
    private function getYearName(int $yearNumber, string $locale): string
    {
        $names = [
            'fr' => [
                1 => 'Première Année',
                2 => 'Deuxième Année',
                3 => 'Troisième Année',
                4 => 'Quatrième Année',
                5 => 'Cinquième Année',
            ],
            'ar' => [
                1 => 'السنة الأولى',
                2 => 'السنة الثانية',
                3 => 'السنة الثالثة',
                4 => 'السنة الرابعة',
                5 => 'السنة الخامسة',
            ],
        ];

        return $names[$locale][$yearNumber] ?? "Année {$yearNumber}";
    }
}

