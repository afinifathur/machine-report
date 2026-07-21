<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MachineComponent;
use App\Models\MachineRequiredSparepart;
use App\Models\MachineDocument;
use App\Models\MachinePhoto;
use App\Models\MasterProductionArea;
use Illuminate\Database\Seeder;

class DummyMachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $machinesData = [
            [
                'code' => 'CNC-08',
                'name' => 'CNC Milling Center',
                'department' => 'Machining',
                'production_area' => 'Area A',
                'category' => 'Milling',
                'criticality' => 'high',
                'operational_status' => 'breakdown',
                'manufacturer' => 'Siemens',
                'model' => 'X-500',
                'serial_number' => 'SN-CNC08-2019',
                'installation_date' => '2019-03-12',
                'commissioning_date' => '2019-03-20',
                'vendor' => 'Siemens Industrial Solutions',
                'qr_code_path' => 'images/qr-cnc-08.png',
                'is_active' => true,
                'lifecycle_status' => 'ACTIVE',
                'components' => ['Spindle Unit', 'Motor', 'Lubrication System', 'Cooling System', 'PLC'],
                'spareparts' => ['BRG-6204', 'SEAL-TC-40', 'HYD-OIL-46'],
                'documents' => [
                    'manual_book' => ['file_name' => 'manual_book_cnc_08.pdf', 'file_path' => '/documents/manuals/cnc_08_manual.pdf'],
                    'electrical_diagram' => ['file_name' => 'electrical_schematic_v2.pdf', 'file_path' => '/documents/schematics/cnc_08_electrical.pdf'],
                    'hydraulic_diagram' => ['file_name' => null, 'file_path' => null],
                    'parameter_backup' => ['file_name' => null, 'file_path' => null],
                    'vendor_document' => ['file_name' => 'warranty_cert.pdf', 'file_path' => '/documents/warranty/cnc_08_warranty.pdf'],
                ],
                'photos' => [
                    'overall' => ['file_name' => 'cnc_08_overall.webp', 'file_path' => 'images/cnc-08.webp'],
                    'name_plate' => ['file_name' => null, 'file_path' => null],
                    'electrical_cabinet' => ['file_name' => null, 'file_path' => null],
                    'hydraulic_unit' => ['file_name' => null, 'file_path' => null],
                ]
            ],
            [
                'code' => 'CNC-04',
                'name' => 'Precision Lathe Pro',
                'department' => 'Machining',
                'production_area' => 'Area B',
                'category' => 'Lathe',
                'criticality' => 'mission_critical',
                'operational_status' => 'breakdown',
                'manufacturer' => 'Haas',
                'model' => 'VF-2',
                'serial_number' => 'SN-VF2-2020',
                'installation_date' => '2020-05-15',
                'commissioning_date' => '2020-05-22',
                'vendor' => 'Haas Automation Inc.',
                'qr_code_path' => 'images/qr-cnc-08.png',
                'is_active' => true,
                'lifecycle_status' => 'ACTIVE',
                'components' => ['Spindle Unit', 'Cooling System', 'Servo X Axis', 'Servo Z Axis'],
                'spareparts' => ['BRG-6204', 'SEAL-TC-40'],
                'documents' => [
                    'manual_book' => ['file_name' => 'manual_lathe_vf2.pdf', 'file_path' => '/documents/manuals/haas_vf2_manual.pdf'],
                    'electrical_diagram' => ['file_name' => null, 'file_path' => null],
                    'hydraulic_diagram' => ['file_name' => null, 'file_path' => null],
                    'parameter_backup' => ['file_name' => 'params_backup_2023.txt', 'file_path' => '/documents/backups/haas_vf2_params.txt'],
                    'vendor_document' => ['file_name' => null, 'file_path' => null],
                ],
                'photos' => [
                    'overall' => ['file_name' => null, 'file_path' => null],
                    'name_plate' => ['file_name' => null, 'file_path' => null],
                    'electrical_cabinet' => ['file_name' => null, 'file_path' => null],
                    'hydraulic_unit' => ['file_name' => null, 'file_path' => null],
                ]
            ],
            [
                'code' => 'ARM-12',
                'name' => 'Robotic Welder X1',
                'department' => 'Assembly Center',
                'production_area' => 'Area C',
                'category' => 'Robot',
                'criticality' => 'high',
                'operational_status' => 'maintenance',
                'manufacturer' => 'Fanuc',
                'model' => 'R-2000iC',
                'serial_number' => 'SN-FANUC-2021',
                'installation_date' => '2021-08-10',
                'commissioning_date' => '2021-08-18',
                'vendor' => 'Fanuc America Corp',
                'qr_code_path' => 'images/qr-cnc-08.png',
                'is_active' => true,
                'lifecycle_status' => 'ACTIVE',
                'components' => ['Servo System', 'PLC', 'Motor', 'Lubrication Unit'],
                'spareparts' => ['RLY-24V', 'BRG-NU22'],
                'documents' => [
                    'manual_book' => ['file_name' => null, 'file_path' => null],
                    'electrical_diagram' => ['file_name' => 'robotic_welder_electrical.pdf', 'file_path' => '/documents/schematics/fanuc_welder_electrical.pdf'],
                    'hydraulic_diagram' => ['file_name' => null, 'file_path' => null],
                    'parameter_backup' => ['file_name' => null, 'file_path' => null],
                    'vendor_document' => ['file_name' => null, 'file_path' => null],
                ],
                'photos' => [
                    'overall' => ['file_name' => null, 'file_path' => null],
                    'name_plate' => ['file_name' => null, 'file_path' => null],
                    'electrical_cabinet' => ['file_name' => null, 'file_path' => null],
                    'hydraulic_unit' => ['file_name' => null, 'file_path' => null],
                ]
            ],
            [
                'code' => 'PMP-08',
                'name' => 'Hydraulic Feed Pump',
                'department' => 'Maintenance',
                'production_area' => 'Area D',
                'category' => 'Pump',
                'criticality' => 'medium',
                'operational_status' => 'idle',
                'manufacturer' => 'Rexroth',
                'model' => 'A4VSO',
                'serial_number' => 'SN-REXROTH-2018',
                'installation_date' => '2018-11-05',
                'commissioning_date' => '2018-11-12',
                'vendor' => 'Bosch Rexroth Group',
                'qr_code_path' => 'images/qr-cnc-08.png',
                'is_active' => true,
                'lifecycle_status' => 'ACTIVE',
                'components' => ['Hydraulic Unit', 'Motor', 'Lubrication Unit'],
                'spareparts' => ['PMP-G1', 'HYD-OIL-46'],
                'documents' => [
                    'manual_book' => ['file_name' => null, 'file_path' => null],
                    'electrical_diagram' => ['file_name' => null, 'file_path' => null],
                    'hydraulic_diagram' => ['file_name' => null, 'file_path' => null],
                    'parameter_backup' => ['file_name' => null, 'file_path' => null],
                    'vendor_document' => ['file_name' => null, 'file_path' => null],
                ],
                'photos' => [
                    'overall' => ['file_name' => null, 'file_path' => null],
                    'name_plate' => ['file_name' => null, 'file_path' => null],
                    'electrical_cabinet' => ['file_name' => null, 'file_path' => null],
                    'hydraulic_unit' => ['file_name' => null, 'file_path' => null],
                ]
            ],
            [
                'code' => 'DRL-19',
                'name' => 'Radial Drill Press',
                'department' => 'Maintenance',
                'production_area' => 'Workshop',
                'category' => 'Drilling',
                'criticality' => 'low',
                'operational_status' => 'running',
                'manufacturer' => 'Carlton',
                'model' => '3A',
                'serial_number' => 'SN-CARLTON-2015',
                'installation_date' => '2015-06-20',
                'commissioning_date' => '2015-06-25',
                'vendor' => 'Carlton Machine Tool Co',
                'qr_code_path' => 'images/qr-cnc-08.png',
                'is_active' => true,
                'lifecycle_status' => 'ACTIVE',
                'components' => ['Motor', 'Lubrication Unit', 'Spindle Unit'],
                'spareparts' => ['VBLT-A42'],
                'documents' => [
                    'manual_book' => ['file_name' => 'carlton_3a_manual.pdf', 'file_path' => '/documents/manuals/carlton_3a_manual.pdf'],
                    'electrical_diagram' => ['file_name' => 'carlton_3a_electrical.pdf', 'file_path' => '/documents/schematics/carlton_3a_electrical.pdf'],
                    'hydraulic_diagram' => ['file_name' => null, 'file_path' => null],
                    'parameter_backup' => ['file_name' => null, 'file_path' => null],
                    'vendor_document' => ['file_name' => null, 'file_path' => null],
                ],
                'photos' => [
                    'overall' => ['file_name' => null, 'file_path' => null],
                    'name_plate' => ['file_name' => null, 'file_path' => null],
                    'electrical_cabinet' => ['file_name' => null, 'file_path' => null],
                    'hydraulic_unit' => ['file_name' => null, 'file_path' => null],
                ]
            ]
        ];

        foreach ($machinesData as $data) {
            $areaCode = match($data['department']) {
                'Machining' => 'MACHINING',
                'Assembly Center' => 'ASSEMBLY',
                'Maintenance' => 'MAINTENANCE',
                default => 'MACHINING'
            };
            $area = MasterProductionArea::where('code', $areaCode)->first();
            $productionAreaId = $area ? $area->id : null;

            $machine = Machine::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'department' => $data['department'],
                'production_area_id' => $productionAreaId,
                'production_area' => $data['production_area'],
                'category' => $data['category'],
                'criticality' => $data['criticality'],
                'operational_status' => $data['operational_status'],
                'manufacturer' => $data['manufacturer'],
                'model' => $data['model'],
                'serial_number' => $data['serial_number'],
                'installation_date' => $data['installation_date'],
                'commissioning_date' => $data['commissioning_date'],
                'vendor' => $data['vendor'],
                'qr_code_path' => $data['qr_code_path'],
                'is_active' => $data['is_active'],
                'lifecycle_status' => $data['lifecycle_status'],
            ]);

            // Components
            foreach ($data['components'] as $componentName) {
                MachineComponent::create([
                    'machine_id' => $machine->id,
                    'name' => $componentName
                ]);
            }

            // Required Spare Parts mappings
            foreach ($data['spareparts'] as $itemCode) {
                MachineRequiredSparepart::create([
                    'machine_id' => $machine->id,
                    'warehouse_item_code' => $itemCode
                ]);
            }

            // Documents
            foreach ($data['documents'] as $type => $doc) {
                MachineDocument::create([
                    'machine_id' => $machine->id,
                    'type' => $type,
                    'file_name' => $doc['file_name'],
                    'file_path' => $doc['file_path'],
                ]);
            }

            // Photos
            foreach ($data['photos'] as $type => $photo) {
                MachinePhoto::create([
                    'machine_id' => $machine->id,
                    'type' => $type,
                    'file_name' => $photo['file_name'],
                    'file_path' => $photo['file_path'],
                ]);
            }
        }
    }
}
