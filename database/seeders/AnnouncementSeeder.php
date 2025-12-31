<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample announcements for the Institut national de formation superieure Paramédicale de Sidi Bel Abbès
     */
    public function run(): void
    {
        // Get admin user as author
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->warn('No admin user found. Please run UserSeeder first.');
            return;
        }

        $announcements = [
            [
                'title' => [
                    'fr' => 'Bienvenue à l\'Institut national de formation superieure Paramédicale de Sidi Bel Abbès',
                    'ar' => 'مرحباً بكم في المعهد الوطني للتعليم العالي الطبي المساعد بسيدي بلعباس',
                ],
                'content' => [
                    'fr' => 'Nous sommes ravis de vous accueillir à l\'Institut national de formation superieure Paramédicale de Sidi Bel Abbès. Notre institut est dédié à l\'excellence dans la formation paramédicale et à la préparation de professionnels compétents pour le secteur de la santé publique. Nous offrons des programmes de formation de qualité dans diverses spécialités paramédicales.',
                    'ar' => 'يسرنا أن نرحب بكم في المعهد الوطني للتعليم العالي الطبي المساعد بسيدي بلعباس. معهدنا مخصص للتميز في التعليم الطبي المساعد وإعداد محترفين أكفاء لقطاع الصحة العامة. نقدم برامج تدريبية عالية الجودة في مختلف التخصصات الطبية المساعدة.',
                ],
                'author_id' => $admin->id,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(5),
                'target_audience' => 'all',
            ],
            [
                'title' => [
                    'fr' => 'Inscription Ouverte - Année Académique 2024-2025',
                    'ar' => 'التسجيل مفتوح - السنة الأكاديمية 2024-2025',
                ],
                'content' => [
                    'fr' => 'Les inscriptions pour l\'année académique 2024-2025 sont maintenant ouvertes. Les étudiants intéressés peuvent postuler pour nos différents programmes de formation. Veuillez consulter notre site web pour plus d\'informations sur les conditions d\'admission et les dates importantes.',
                    'ar' => 'التسجيل للعام الأكاديمي 2024-2025 مفتوح الآن. يمكن للطلاب المهتمين التقدم لبرامجنا التدريبية المختلفة. يرجى زيارة موقعنا الإلكتروني لمزيد من المعلومات حول شروط القبول والتواريخ المهمة.',
                ],
                'author_id' => $admin->id,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(3),
                'target_audience' => 'all',
            ],
            [
                'title' => [
                    'fr' => 'Nouvelle Infrastructure - Laboratoires Modernisés',
                    'ar' => 'بنية تحتية جديدة - مختبرات حديثة',
                ],
                'content' => [
                    'fr' => 'Nous sommes fiers d\'annoncer la modernisation de nos laboratoires avec de nouveaux équipements de pointe. Ces améliorations permettront aux étudiants d\'acquérir une expérience pratique de qualité dans un environnement moderne et sécurisé.',
                    'ar' => 'نفخر بالإعلان عن تحديث مختبراتنا بأحدث المعدات. ستسمح هذه التحسينات للطلاب باكتساب خبرة عملية عالية الجودة في بيئة حديثة وآمنة.',
                ],
                'author_id' => $admin->id,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(2),
                'target_audience' => 'all',
            ],
            [
                'title' => [
                    'fr' => 'Rappel Important - Dates d\'Examen',
                    'ar' => 'تذكير مهم - مواعيد الامتحانات',
                ],
                'content' => [
                    'fr' => 'Chers étudiants, veuillez noter que les examens du premier semestre commenceront le 15 janvier 2025. Assurez-vous de consulter régulièrement votre emploi du temps et de préparer vos révisions en conséquence. Bonne chance à tous !',
                    'ar' => 'أعزائي الطلاب، يرجى ملاحظة أن امتحانات الفصل الدراسي الأول ستبدأ في 15 يناير 2025. تأكدوا من مراجعة جدولكم الدراسي بانتظام والتحضير للمراجعة وفقاً لذلك. حظاً موفقاً للجميع!',
                ],
                'author_id' => $admin->id,
                'is_published' => true,
                'published_at' => Carbon::now()->subDay(),
                'target_audience' => 'students',
            ],
            [
                'title' => [
                    'fr' => 'Journée Portes Ouvertes - 20 Février 2025',
                    'ar' => 'يوم الأبواب المفتوحة - 20 فبراير 2025',
                ],
                'content' => [
                    'fr' => 'L\'Institut national de formation superieure Paramédicale organise une journée portes ouvertes le 20 février 2025. Venez découvrir nos installations, rencontrer nos enseignants et en apprendre davantage sur nos programmes de formation. L\'événement est ouvert à tous les étudiants intéressés et leurs familles.',
                    'ar' => 'ينظم المعهد الوطني للتعليم العالي الطبي المساعد يوم أبواب مفتوحة في 20 فبراير 2025. تعالوا واكتشفوا مرافقنا، والتقوا بأساتذتنا، وتعرفوا على برامجنا التدريبية. الحدث مفتوح لجميع الطلاب المهتمين وعائلاتهم.',
                ],
                'author_id' => $admin->id,
                'is_published' => true,
                'published_at' => Carbon::now()->addDays(10),
                'target_audience' => 'all',
            ],
            [
                'title' => [
                    'fr' => 'Partenariat avec l\'Hôpital de Sidi Bel Abbès',
                    'ar' => 'شراكة مع مستشفى سيدي بلعباس',
                ],
                'content' => [
                    'fr' => 'Nous sommes heureux d\'annoncer un nouveau partenariat avec l\'Hôpital de Sidi Bel Abbès. Ce partenariat offrira à nos étudiants des opportunités de stages pratiques et d\'expérience clinique dans un environnement hospitalier réel.',
                    'ar' => 'يسرنا الإعلان عن شراكة جديدة مع مستشفى سيدي بلعباس. ستوفر هذه الشراكة لطلابنا فرص التدريب العملي والخبرة السريرية في بيئة مستشفى حقيقية.',
                ],
                'author_id' => $admin->id,
                'is_published' => true,
                'published_at' => Carbon::now()->subHours(6),
                'target_audience' => 'all',
            ],
            [
                'title' => [
                    'fr' => 'Formation Continue - Nouveaux Modules Disponibles',
                    'ar' => 'التدريب المستمر - وحدات جديدة متاحة',
                ],
                'content' => [
                    'fr' => 'De nouveaux modules de formation continue sont maintenant disponibles pour les professionnels de la santé. Ces modules couvrent les dernières avancées en soins paramédicaux et sont conçus pour améliorer les compétences professionnelles.',
                    'ar' => 'وحدات تدريبية جديدة متاحة الآن للمهنيين الصحيين. تغطي هذه الوحدات أحدث التطورات في الرعاية الطبية المساعدة وهي مصممة لتحسين المهارات المهنية.',
                ],
                'author_id' => $admin->id,
                'is_published' => false,
                'published_at' => null,
                'target_audience' => 'all',
            ],
            [
                'title' => [
                    'fr' => 'Résultats des Examens - Session de Décembre 2024',
                    'ar' => 'نتائج الامتحانات - دورة ديسمبر 2024',
                ],
                'content' => [
                    'fr' => 'Les résultats des examens de la session de décembre 2024 sont maintenant disponibles. Les étudiants peuvent consulter leurs résultats sur leur espace étudiant. Félicitations à tous ceux qui ont réussi !',
                    'ar' => 'نتائج امتحانات دورة ديسمبر 2024 متاحة الآن. يمكن للطلاب مراجعة نتائجهم على مساحتهم الطلابية. تهانينا لجميع الناجحين!',
                ],
                'author_id' => $admin->id,
                'is_published' => true,
                'published_at' => Carbon::now()->subHours(2),
                'target_audience' => 'students',
            ],
        ];

        $created = 0;
        foreach ($announcements as $announcementData) {
            // Check if announcement with same French title already exists
            $titleFr = $announcementData['title']['fr'];
            $existing = Announcement::where('title->fr', $titleFr)->first();

            if (!$existing) {
                Announcement::create($announcementData);
                $created++;
            }
        }

        $totalCount = Announcement::count();
        $this->command->info("Announcements seeded: {$created} new announcements created. Total announcements in database: {$totalCount}");
    }
}

