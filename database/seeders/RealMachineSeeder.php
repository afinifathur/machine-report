<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MasterDepartment;
use App\Models\MasterMachineCategory;
use App\Models\MasterProductionArea;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealMachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $machines = [
            // Area: BAHAN BAKU
            ['name' => 'PRESS 1', 'code' => 'A-PS.01', 'area' => 'BAHAN BAKU'],
            ['name' => 'PRESS 2', 'code' => 'A-PS.02', 'area' => 'BAHAN BAKU'],
            ['name' => 'PRESS 3', 'code' => 'A-PS.03', 'area' => 'BAHAN BAKU'],
            ['name' => 'GUNTING POTONG 1', 'code' => 'A-GT.01', 'area' => 'BAHAN BAKU'],
            ['name' => 'CUTTING PLASMA 1', 'code' => 'A-CP.01', 'area' => 'BAHAN BAKU'],
            ['name' => 'CUTTING PLASMA 2', 'code' => 'A-CP.02', 'area' => 'BAHAN BAKU'],
            ['name' => 'HOIST 3', 'code' => 'A-HS.03', 'area' => 'BAHAN BAKU'],
            ['name' => 'HOIST 4', 'code' => 'A-HS.04', 'area' => 'BAHAN BAKU'],

            // Area: COR FLANGE
            ['name' => 'FOUNDRY 2', 'code' => 'B-FD.02', 'area' => 'COR FLANGE'],
            ['name' => 'FOUNDRY 3', 'code' => 'B-FD.03', 'area' => 'COR FLANGE'],
            ['name' => 'FOUNDRY 5', 'code' => 'B-FD.05', 'area' => 'COR FLANGE'],
            ['name' => 'KOMPRESOR SCREW 1', 'code' => 'B-KS.01', 'area' => 'COR FLANGE'],
            ['name' => 'KOMPRESOR SCREW 2', 'code' => 'B-KS.02', 'area' => 'COR FLANGE'],
            ['name' => 'HOIST 1', 'code' => 'B-HS.01', 'area' => 'COR FLANGE'],
            ['name' => 'HOIST 2', 'code' => 'B-HS.02', 'area' => 'COR FLANGE'],
            ['name' => 'MOLEN 1', 'code' => 'B-MN.01', 'area' => 'COR FLANGE'],
            ['name' => 'MOLEN 2', 'code' => 'B-MN.02', 'area' => 'COR FLANGE'],
            ['name' => 'MOULDING 1', 'code' => 'B-MD.01', 'area' => 'COR FLANGE'],
            ['name' => 'MOULDING 2', 'code' => 'B-MD.02', 'area' => 'COR FLANGE'],
            ['name' => 'MOULDING 3', 'code' => 'B-MD.03', 'area' => 'COR FLANGE'],
            ['name' => 'MOULDING 4', 'code' => 'B-MD.04', 'area' => 'COR FLANGE'],
            ['name' => 'MOULDING 5', 'code' => 'B-MD.05', 'area' => 'COR FLANGE'],
            ['name' => 'SAND TREATMENT 1', 'code' => 'B-ST.01', 'area' => 'COR FLANGE'],
            ['name' => 'AYAK PASIR 1', 'code' => 'B-AP.01', 'area' => 'COR FLANGE'],
            ['name' => 'LOADER 1', 'code' => 'B-LD.01', 'area' => 'COR FLANGE'],

            // Area: NETTO FLANGE
            ['name' => 'CUTTING PLASMA 3', 'code' => 'C-CP.03', 'area' => 'NETTO FLANGE'],
            ['name' => 'CUTTING PLASMA 4', 'code' => 'C-CP.04', 'area' => 'NETTO FLANGE'],
            ['name' => 'CUTTING PLASMA 5', 'code' => 'C-CP.05', 'area' => 'NETTO FLANGE'],
            ['name' => 'LAS ARGON 1', 'code' => 'C-LA.01', 'area' => 'NETTO FLANGE'],
            ['name' => 'LAS ARGON 2', 'code' => 'C-LA.02', 'area' => 'NETTO FLANGE'],
            ['name' => 'GERINDA KASAR 1', 'code' => 'C-GK.01', 'area' => 'NETTO FLANGE'],
            ['name' => 'GERINDA KASAR 2', 'code' => 'C-GK.02', 'area' => 'NETTO FLANGE'],
            ['name' => 'SAND BLASTING 1', 'code' => 'C.SB.01', 'area' => 'NETTO FLANGE'],
            ['name' => 'HEAD TREATMENT', 'code' => 'C-HT.01', 'area' => 'NETTO FLANGE'],

            // Area: GUDANG JADI FLANGE
            ['name' => 'STEMPEL ANGIN 1', 'code' => 'D-SA.01', 'area' => 'GUDANG JADI FLANGE'],
            ['name' => 'STEMPEL ANGIN 2', 'code' => 'D-SA.02', 'area' => 'GUDANG JADI FLANGE'],
            ['name' => 'STRAPING TALI 1', 'code' => 'D-SP.01', 'area' => 'GUDANG JADI FLANGE'],
            ['name' => 'STRAPING TALI 2', 'code' => 'D-SP.02', 'area' => 'GUDANG JADI FLANGE'],
            ['name' => 'WRAPPING', 'code' => 'D-WP.01', 'area' => 'GUDANG JADI FLANGE'],

            // Area: QC BALL VALVE
            ['name' => 'KOMPRESOR PISTON', 'code' => 'E-KP.01', 'area' => 'QC BALL VALVE'],
            ['name' => 'TEST BALL VALVE', 'code' => 'E-BV.01', 'area' => 'QC BALL VALVE'],
            ['name' => 'PENCUCI FITTING', 'code' => 'E-PF.01', 'area' => 'QC BALL VALVE'],
            ['name' => 'BOR MILLING 5', 'code' => 'E-ML.05', 'area' => 'QC BALL VALVE'],
            ['name' => 'HAND BLASTING 1', 'code' => 'E-HB.01', 'area' => 'QC BALL VALVE'],

            // Area: BUBUT FITTING
            ['name' => 'BUBUT CNC 80', 'code' => 'N-BC.80', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 78', 'code' => 'N-BC.78', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 79', 'code' => 'N-BC.79', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 76', 'code' => 'N-BC.76', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 77', 'code' => 'N-BC.77', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 63', 'code' => 'N-BC.63', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 43', 'code' => 'N-BC.43', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 61', 'code' => 'N-BC.61', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 62', 'code' => 'N-BC.62', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 83', 'code' => 'N-BC.83', 'area' => 'BUBUT FITTING'],
            ['name' => 'BUBUT CNC 64', 'code' => 'N-BC.64', 'area' => 'BUBUT FITTING'],

            // Area: BUBUT FLANGE BESI
            ['name' => 'BUBUT CNC 88', 'code' => 'O-BC.88', 'area' => 'BUBUT FLANGE BESI'],
            ['name' => 'BUBUT CNC 89', 'code' => 'O-BC.89', 'area' => 'BUBUT FLANGE BESI'],
            ['name' => 'BUBUT CNC 90', 'code' => 'O-BC.90', 'area' => 'BUBUT FLANGE BESI'],
            ['name' => 'BUBUT CNC 91', 'code' => 'O-BC.91', 'area' => 'BUBUT FLANGE BESI'],
            ['name' => 'BUBUT CNC 65', 'code' => 'O-BC.65', 'area' => 'BUBUT FLANGE BESI'],
            ['name' => 'BUBUT CNC 56', 'code' => 'O-BC.56', 'area' => 'BUBUT FLANGE BESI'],
            ['name' => 'BUBUT CNC 50', 'code' => 'O-BC.50', 'area' => 'BUBUT FLANGE BESI'],
            ['name' => 'BUBUT CNC 47', 'code' => 'O-BC.47', 'area' => 'BUBUT FLANGE BESI'],
            ['name' => 'BUBUT CNC 34', 'code' => 'O-BC.34', 'area' => 'BUBUT FLANGE BESI'],

            // Area: BUBUT CNC FLANGE
            ['name' => 'BUBUT CNC 85', 'code' => 'R-BC.85', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 84', 'code' => 'R-BC.84', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 82', 'code' => 'R-BC.82', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 81', 'code' => 'R-BC.81', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 74', 'code' => 'R-BC.74', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 73', 'code' => 'R-BC.73', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 70', 'code' => 'R-BC.70', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 69', 'code' => 'R-BC.69', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 68', 'code' => 'R-BC.68', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 67', 'code' => 'R-BC.67', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 66', 'code' => 'R-BC.66', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 55', 'code' => 'R-BC.55', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 53', 'code' => 'R-BC.53', 'area' => 'BUBUT CNC FLANGE'],
            ['name' => 'BUBUT CNC 41', 'code' => 'R-BC.41', 'area' => 'BUBUT CNC FLANGE'],

            // Area: UMUM
            ['name' => 'BALL MILL 01', 'code' => 'Y-BA.01', 'area' => 'UMUM'],
        ];

        // 1. Get creator User (Admin)
        $adminUser = User::where('email', 'admin@mrm.local')->first() ?? User::first();
        $createdBy = $adminUser ? $adminUser->id : null;

        // 2. Pre-cache Production Areas, Machine Categories, and Departments to prevent N+1 database queries
        $areaCache = MasterProductionArea::all()->keyBy('name');
        $categoryCache = MasterMachineCategory::all()->keyBy('name');
        $departmentCache = MasterDepartment::all()->keyBy('name');

        foreach ($machines as $data) {
            $code = $data['code'];
            $name = $data['name'];
            $areaName = $data['area'];

            // Production Area resolution & caching
            if (!$areaCache->has($areaName)) {
                $areaObj = MasterProductionArea::firstOrCreate(
                    ['name' => $areaName],
                    ['code' => Str::slug($areaName), 'is_active' => true, 'sort_order' => $areaCache->count() + 1]
                );
                $areaCache->put($areaName, $areaObj);
            } else {
                $areaObj = $areaCache->get($areaName);
            }

            // Automatic Category Classification
            $categoryName = self::classifyCategory($name);
            if (!$categoryCache->has($categoryName)) {
                $catObj = MasterMachineCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['code' => Str::slug($categoryName), 'is_active' => true, 'sort_order' => $categoryCache->count() + 1]
                );
                $categoryCache->put($categoryName, $catObj);
            }

            // Automatic Department Derivation
            $deptName = self::deriveDepartment($areaName, $categoryName);
            if (!$departmentCache->has($deptName)) {
                $deptObj = MasterDepartment::firstOrCreate(
                    ['name' => $deptName],
                    ['code' => Str::slug($deptName), 'is_active' => true, 'sort_order' => $departmentCache->count() + 1]
                );
                $departmentCache->put($deptName, $deptObj);
            }

            // Update or create machine (idempotent duplicate safety)
            Machine::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'department' => $deptName,
                    'production_area' => $areaName,
                    'production_area_id' => $areaObj->id,
                    'category' => $categoryName,
                    'criticality' => 'medium',
                    'operational_status' => 'running',
                    'is_active' => true,
                    'lifecycle_status' => 'ACTIVE',
                    'created_by' => $createdBy,
                ]
            );
        }
    }

    /**
     * Automatic category classification logic.
     */
    public static function classifyCategory(string $machineName): string
    {
        $upper = strtoupper($machineName);

        if (str_contains($upper, 'PRESS') && !str_contains($upper, 'COMPRESSOR') && !str_contains($upper, 'KOMPRESOR')) {
            return 'Press';
        }
        if (str_contains($upper, 'BUBUT')) {
            return 'Lathe';
        }
        if (str_contains($upper, 'CUTTING PLASMA')) {
            return 'Plasma Cutting';
        }
        if (str_contains($upper, 'BOR')) {
            return 'Drilling';
        }
        if (str_contains($upper, 'LAS ARGON') || str_contains($upper, 'LAS LISTRIK') || str_starts_with($upper, 'LAS ')) {
            return 'Welding';
        }
        if (str_contains($upper, 'GERINDA')) {
            return 'Grinding';
        }
        if (str_contains($upper, 'KOMPRESOR') || str_contains($upper, 'COMPRESSOR')) {
            return 'Compressor';
        }
        if (str_contains($upper, 'GENSET')) {
            return 'Generator';
        }
        if (str_contains($upper, 'FORKLIFT')) {
            return 'Forklift';
        }
        if (str_contains($upper, 'HOIST')) {
            return 'Hoist';
        }
        if (str_contains($upper, 'POMPA') || str_contains($upper, 'PUMP')) {
            return 'Pump';
        }
        if (str_contains($upper, 'MOULDING')) {
            return 'Moulding';
        }
        if (str_contains($upper, 'MIXER') || str_contains($upper, 'MOLEN')) {
            return 'Mixer';
        }
        if (str_contains($upper, 'BLOWER')) {
            return 'Blower';
        }
        if (str_contains($upper, 'FOUNDRY')) {
            return 'Furnace';
        }
        if (str_contains($upper, 'OVEN')) {
            return 'Oven';
        }
        if (str_contains($upper, 'BIOMAS')) {
            return 'Furnace';
        }
        if (str_contains($upper, 'SAND BLASTING') || str_contains($upper, 'HAND BLASTING')) {
            return 'Sand Blasting';
        }
        if (str_contains($upper, 'HEAD TREATMENT') || str_contains($upper, 'HEAT TREATMENT')) {
            return 'Heat Treatment';
        }
        if (str_contains($upper, 'TEST')) {
            return 'Testing Equipment';
        }
        if (str_contains($upper, 'WRAPPING') || str_contains($upper, 'STRAPING')) {
            return 'Packaging';
        }
        if (str_contains($upper, 'LOADER')) {
            return 'Loader';
        }
        if (str_contains($upper, 'SCRAP')) {
            return 'Utility';
        }
        if (str_contains($upper, 'AYAK')) {
            return 'Screening';
        }
        if (str_contains($upper, 'TRANSFER')) {
            return 'Transfer';
        }
        if (str_contains($upper, 'PENAMPUNG')) {
            return 'Tank';
        }
        if (str_contains($upper, 'PEMANAS')) {
            return 'Heater';
        }
        if (str_contains($upper, 'CETAK LILIN')) {
            return 'Wax Injection';
        }
        if (str_contains($upper, 'TANJEK LILIN')) {
            return 'Wax Processing';
        }

        return 'General Machine';
    }

    /**
     * Helper to derive department based on area or category.
     */
    public static function deriveDepartment(string $area, string $category): string
    {
        if (str_contains($area, 'BUBUT') || str_contains($area, 'NETTO') || $category === 'Lathe') {
            return 'Machining';
        }
        if (str_contains($area, 'COR') || $category === 'Furnace') {
            return 'Foundry';
        }
        if (str_contains($area, 'QC')) {
            return 'QC';
        }
        if (str_contains($area, 'GUDANG') || str_contains($area, 'BAHAN BAKU')) {
            return 'Warehouse';
        }

        return 'Production';
    }
}
