<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlidesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if slides already exist
        if (HeroSlide::count() > 0) {
            $this->command->info('Hero slides already exist. Skipping seeder.');
            return;
        }

        HeroSlide::create([
            'title' => [
                'fr' => 'Formation Supérieure Paramédicale d\'Excellence',
                'ar' => 'تدريب طبي مساعد متميز',
                'en' => 'Excellence in Paramedical Higher Education',
            ],
            'subtitle' => [
                'fr' => 'Préparez votre avenir professionnel dans le secteur paramédical avec nos formations de qualité',
                'ar' => 'جهز مستقبلك المهني في القطاع الطبي المساعد مع تدريبنا عالي الجودة',
                'en' => 'Prepare your professional future in the paramedical sector with our quality training',
            ],
            'image_path' => null, // Admin should upload images
            'image_filename' => null,
            'order' => 0,
            'is_active' => false, // Set to false so admin needs to add an image first
            'gradient' => 'from-blue-600 to-cyan-500',
        ]);

        HeroSlide::create([
            'title' => [
                'fr' => 'Formations Professionnelles Certifiantes',
                'ar' => 'تدريب مهني معتمد',
                'en' => 'Certified Professional Training',
            ],
            'subtitle' => [
                'fr' => 'Développez vos compétences avec nos programmes adaptés aux besoins du marché',
                'ar' => 'طور مهاراتك مع برامجنا المصممة لتلبية احتياجات السوق',
                'en' => 'Develop your skills with our programs tailored to market needs',
            ],
            'image_path' => null,
            'image_filename' => null,
            'order' => 1,
            'is_active' => false,
            'gradient' => 'from-emerald-600 to-teal-500',
        ]);

        HeroSlide::create([
            'title' => [
                'fr' => 'Excellence Académique et Professionnelle',
                'ar' => 'التميز الأكاديمي والمهني',
                'en' => 'Academic and Professional Excellence',
            ],
            'subtitle' => [
                'fr' => 'Rejoignez une communauté d\'apprentissage dynamique et innovante',
                'ar' => 'انضم إلى مجتمع تعليمي ديناميكي ومبتكر',
                'en' => 'Join a dynamic and innovative learning community',
            ],
            'image_path' => null,
            'image_filename' => null,
            'order' => 2,
            'is_active' => false,
            'gradient' => 'from-purple-600 to-pink-500',
        ]);

        $this->command->info('Hero slides created successfully!');
        $this->command->info('Note: Slides are created as inactive. Please upload images and activate them in the admin panel.');
    }
}
