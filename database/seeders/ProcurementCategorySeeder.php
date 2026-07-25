<?php

namespace Database\Seeders;

use App\Models\ProcurementCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProcurementCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Mechanical',
            'Electrical',
            'Hydraulic',
            'Pneumatic',
            'Instrument',
            'Utilities',
            'Machining',
            'Fabrication',
            'Service',
            'Others',
        ];

        foreach ($categories as $cat) {
            ProcurementCategory::firstOrCreate(
                ['slug' => Str::slug($cat)],
                ['name' => $cat, 'is_active' => true]
            );
        }
    }
}
