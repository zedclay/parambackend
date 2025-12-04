<?php

namespace Database\Seeders;

use App\Models\Filiere;
use App\Models\Speciality;
use Illuminate\Database\Seeder;

class SpecialitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Source: INPFP (Institut National Pédagogique de Formation Paramédicale)
     * Website: https://www.inpfp.dz/
     * Data extracted from: https://www.inpfp.dz/spip.php?page=plan
     * 
     * All programs are "Licence Professionnalisante" (3-year programs)
     */
    public function run(): void
    {
        $soinsInfirmiers = Filiere::where('slug', 'soins-infirmiers')->first();
        $kinesitherapie = Filiere::where('slug', 'kinesitherapie')->first();
        $techniquesLab = Filiere::where('slug', 'techniques-laboratoire')->first();
        $radiologie = Filiere::where('slug', 'radiologie-imagerie-medicale')->first();
        $ergotherapie = Filiere::where('slug', 'ergotherapie')->first();
        $assistanceMedicale = Filiere::where('slug', 'assistance-medicale')->first();
        $assistanceSociale = Filiere::where('slug', 'assistance-sociale')->first();
        $psychomotricite = Filiere::where('slug', 'psychomotricite')->first();
        $sageFemme = Filiere::where('slug', 'sage-femme')->first();

        $specialities = [
            // Soins Infirmiers
            [
                'filiere_id' => $soinsInfirmiers->id,
                'name' => [
                    'fr' => 'Licence Professionnalisante Infirmier de Santé Publique',
                    'ar' => 'الليسانس المهنية في التمريض للصحة العامة',
                ],
                'slug' => 'licence-infirmier-sante-publique',
                'description' => [
                    'fr' => 'Formation de 3 ans menant à la Licence Professionnalisante Infirmier de Santé Publique. Programme complet incluant anatomie, physiologie, soins infirmiers fondamentaux, pharmacologie, pathologie, éthique professionnelle et stages pratiques.',
                    'ar' => 'تدريب لمدة 3 سنوات يؤدي إلى الليسانس المهنية في التمريض للصحة العامة. برنامج شامل يشمل التشريح والفيزيولوجيا وأساسيات التمريض وعلم الأدوية وعلم الأمراض والأخلاقيات المهنية والتدريب العملي.',
                ],
                'duration' => '3 ans',
                'order' => 1,
                'is_active' => true,
            ],
            
            // Kinésithérapie
            [
                'filiere_id' => $kinesitherapie->id,
                'name' => [
                    'fr' => 'Licence Professionnalisante Kinésithérapeute de Santé Publique',
                    'ar' => 'الليسانس المهنية في العلاج الطبيعي للصحة العامة',
                ],
                'slug' => 'licence-kinesitherapeute-sante-publique',
                'description' => [
                    'fr' => 'Formation de 3 ans en kinésithérapie et rééducation fonctionnelle de santé publique. Programme incluant anatomie fonctionnelle, physiologie du mouvement, techniques de rééducation, massothérapie et stages cliniques.',
                    'ar' => 'تدريب لمدة 3 سنوات في العلاج الطبيعي وإعادة التأهيل الوظيفي في الصحة العامة. برنامج يشمل التشريح الوظيفي وفسيولوجيا الحركة وتقنيات إعادة التأهيل والعلاج بالتدليك والتدريب السريري.',
                ],
                'duration' => '3 ans',
                'order' => 1,
                'is_active' => true,
            ],
            
            // Techniques de Laboratoire
            [
                'filiere_id' => $techniquesLab->id,
                'name' => [
                    'fr' => 'Licence Professionnalisante Laborantins Santé Publique',
                    'ar' => 'الليسانس المهنية في تقنيات المختبر للصحة العامة',
                ],
                'slug' => 'licence-laborantins-sante-publique',
                'description' => [
                    'fr' => 'Formation de 3 ans en techniques de laboratoire médical et analyses biologiques de santé publique. Programme incluant biochimie, hématologie, microbiologie, immunologie, biologie moléculaire et techniques d\'analyses.',
                    'ar' => 'تدريب لمدة 3 سنوات في تقنيات المختبر الطبي والتحليلات البيولوجية في الصحة العامة. برنامج يشمل الكيمياء الحيوية وطب الدم وعلم الأحياء الدقيقة وعلم المناعة والبيولوجيا الجزيئية وتقنيات التحليل.',
                ],
                'duration' => '3 ans',
                'order' => 1,
                'is_active' => true,
            ],
            
            // Radiologie et Imagerie Médicale
            [
                'filiere_id' => $radiologie->id,
                'name' => [
                    'fr' => 'Licence Professionnalisante Manipulateur en Imagerie Médicale',
                    'ar' => 'الليسانس المهنية في تقنيات التصوير الطبي',
                ],
                'slug' => 'licence-manipulateur-imagerie-medicale',
                'description' => [
                    'fr' => 'Formation de 3 ans en techniques d\'imagerie médicale de santé publique. Programme incluant radiologie conventionnelle, scanner, IRM, échographie, médecine nucléaire, radioprotection et techniques d\'imagerie avancées.',
                    'ar' => 'تدريب لمدة 3 سنوات في تقنيات التصوير الطبي في الصحة العامة. برنامج يشمل الأشعة التقليدية والماسح الضوئي والتصوير بالرنين المغناطيسي والموجات فوق الصوتية والطب النووي والحماية من الإشعاع وتقنيات التصوير المتقدمة.',
                ],
                'duration' => '3 ans',
                'order' => 1,
                'is_active' => true,
            ],
            
            // Ergothérapie
            [
                'filiere_id' => $ergotherapie->id,
                'name' => [
                    'fr' => 'Licence Professionnalisante Ergothérapeute de Santé Publique',
                    'ar' => 'الليسانس المهنية في العلاج الوظيفي للصحة العامة',
                ],
                'slug' => 'licence-ergotherapeute-sante-publique',
                'description' => [
                    'fr' => 'Formation de 3 ans en ergothérapie de santé publique. Programme incluant évaluation fonctionnelle, réadaptation, aide à l\'autonomie, adaptation de l\'environnement, activités thérapeutiques et stages pratiques.',
                    'ar' => 'تدريب لمدة 3 سنوات في العلاج الوظيفي في الصحة العامة. برنامج يشمل التقييم الوظيفي وإعادة التأهيل والمساعدة على الاستقلالية وتكييف البيئة والأنشطة العلاجية والتدريب العملي.',
                ],
                'duration' => '3 ans',
                'order' => 1,
                'is_active' => true,
            ],
            
            // Assistance Médicale
            [
                'filiere_id' => $assistanceMedicale->id,
                'name' => [
                    'fr' => 'Licence Professionnalisante Assistant Médical de Santé Publique',
                    'ar' => 'الليسانس المهنية في المساعدة الطبية للصحة العامة',
                ],
                'slug' => 'licence-assistant-medical-sante-publique',
                'description' => [
                    'fr' => 'Formation de 3 ans d\'assistant médical de santé publique. Programme incluant assistance aux soins, techniques de base, gestion administrative, accueil des patients, et coordination des soins.',
                    'ar' => 'تدريب لمدة 3 سنوات لمساعدي الصحة العامة. برنامج يشمل المساعدة في الرعاية والتقنيات الأساسية والإدارة الإدارية والاستقبال وتنسيق الرعاية.',
                ],
                'duration' => '3 ans',
                'order' => 1,
                'is_active' => true,
            ],
            
            // Assistance Sociale
            [
                'filiere_id' => $assistanceSociale->id,
                'name' => [
                    'fr' => 'Licence Professionnalisante Assistant Social de Santé Publique',
                    'ar' => 'الليسانس المهنية في المساعدة الاجتماعية للصحة العامة',
                ],
                'slug' => 'licence-assistant-social-sante-publique',
                'description' => [
                    'fr' => 'Formation de 3 ans d\'assistant social de santé publique. Programme incluant travail social, accompagnement social, médiation, aide à l\'insertion, protection sociale et intervention sociale.',
                    'ar' => 'تدريب لمدة 3 سنوات لمساعدي الصحة العامة الاجتماعيين. برنامج يشمل العمل الاجتماعي والمرافقة الاجتماعية والوساطة والمساعدة على الإدماج والحماية الاجتماعية والتدخل الاجتماعي.',
                ],
                'duration' => '3 ans',
                'order' => 1,
                'is_active' => true,
            ],
            
            // Psychomotricité
            [
                'filiere_id' => $psychomotricite->id,
                'name' => [
                    'fr' => 'Licence Professionnalisante Psychomotricité de Santé Publique',
                    'ar' => 'الليسانس المهنية في العلاج النفسي الحركي للصحة العامة',
                ],
                'slug' => 'licence-psychomotricite-sante-publique',
                'description' => [
                    'fr' => 'Formation de 3 ans en psychomotricité de santé publique. Programme incluant développement psychomoteur, évaluation psychomotrice, rééducation psychomotrice, relaxation, techniques corporelles et stages pratiques.',
                    'ar' => 'تدريب لمدة 3 سنوات في العلاج النفسي الحركي في الصحة العامة. برنامج يشمل النمو النفسي الحركي والتقييم النفسي الحركي وإعادة التأهيل النفسي الحركي والاسترخاء والتقنيات الجسدية والتدريب العملي.',
                ],
                'duration' => '3 ans',
                'order' => 1,
                'is_active' => true,
            ],
            
            // Sage-Femme
            [
                'filiere_id' => $sageFemme->id,
                'name' => [
                    'fr' => 'Programme de Formation des Sages-Femmes de Santé Publique',
                    'ar' => 'برنامج تدريب القابلات في الصحة العامة',
                ],
                'slug' => 'programme-sages-femmes-sante-publique',
                'description' => [
                    'fr' => 'Programme de formation des sages-femmes de santé publique. Formation complète incluant gynécologie, obstétrique, pédiatrie, soins périnataux, suivi de grossesse, accouchement, soins post-partum et stages cliniques.',
                    'ar' => 'برنامج تدريب القابلات في الصحة العامة. تدريب شامل يشمل أمراض النساء والتوليد وطب الأطفال والرعاية حول الولادة ومتابعة الحمل والولادة ورعاية ما بعد الولادة والتدريب السريري.',
                ],
                'duration' => '3 ans',
                'order' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($specialities as $speciality) {
            Speciality::firstOrCreate(
                ['slug' => $speciality['slug']],
                $speciality
            );
        }
    }
}
