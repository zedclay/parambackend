<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class CreateHeroSlidesWithStaticImages extends Seeder
{
    /**
     * Create 11 hero slides with static image paths
     */
    public function run(): void
    {
        // Delete existing inactive slides if they exist (from old seeder)
        HeroSlide::where('is_active', false)->whereNull('image_path')->delete();

        // Create 11 slides with static image paths
        $slides = [];
        for ($i = 1; $i <= 11; $i++) {
            $slides[] = [
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
                'image_path' => "/images/hero/hero-{$i}.jpg", // Static file path
                'image_filename' => "hero-{$i}.jpg",
                'order' => $i - 1,
                'is_active' => true,
                'gradient' => $this->getGradientForIndex($i),
            ];
        }

        foreach ($slides as $slideData) {
            HeroSlide::updateOrCreate(
                ['image_path' => $slideData['image_path']],
                $slideData
            );
        }

        $this->command->info('Created 11 hero slides with static image paths!');
    }

    /**
     * Get gradient class based on slide index
     */
    private function getGradientForIndex(int $index): string
    {
        $gradients = [
            'from-blue-600 to-cyan-500',
            'from-emerald-600 to-teal-500',
            'from-purple-600 to-pink-500',
            'from-orange-600 to-red-500',
            'from-indigo-600 to-purple-500',
            'from-green-600 to-emerald-500',
            'from-rose-600 to-pink-500',
            'from-cyan-600 to-blue-500',
            'from-yellow-600 to-orange-500',
            'from-violet-600 to-purple-500',
            'from-teal-600 to-cyan-500',
        ];

        return $gradients[($index - 1) % count($gradients)];
    }
}
