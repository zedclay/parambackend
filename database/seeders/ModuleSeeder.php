<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Speciality;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Source: Based on typical paramedical curriculum modules
     * Note: Replace with actual institute modules after research
     */
    public function run(): void
    {
        $infirmierSantePublique = Speciality::where('slug', 'infirmier-sante-publique')->first();
        
        if ($infirmierSantePublique) {
            $modules = [
                [
                    'specialite_id' => $infirmierSantePublique->id,
                    'code' => 'MOD101',
                    'title' => [
                        'fr' => 'Anatomie et Physiologie Humaine',
                        'ar' => 'التشريح والفيزيولوجيا البشرية',
                    ],
                    'description' => [
                        'fr' => 'Étude de l\'anatomie et de la physiologie du corps humain.',
                        'ar' => 'دراسة تشريح وفسيولوجيا جسم الإنسان.',
                    ],
                    'credits' => 6,
                    'hours' => 90,
                    'order' => 1,
                    'is_active' => true,
                ],
                [
                    'specialite_id' => $infirmierSantePublique->id,
                    'code' => 'MOD102',
                    'title' => [
                        'fr' => 'Soins Infirmiers Fondamentaux',
                        'ar' => 'أساسيات التمريض',
                    ],
                    'description' => [
                        'fr' => 'Apprentissage des techniques de base en soins infirmiers.',
                        'ar' => 'تعلم التقنيات الأساسية في التمريض.',
                    ],
                    'credits' => 8,
                    'hours' => 120,
                    'order' => 2,
                    'is_active' => true,
                ],
                [
                    'specialite_id' => $infirmierSantePublique->id,
                    'code' => 'MOD103',
                    'title' => [
                        'fr' => 'Pharmacologie',
                        'ar' => 'علم الأدوية',
                    ],
                    'description' => [
                        'fr' => 'Étude des médicaments et de leur utilisation en soins infirmiers.',
                        'ar' => 'دراسة الأدوية واستخدامها في التمريض.',
                    ],
                    'credits' => 4,
                    'hours' => 60,
                    'order' => 3,
                    'is_active' => true,
                ],
                [
                    'specialite_id' => $infirmierSantePublique->id,
                    'code' => 'MOD104',
                    'title' => [
                        'fr' => 'Pathologie Générale',
                        'ar' => 'علم الأمراض العام',
                    ],
                    'description' => [
                        'fr' => 'Étude des maladies et de leurs manifestations.',
                        'ar' => 'دراسة الأمراض ومظاهرها.',
                    ],
                    'credits' => 5,
                    'hours' => 75,
                    'order' => 4,
                    'is_active' => true,
                ],
                [
                    'specialite_id' => $infirmierSantePublique->id,
                    'code' => 'MOD105',
                    'title' => [
                        'fr' => 'Éthique et Déontologie Professionnelle',
                        'ar' => 'الأخلاقيات وآداب المهنة',
                    ],
                    'description' => [
                        'fr' => 'Principes éthiques et déontologiques de la profession infirmière.',
                        'ar' => 'المبادئ الأخلاقية وآداب مهنة التمريض.',
                    ],
                    'credits' => 3,
                    'hours' => 45,
                    'order' => 5,
                    'is_active' => true,
                ],
            ];

            foreach ($modules as $module) {
                Module::firstOrCreate(
                    ['code' => $module['code'], 'specialite_id' => $module['specialite_id']],
                    $module
                );
            }
        }
    }
}
