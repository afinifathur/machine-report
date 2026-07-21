<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceExecution;
use App\Services\MaintenanceReadinessService;
use App\Repositories\WarehouseRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected MaintenanceReadinessService $readinessService;
    protected WarehouseRepositoryInterface $warehouseRepo;

    public function __construct(
        MaintenanceReadinessService $readinessService,
        WarehouseRepositoryInterface $warehouseRepo
    ) {
        $this->readinessService = $readinessService;
        $this->warehouseRepo = $warehouseRepo;
    }

    /**
     * Display the morning briefing dashboard.
     */
    public function index()
    {
        $today = Carbon::today();

        // -------------------------------------------------------------
        // Query today's plans (scheduled for today or overdue/unfinished)
        // -------------------------------------------------------------
        $todayPlans = MaintenancePlan::with([
            'machine.documents',
            'maintenanceTemplate.checklists',
            'maintenanceTemplate.spareparts',
            'execution'
        ])
        ->whereHas('machine', function ($q) {
            $q->where('is_active', true)
              ->where('lifecycle_status', 'ACTIVE');
        })
        ->where(function ($query) use ($today) {
            $query->whereDate('scheduled_date', $today)
                  ->orWhere(function ($q) use ($today) {
                      $q->whereDate('scheduled_date', '<', $today)
                        ->whereNotIn('status', ['completed', 'cancelled']);
                  });
        })
        ->get();

        // Attach readiness report to each plan
        $todayPlans->each(function ($plan) {
            $plan->readiness = $this->readinessService->getReadinessReport($plan);
        });

        // -------------------------------------------------------------
        // SECTION 1 & 2: Contextual Greeting & Agenda Hari Ini
        // -------------------------------------------------------------
        $counts = [
            'completed' => 0,
            'waiting_review' => 0,
            'in_progress' => 0,
            'not_started' => 0,
        ];

        foreach ($todayPlans as $plan) {
            $statusText = $plan->readiness['overall_status'];
            if ($statusText === 'Completed') {
                $counts['completed']++;
            } elseif ($statusText === 'Waiting Review') {
                $counts['waiting_review']++;
            } elseif ($plan->status === 'in_progress') {
                $counts['in_progress']++;
            } else {
                $counts['not_started']++;
            }
        }

        $totalWorkOrders = $todayPlans->count();
        $attentionNeeded = $counts['waiting_review'] + $todayPlans->filter(fn($p) => $p->readiness['overall_status'] === 'Blocked')->count();

        // Greeting
        $hour = Carbon::now()->hour;
        if ($hour < 11) {
            $greeting = 'Selamat Pagi';
        } elseif ($hour < 15) {
            $greeting = 'Selamat Siang';
        } elseif ($hour < 19) {
            $greeting = 'Selamat Sore';
        } else {
            $greeting = 'Selamat Malam';
        }

        $greetingText = "{$greeting}. Hari ini terdapat {$totalWorkOrders} Perintah Kerja. {$attentionNeeded} membutuhkan perhatian Anda.";

        // -------------------------------------------------------------
        // SECTION 3: Hambatan Hari Ini (Today's Blockers)
        // -------------------------------------------------------------
        $blockers = [];

        foreach ($todayPlans as $plan) {
            $rd = $plan->readiness;
            $machine = $plan->machine;

            // 1. Machine Breakdown
            if (!$rd['machine_ready']) {
                $blockers[] = [
                    'type' => 'Breakdown',
                    'reason' => "Mesin {$machine->code} sedang mengalami kerusakan/down.",
                    'machine_code' => $machine->code,
                    'machine_name' => $machine->name,
                    'plan_id' => $plan->id,
                    'action_label' => 'Buka Paspor Mesin',
                    'action_url' => route('machines.show', $machine->code),
                    'severity' => 'critical'
                ];
            }

            // 2. Missing Sparepart
            if (!$rd['spareparts_available'] && $plan->maintenanceTemplate) {
                foreach ($plan->maintenanceTemplate->spareparts as $reqPart) {
                    $wms = $this->warehouseRepo->getItemDetails($reqPart->warehouse_item_code);
                    if ($wms['stock'] < $reqPart->quantity) {
                        $blockers[] = [
                            'type' => 'Sparepart',
                            'reason' => "Stok WMS kurang untuk {$reqPart->warehouse_item_code} ({$wms['name']}): Butuh {$reqPart->quantity}, Tersedia {$wms['stock']}.",
                            'machine_code' => $machine->code,
                            'machine_name' => $machine->name,
                            'plan_id' => $plan->id,
                            'action_label' => 'Lihat Detail PM',
                            'action_url' => route('planning.show', $plan->id),
                            'severity' => 'critical'
                        ];
                    }
                }
            }

            // 3. Technician Not Assigned
            if (!$rd['technician_assigned']) {
                $blockers[] = [
                    'type' => 'Technician',
                    'reason' => "Teknisi pelaksana belum ditunjuk untuk PM {$plan->maintenanceTemplate->name}.",
                    'machine_code' => $machine->code,
                    'machine_name' => $machine->name,
                    'plan_id' => $plan->id,
                    'action_label' => 'Tunjuk Teknisi',
                    'action_url' => route('planning.show', $plan->id),
                    'severity' => 'warning'
                ];
            }

            // 4. Missing Manual
            if (!$rd['documents_available']) {
                $blockers[] = [
                    'type' => 'Manual',
                    'reason' => "Buku manual (manual book) belum diunggah untuk mesin {$machine->code}.",
                    'machine_code' => $machine->code,
                    'machine_name' => $machine->name,
                    'plan_id' => $plan->id,
                    'action_label' => 'Buka Paspor Mesin',
                    'action_url' => route('machines.show', $machine->code),
                    'severity' => 'warning'
                ];
            }
        }

        // -------------------------------------------------------------
        // SECTION 4: Mesin Prioritas (Priority Machines)
        // -------------------------------------------------------------
        $allMachines = Machine::where('is_active', true)->where('lifecycle_status', 'ACTIVE')->get();
        $priorityMachines = $allMachines->map(function ($machine) use ($today) {
            $score = 100 - $machine->health_score; // lower health = higher priority
            $reasons = [];

            // Check if machine is breakdown
            if ($machine->operational_status === 'breakdown') {
                $score += 50;
                $reasons[] = 'Kerusakan Aktif';
            } elseif ($machine->operational_status === 'maintenance') {
                $score += 20;
                $reasons[] = 'Sedang Dirawat';
            }

            // Check blocked PMs scheduled for today or past
            $plansForMachine = MaintenancePlan::where('machine_id', $machine->id)
                ->where(function($q) use ($today) {
                    $q->whereDate('scheduled_date', $today)
                      ->orWhere(function($sq) use ($today) {
                          $sq->whereDate('scheduled_date', '<', $today)
                            ->whereNotIn('status', ['completed', 'cancelled']);
                      });
                })->get();

            $hasBlocked = false;
            $hasLate = false;

            foreach ($plansForMachine as $p) {
                $rd = $this->readinessService->getReadinessReport($p);
                if ($rd['overall_status'] === 'Blocked') {
                    $hasBlocked = true;
                }
                if ($p->scheduled_date->lt($today) && !in_array($p->status, ['completed', 'cancelled'])) {
                    $hasLate = true;
                }
            }

            if ($hasBlocked) {
                $score += 50;
                $reasons[] = 'PM Terblokir';
            }
            if ($hasLate) {
                $score += 30;
                $reasons[] = 'PM Terlambat';
            }

            // Check for recent low inspection scores (overall score < 3.0 in last 7 days)
            $recentLow = MaintenanceExecution::where('machine_id', $machine->id)
                ->where('completed_at', '>=', Carbon::now()->subDays(7))
                ->where('overall_score', '<', 3.0)
                ->exists();

            if ($recentLow) {
                $score += 40;
                $reasons[] = 'Inspeksi Rendah';
            }

            $machine->priority_score = $score;
            $machine->priority_reasons = $reasons;

            return $machine;
        })
        ->sortByDesc('priority_score')
        ->take(5)
        ->values();

        // -------------------------------------------------------------
        // SECTION 5: Aktivitas Hari Ini (Timeline)
        // -------------------------------------------------------------
        $timelineEvents = [];

        // 1. Executions started today
        $startedExecs = MaintenanceExecution::with(['plan.machine', 'plan.maintenanceTemplate'])
            ->whereDate('started_at', $today)
            ->get();

        foreach ($startedExecs as $exec) {
            $timeString = $exec->started_at->format('H:i');
            $timelineEvents[] = [
                'time' => $timeString,
                'raw_time' => $exec->started_at,
                'title' => "PM {$exec->plan->machine->code} Dimulai",
                'details' => "Teknisi: {$exec->operator_name} (SOP: {$exec->plan->maintenanceTemplate->name})",
                'type' => 'started',
                'color' => 'amber'
            ];
        }

        // 2. Executions completed/waiting review today
        $completedExecs = MaintenanceExecution::with(['plan.machine', 'plan.maintenanceTemplate'])
            ->whereDate('completed_at', $today)
            ->get();

        foreach ($completedExecs as $exec) {
            $timeString = $exec->completed_at->format('H:i');
            $isWaiting = $exec->status === 'waiting_review';
            $timelineEvents[] = [
                'time' => $timeString,
                'raw_time' => $exec->completed_at,
                'title' => $isWaiting 
                    ? "{$exec->plan->machine->code} Menunggu Review" 
                    : "PM {$exec->plan->machine->code} Selesai",
                'details' => "Teknisi: {$exec->operator_name} | Nilai: " . number_format($exec->overall_score, 2),
                'type' => $isWaiting ? 'waiting_review' : 'completed',
                'color' => $isWaiting ? 'blue' : 'green'
            ];
        }

        // 3. Blocked plans scheduled for today
        foreach ($todayPlans as $plan) {
            if ($plan->readiness['overall_status'] === 'Blocked') {
                // If it is blocked, we can list it as blocked at 08:00 (briefing time) or updated_at
                $timeString = '07:30';
                $timelineEvents[] = [
                    'time' => $timeString,
                    'raw_time' => $plan->updated_at->startOfDay()->addHours(7)->addMinutes(30), // Sort it early in the morning
                    'title' => "{$plan->machine->code} Terblokir",
                    'details' => "PM tertunda. Hambatan: " . ($plan->readiness['blockers'][0] ?? 'Masalah Ketersediaan'),
                    'type' => 'blocked',
                    'color' => 'red'
                ];
            }
        }

        // Sort events chronologically (ascending)
        usort($timelineEvents, function ($a, $b) {
            return $a['raw_time'] <=> $b['raw_time'];
        });

        // -------------------------------------------------------------
        // SECTION 6: Ringkasan Operasional (Supporting Indicators)
        // -------------------------------------------------------------
        $avgHealth = $allMachines->avg('health_score') ?? 100;
        
        $completedCount = MaintenancePlan::where('status', 'completed')->count();
        $totalCount = MaintenancePlan::count();
        $complianceRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 94;

        $breakdownCount = Machine::where('operational_status', 'breakdown')->count();
        $simulatedDowntime = 14.2; // base downtime in hours
        if ($breakdownCount > 0) {
            $simulatedDowntime += $breakdownCount * 3.5;
        }

        // Spare part alerts (items out of stock in WMS)
        $outOfStockCount = 0;
        $mockWarehouseItems = ['BRG-6204', 'SEAL-TC-40', 'HYD-OIL-46', 'SL-6204', 'PMP-G1', 'VBLT-A42', 'RLY-24V', 'BRG-NU22'];
        foreach ($mockWarehouseItems as $code) {
            $wms = $this->warehouseRepo->getItemDetails($code);
            if ($wms['stock'] == 0) {
                $outOfStockCount++;
            }
        }

        // -------------------------------------------------------------
        // SECTION 7: Rekomendasi Tindakan (Action Recommendations)
        // -------------------------------------------------------------
        $recommendations = [];

        // 1. Unassigned technician recommendation
        $unassignedPlans = $todayPlans->filter(fn($p) => $p->readiness['technician_assigned'] === false);
        if ($unassignedPlans->count() > 0) {
            $recommendations[] = [
                'title' => 'Tunjuk Teknisi Lapangan',
                'description' => "Terdapat {$unassignedPlans->count()} rencana PM hari ini yang belum ditugaskan ke teknisi pelaksana.",
                'action_label' => 'Tunjuk Teknisi',
                'action_url' => route('planning.index', ['readiness_status' => 'Almost Ready']),
                'icon' => 'engineering',
                'color' => 'orange'
            ];
        }

        // 2. Blocked plans spare part ordering recommendation
        $blockedSparepartsPlans = $todayPlans->filter(fn($p) => $p->readiness['spareparts_available'] === false);
        if ($blockedSparepartsPlans->count() > 0) {
            $recommendations[] = [
                'title' => 'Pesan Suku Cadang Kurang',
                'description' => "Terdapat {$blockedSparepartsPlans->count()} PM terblokir hari ini karena stok spare part di WMS kosong.",
                'action_label' => 'Buka Detail Hambatan',
                'action_url' => route('planning.index', ['readiness_status' => 'Blocked']),
                'icon' => 'shopping_cart',
                'color' => 'red'
            ];
        }

        // 3. Waiting review inspection reports recommendation
        $waitingReviewPlans = $todayPlans->filter(fn($p) => $p->readiness['overall_status'] === 'Waiting Review');
        if ($waitingReviewPlans->count() > 0) {
            $recommendations[] = [
                'title' => 'Tinjau Laporan Lapangan Selesai',
                'description' => "Ada {$waitingReviewPlans->count()} laporan checklist PM yang telah diserahkan teknisi dan menunggu persetujuan Anda.",
                'action_label' => 'Tinjau Laporan',
                'action_url' => route('planning.index', ['readiness_status' => 'Waiting Review']),
                'icon' => 'rate_review',
                'color' => 'blue'
            ];
        }

        // 4. Default: Print work orders recommendation
        $readyToPrintPlans = $todayPlans->filter(fn($p) => $p->readiness['overall_status'] === 'Ready');
        if ($readyToPrintPlans->count() > 0) {
            $recommendations[] = [
                'title' => 'Cetak Perintah Kerja Hari Ini',
                'description' => "Ada {$readyToPrintPlans->count()} PM berstatus Siap Eksekusi. Cetak Work Order Sheet untuk briefing pagi teknisi.",
                'action_label' => 'Cetak Lembar WO',
                'action_url' => route('planning.index', ['readiness_status' => 'Ready']),
                'icon' => 'print',
                'color' => 'green'
            ];
        }

        return view('dashboard.index', compact(
            'greetingText',
            'counts',
            'totalWorkOrders',
            'blockers',
            'priorityMachines',
            'timelineEvents',
            'avgHealth',
            'complianceRate',
            'simulatedDowntime',
            'outOfStockCount',
            'recommendations'
        ));
    }
}
