<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialization;
use Illuminate\Support\Str;

class SpecializationSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Ophthalmology',
                'slug' => 'eye',
                'icon' => 'eye-outline',
                'description' => 'Eye and vision care, including corneal surgery and refractive procedures',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Dermatology',
                'slug' => 'skin',
                'icon' => 'body-outline',
                'description' => 'Skin care, treatments, and dermatological procedures',
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Cardiology',
                'slug' => 'cardiology',
                'icon' => 'heart-outline',
                'description' => 'Heart and cardiovascular system care',
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Orthopedics',
                'slug' => 'orthopedics',
                'icon' => 'medical-outline',
                'description' => 'Bone, joint, and musculoskeletal system treatments',
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Neurology',
                'slug' => 'neurology',
                'icon' => 'brain-outline',
                'description' => 'Nervous system and brain health care',
                'display_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Pediatrics',
                'slug' => 'pediatrics',
                'icon' => 'people-outline',
                'description' => 'Medical care for infants, children, and adolescents',
                'display_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Gynecology',
                'slug' => 'gynecology',
                'icon' => 'female-outline',
                'description' => 'Women\'s reproductive health and care',
                'display_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Urology',
                'slug' => 'urology',
                'icon' => 'medical-outline',
                'description' => 'Urinary tract and male reproductive system care',
                'display_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Gastroenterology',
                'slug' => 'gastroenterology',
                'icon' => 'restaurant-outline',
                'description' => 'Digestive system and gastrointestinal health',
                'display_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Endocrinology',
                'slug' => 'endocrinology',
                'icon' => 'pulse-outline',
                'description' => 'Hormone disorders and metabolic conditions',
                'display_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($data as $item) {
            Specialization::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}
