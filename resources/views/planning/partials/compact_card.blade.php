@php
    $rd = $plan->readiness;
    $statusText = $rd['overall_status'];
    
    $statusBadgeClass = match($statusText) {
        'Completed' => 'bg-green-50 text-green-700 border border-green-100',
        'Waiting Review' => 'bg-blue-50 text-blue-700 border border-blue-100',
        'Ready' => 'bg-green-50 text-green-700 border border-green-100',
        'Almost Ready' => 'bg-orange-50 text-orange-700 border border-orange-100',
        'Blocked' => 'bg-red-50 text-red-700 border border-red-100/50',
        'Reported' => 'bg-rose-50 text-rose-700 border border-rose-100',
        'Assigned' => 'bg-amber-50 text-amber-700 border border-amber-100',
        default => 'bg-slate-50 text-slate-700 border border-slate-100',
    };

    $statusLabel = match($statusText) {
        'Completed' => 'Selesai',
        'Waiting Review' => 'Menunggu Review',
        'Ready' => 'Siap Eksekusi',
        'Almost Ready' => 'Hampir Siap',
        'Blocked' => 'Terblokir',
        'Reported' => 'Dilaporkan',
        'Assigned' => 'Ditugaskan',
        default => $statusText,
    };

    $priorityBadgeClass = match($plan->priority) {
        'low' => 'bg-slate-100 text-slate-700 border border-slate-200',
        'medium' => 'bg-blue-50 text-blue-700 border border-blue-100',
        'high' => 'bg-orange-50 text-orange-700 border border-orange-100',
        'critical' => 'bg-red-50 text-red-700 border border-red-100 font-bold',
        default => 'bg-slate-100 text-slate-700 border border-slate-200',
    };

    $priorityLabel = match($plan->priority) {
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'critical' => 'Kritis',
        default => $plan->priority,
    };

    $priorityColor = match($plan->priority) {
        'critical' => 'border-l-4 border-l-red-500',
        'high' => 'border-l-4 border-l-orange-500',
        'medium' => 'border-l-4 border-l-yellow-500',
        'low' => 'border-l-4 border-l-green-500',
        default => 'border-l-4 border-l-slate-300',
    };
@endphp

<div class="bg-white border border-slate-200 {{ $priorityColor }} rounded-xl p-3 shadow-xs hover:shadow hover:border-primary/45 transition-all flex flex-col md:flex-row md:items-center justify-between gap-3 relative">
    <!-- Left details column -->
    <div class="flex-1 min-w-0 space-y-1.5">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="mono text-[10px] font-bold text-primary px-1.5 py-0.25 bg-primary/10 rounded">{{ $plan->machine->code }}</span>
            <span class="text-xs text-slate-800 font-bold truncate max-w-[200px]" title="{{ $plan->machine->name }}">{{ $plan->machine->name }}</span>
            
            <span class="inline-flex items-center gap-1 px-1.5 py-0.25 rounded text-[9px] font-bold uppercase {{ $priorityBadgeClass }}">
                {{ $priorityLabel }}
            </span>
            
            @if($plan->isCorrective())
                <span class="inline-flex items-center gap-1 px-1.5 py-0.25 rounded text-[9px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-100">
                    CM
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-1.5 py-0.25 rounded text-[9px] font-bold uppercase bg-green-50 text-green-700 border border-green-100">
                    PM
                </span>
            @endif
            
            <span class="inline-flex items-center gap-1 px-1.5 py-0.25 rounded text-[9px] font-bold uppercase {{ $statusBadgeClass }}">
                {{ $statusLabel }}
            </span>
        </div>
        
        <div class="flex flex-col sm:flex-row sm:items-center gap-x-4 gap-y-1 text-xs">
            <h4 class="font-bold text-slate-800 truncate" title="{{ $plan->isCorrective() ? $plan->breakdown_number : $plan->maintenanceTemplate->name }}">
                @if($plan->isCorrective())
                    BD: {{ $plan->breakdown_number }}
                @else
                    PM: {{ $plan->maintenanceTemplate->name }}
                @endif
            </h4>
            <div class="flex flex-wrap items-center gap-3 text-slate-500 text-[11px]">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">timer</span>
                    @if($plan->isCorrective())
                        {{ $plan->downtime_duration ?? '-' }}m
                    @else
                        {{ $plan->maintenanceTemplate->estimated_duration }}m
                    @endif
                </span>
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">engineering</span>
                    {{ $plan->assigned_technician ?? 'Belum Ditugaskan' }}
                </span>
                <span class="flex items-center gap-1 font-semibold text-slate-600" title="Jadwal Rencana">
                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                    {{ $plan->scheduled_date->format('d M Y') }}
                </span>
                @if($plan->target_completion)
                    <span class="flex items-center gap-1 font-semibold text-primary" title="Target Selesai">
                        <span class="material-symbols-outlined text-[14px]">event_available</span>
                        Target: {{ $plan->target_completion->format('d M Y H:i') }}
                    </span>
                @endif
            </div>
        </div>

        @if($plan->notes)
            <p class="text-[10px] text-slate-400 italic truncate max-w-full" title="{{ $plan->notes }}">
                "{{ $plan->notes }}"
            </p>
        @endif

        @if($statusText === 'Blocked' && count($rd['blockers']) > 0)
            <div class="text-[10px] text-red-500 font-medium flex items-center gap-1 truncate">
                <span class="material-symbols-outlined text-[12px] shrink-0">error</span>
                <span>Hambatan: {{ implode(', ', $rd['blockers']) }}</span>
            </div>
        @elseif($statusText === 'Almost Ready' && count($rd['warnings']) > 0)
            <div class="text-[10px] text-orange-600 font-medium flex items-center gap-1 truncate">
                <span class="material-symbols-outlined text-[12px] shrink-0">warning</span>
                <span>Peringatan: {{ implode(', ', $rd['warnings']) }}</span>
            </div>
        @endif
    </div>

    <!-- Right action button -->
    <div class="shrink-0 flex items-center">
        <a href="{{ route('planning.show', $plan->id) }}" class="w-full md:w-auto bg-primary hover:bg-primary-container text-on-primary text-xs font-bold px-3 py-2 rounded-lg transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
            Audit Kesiapan
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        </a>
    </div>
</div>
