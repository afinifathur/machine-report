<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

class RealMachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $machines = [
            ['code' => 'N-BC.80', 'name' => 'BUBUT CNC 80'],
            ['code' => 'N-BC.78', 'name' => 'BUBUT CNC 78'],
            ['code' => 'N-BC.79', 'name' => 'BUBUT CNC 79'],
            ['code' => 'N-BC.76', 'name' => 'BUBUT CNC 76'],
            ['code' => 'N-BC.77', 'name' => 'BUBUT CNC 77'],
            ['code' => 'N-BC.63', 'name' => 'BUBUT CNC 63'],
            ['code' => 'N-BC.43', 'name' => 'BUBUT CNC 43'],
            ['code' => 'N-BC.61', 'name' => 'BUBUT CNC 61'],
            ['code' => 'N-BC.62', 'name' => 'BUBUT CNC 62'],
            ['code' => 'N-BC.83', 'name' => 'BUBUT CNC 83'],
            ['code' => 'N-BC.64', 'name' => 'BUBUT CNC 64'],

            ['code' => 'O-BC.88', 'name' => 'BUBUT CNC 88'],
            ['code' => 'O-BC.89', 'name' => 'BUBUT CNC 89'],
            ['code' => 'O-BC.90', 'name' => 'BUBUT CNC 90'],
            ['code' => 'O-BC.91', 'name' => 'BUBUT CNC 91'],
            ['code' => 'O-BC.65', 'name' => 'BUBUT CNC 65'],
            ['code' => 'O-BC.56', 'name' => 'BUBUT CNC 56'],
            ['code' => 'O-BC.50', 'name' => 'BUBUT CNC 50'],
            ['code' => 'O-BC.47', 'name' => 'BUBUT CNC 47'],
            ['code' => 'O-BC.34', 'name' => 'BUBUT CNC 34'],

            ['code' => 'R-BC.85', 'name' => 'BUBUT CNC 85'],
            ['code' => 'R-BC.84', 'name' => 'BUBUT CNC 84'],
            ['code' => 'R-BC.82', 'name' => 'BUBUT CNC 82'],
            ['code' => 'R-BC.81', 'name' => 'BUBUT CNC 81'],
            ['code' => 'R-BC.74', 'name' => 'BUBUT CNC 74'],
            ['code' => 'R-BC.73', 'name' => 'BUBUT CNC 73'],
            ['code' => 'R-BC.70', 'name' => 'BUBUT CNC 70'],
            ['code' => 'R-BC.69', 'name' => 'BUBUT CNC 69'],
            ['code' => 'R-BC.68', 'name' => 'BUBUT CNC 68'],
            ['code' => 'R-BC.67', 'name' => 'BUBUT CNC 67'],
            ['code' => 'R-BC.66', 'name' => 'BUBUT CNC 66'],
            ['code' => 'R-BC.55', 'name' => 'BUBUT CNC 55'],
            ['code' => 'R-BC.53', 'name' => 'BUBUT CNC 53'],
            ['code' => 'R-BC.41', 'name' => 'BUBUT CNC 41'],
        ];

        foreach ($machines as $data) {
            $code = $data['code'];
            
            // Determine Production Area based on code pattern
            $productionArea = '';
            if (str_starts_with($code, 'N-BC.')) {
                $productionArea = 'BUBUT FITTING';
            } elseif (str_starts_with($code, 'O-BC.')) {
                $productionArea = 'BUBUT FLANGE BESI';
            } elseif (str_starts_with($code, 'R-BC.')) {
                $productionArea = 'BUBUT CNC FLANGE';
            }

            // Check if machine already exists
            $existing = Machine::where('code', $code)->first();

            $values = [
                'name' => $data['name'],
                'department' => 'Machining',
                'production_area' => $productionArea,
                'category' => 'Lathe',
            ];

            // If it doesn't exist, we merge default operational status and active flags
            if (!$existing) {
                $values = array_merge($values, [
                    'criticality' => 'medium',
                    'operational_status' => 'running',
                    'is_active' => true,
                    'lifecycle_status' => 'ACTIVE',
                ]);
            }

            // Perform updateOrCreate which updates only target values and avoids overwriting passport fields
            Machine::updateOrCreate(
                ['code' => $code],
                $values
            );
        }
    }
}
