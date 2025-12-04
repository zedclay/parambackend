<?php

namespace Database\Seeders;

use App\Models\Filiere;
use Illuminate\Database\Seeder;

class FiliereSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Source: INPFP (Institut National Pédagogique de Formation Paramédicale)
     * Website: https://www.inpfp.dz/
     * Data extracted from: https://www.inpfp.dz/spip.php?page=plan
     * 
     * Note: Based on INPFP structure - Formation Initiale programs
     */
    public function run(): void
    {
        $filieres = [
            [
                'name' => [
                    'fr' => 'Soins Infirmiers',
                    'ar' => 'التمريض',
                ],
                'slug' => 'soins-infirmiers',
                'description' => [
                    'fr' => 'Formation en soins infirmiers pour devenir infirmier/infirmière diplômé(e) d\'État de santé publique.',
                    'ar' => 'تدريب في التمريض لتصبح ممرضًا / ممرضة معتمدًا في الصحة العامة.',
                ],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => [
                    'fr' => 'Kinésithérapie',
                    'ar' => 'العلاج الطبيعي',
                ],
                'slug' => 'kinesitherapie',
                'description' => [
                    'fr' => 'Formation en kinésithérapie et rééducation fonctionnelle de santé publique.',
                    'ar' => 'تدريب في العلاج الطبيعي وإعادة التأهيل الوظيفي في الصحة العامة.',
                ],
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => [
                    'fr' => 'Techniques de Laboratoire',
                    'ar' => 'تقنيات المختبر',
                ],
                'slug' => 'techniques-laboratoire',
                'description' => [
                    'fr' => 'Formation aux techniques de laboratoire médical et analyses biologiques de santé publique.',
                    'ar' => 'تدريب على تقنيات المختبر الطبي والتحليلات البيولوجية في الصحة العامة.',
                ],
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => [
                    'fr' => 'Radiologie et Imagerie Médicale',
                    'ar' => 'الأشعة والتصوير الطبي',
                ],
                'slug' => 'radiologie-imagerie-medicale',
                'description' => [
                    'fr' => 'Formation en techniques d\'imagerie médicale et radiologie de santé publique.',
                    'ar' => 'تدريب في تقنيات التصوير الطبي والأشعة في الصحة العامة.',
                ],
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => [
                    'fr' => 'Ergothérapie',
                    'ar' => 'العلاج الوظيفي',
                ],
                'slug' => 'ergotherapie',
                'description' => [
                    'fr' => 'Formation en ergothérapie pour la réadaptation et l\'autonomie des patients.',
                    'ar' => 'تدريب في العلاج الوظيفي لإعادة التأهيل واستقلالية المرضى.',
                ],
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => [
                    'fr' => 'Assistance Médicale',
                    'ar' => 'المساعدة الطبية',
                ],
                'slug' => 'assistance-medicale',
                'description' => [
                    'fr' => 'Formation d\'assistants médicaux de santé publique.',
                    'ar' => 'تدريب مساعدي الصحة العامة.',
                ],
                'order' => 6,
                'is_active' => true,
            ],
            [
                'name' => [
                    'fr' => 'Assistance Sociale',
                    'ar' => 'المساعدة الاجتماعية',
                ],
                'slug' => 'assistance-sociale',
                'description' => [
                    'fr' => 'Formation d\'assistants sociaux de santé publique.',
                    'ar' => 'تدريب مساعدي الصحة العامة الاجتماعيين.',
                ],
                'order' => 7,
                'is_active' => true,
            ],
            [
                'name' => [
                    'fr' => 'Psychomotricité',
                    'ar' => 'العلاج النفسي الحركي',
                ],
                'slug' => 'psychomotricite',
                'description' => [
                    'fr' => 'Formation en psychomotricité de santé publique.',
                    'ar' => 'تدريب في العلاج النفسي الحركي في الصحة العامة.',
                ],
                'order' => 8,
                'is_active' => true,
            ],
            [
                'name' => [
                    'fr' => 'Sage-Femme',
                    'ar' => 'القبالة',
                ],
                'slug' => 'sage-femme',
                'description' => [
                    'fr' => 'Programme de formation des sages-femmes de santé publique.',
                    'ar' => 'برنامج تدريب القابلات في الصحة العامة.',
                ],
                'order' => 9,
                'is_active' => true,
            ],
        ];

        foreach ($filieres as $filiere) {
            Filiere::firstOrCreate(
                ['slug' => $filiere['slug']],
                $filiere
            );
        }
    }
}
