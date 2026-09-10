<?php

namespace App\Services;

use App\Enums\ApprovalDecision;
use App\Enums\ProcurementStatus;
use App\Models\Approval;
use App\Models\ProcurementCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcurementWorkflowService
{
    /**
     * Create a new procurement case draft.
     */
    public function createDraft(array $data, User $creator): ProcurementCase
    {
        $yearMonth = now()->format('Ym');
        $prefix = 'PC-' . $yearMonth . '-';

        // Get the maximum sequential number for the current month/year prefix (using withTrashed to prevent duplicates on soft deleted cases)
        $lastCase = ProcurementCase::withTrashed()
            ->where('case_number', 'like', $prefix . '%')
            ->orderBy('case_number', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastCase) {
            $lastNumStr = substr($lastCase->case_number, -4);
            $nextNumber = (int)$lastNumStr + 1;
        }

        $caseNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return ProcurementCase::create([
            'case_number' => $caseNumber,
            'machine_id' => $data['machine_id'],
            'procurement_category_id' => $data['procurement_category_id'] ?? null,
            'item_name' => $data['item_name'],
            'urgency' => $data['urgency'] ?? 'normal',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => $data['description'],
            'reason' => $data['reason'] ?? null,
            'target_needed_date' => $data['target_needed_date'],
            'machine_down' => filter_var($data['machine_down'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'sourcing_type' => $data['sourcing_type'] ?? null,
            'created_by' => $creator->id,
            'quantity_required' => $data['quantity_required'],
        ]);
    }

    /**
     * Update an existing procurement case draft.
     */
    public function updateDraft(ProcurementCase $case, array $data): ProcurementCase
    {
        if ($case->status !== ProcurementStatus::DRAFT) {
            throw new \Exception('Hanya pengadaan berstatus Draft yang dapat diperbarui.');
        }

        $case->update([
            'machine_id' => $data['machine_id'],
            'procurement_category_id' => $data['procurement_category_id'] ?? $case->procurement_category_id,
            'item_name' => $data['item_name'],
            'urgency' => $data['urgency'] ?? $case->urgency,
            'description' => $data['description'],
            'reason' => $data['reason'] ?? $case->reason,
            'target_needed_date' => $data['target_needed_date'],
            'machine_down' => isset($data['machine_down']) ? filter_var($data['machine_down'], FILTER_VALIDATE_BOOLEAN) : $case->machine_down,
            'sourcing_type' => $data['sourcing_type'] ?? $case->sourcing_type,
            'quantity_required' => $data['quantity_required'] ?? $case->quantity_required,
        ]);

        return $case;
    }

    /**
     * Delete an existing procurement case draft.
     */
    public function deleteDraft(ProcurementCase $case): bool
    {
        if ($case->status !== ProcurementStatus::DRAFT) {
            throw new \Exception('Hanya pengadaan berstatus Draft yang dapat dihapus.');
        }

        return $case->delete();
    }

    /**
     * Submit the procurement case to Kabag Maintenance.
     */
    public function submit(ProcurementCase $case): ProcurementCase
    {
        if ($case->status !== ProcurementStatus::DRAFT) {
            throw new \Exception('Hanya pengadaan berstatus Draft yang dapat diajukan.');
        }

        $case->update([
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
        ]);

        return $case;
    }

    /**
     * Approve Stage 1 (Kabag Maintenance).
     */
    public function approveStage1(ProcurementCase $case, User $user, ?string $note): ProcurementCase
    {
        if ($case->status !== ProcurementStatus::PENDING_KABAG) {
            throw new \Exception('Aksi hanya boleh dijalankan jika status PENDING KABAG.');
        }

        Approval::create([
            'procurement_case_id' => $case->id,
            'user_id' => $user->id,
            'stage' => 1,
            'decision' => ApprovalDecision::APPROVED,
            'note' => $note,
        ]);

        $case->update([
            'status' => ProcurementStatus::PENDING_DIR,
            'current_owner' => 'Direktur',
        ]);

        return $case;
    }

    /**
     * Approve Stage 2 (Direktur).
     */
    public function approveStage2(ProcurementCase $case, User $user, ?string $note): ProcurementCase
    {
        return DB::transaction(function () use ($case, $user, $note) {
            $lockedCase = ProcurementCase::where('id', $case->id)->lockForUpdate()->firstOrFail();

            if ($lockedCase->status !== ProcurementStatus::PENDING_DIR) {
                throw new \Exception('Aksi hanya boleh dijalankan jika status PENDING DIR.');
            }

            Approval::create([
                'procurement_case_id' => $lockedCase->id,
                'user_id' => $user->id,
                'stage' => 2,
                'decision' => ApprovalDecision::APPROVED,
                'note' => $note,
            ]);

            $lockedCase->update([
                'status' => ProcurementStatus::PROCESSING,
                'current_owner' => 'Purchasing',
            ]);

            return $lockedCase;
        });
    }

    /**
     * Return for Information (Need More Information).
     */
    public function returnForInformation(ProcurementCase $case, User $user, string $note): ProcurementCase
    {
        $validStatuses = [
            ProcurementStatus::PENDING_KABAG,
            ProcurementStatus::PENDING_DIR,
            ProcurementStatus::PROCESSING,
        ];

        if (!in_array($case->status, $validStatuses)) {
            throw new \Exception('Aksi hanya boleh dijalankan jika status PENDING KABAG, PENDING DIR, atau PROCESSING.');
        }

        if (empty(trim($note))) {
            throw new \Exception('Komentar wajib diisi untuk mengembalikan request.');
        }

        $stage = match ($case->status) {
            ProcurementStatus::PENDING_KABAG => 1,
            ProcurementStatus::PENDING_DIR => 2,
            ProcurementStatus::PROCESSING => 3,
        };

        Approval::create([
            'procurement_case_id' => $case->id,
            'user_id' => $user->id,
            'stage' => $stage,
            'decision' => ApprovalDecision::RETURNED_FOR_INFO,
            'note' => $note,
        ]);

        $case->update([
            'status' => ProcurementStatus::NEED_INFO,
            'current_owner' => 'Admin Maintenance',
        ]);

        return $case;
    }

    /**
     * Update information and resubmit.
     */
    public function updateInformation(ProcurementCase $case, array $data): ProcurementCase
    {
        if ($case->status !== ProcurementStatus::NEED_INFO) {
            throw new \Exception('Aksi hanya boleh dijalankan jika status NEED INFO.');
        }

        $case->update([
            'machine_id' => $data['machine_id'],
            'procurement_category_id' => $data['procurement_category_id'] ?? $case->procurement_category_id,
            'item_name' => $data['item_name'],
            'urgency' => $data['urgency'] ?? $case->urgency,
            'description' => $data['description'],
            'reason' => $data['reason'] ?? $case->reason,
            'target_needed_date' => $data['target_needed_date'],
            'machine_down' => isset($data['machine_down']) ? filter_var($data['machine_down'], FILTER_VALIDATE_BOOLEAN) : $case->machine_down,
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
            'quantity_required' => $data['quantity_required'] ?? $case->quantity_required,
        ]);

        return $case;
    }

    /**
     * Input PO (Purchasing).
     */
    public function inputPO(ProcurementCase $case, string $poNumber, string $vendorName, string $poDate, ?User $user = null): ProcurementCase
    {
        return DB::transaction(function () use ($case, $poNumber, $vendorName, $poDate, $user) {
            $lockedCase = ProcurementCase::where('id', $case->id)->lockForUpdate()->firstOrFail();

            if (!in_array($lockedCase->status, [ProcurementStatus::PROCESSING, ProcurementStatus::PENDING_DIR])) {
                throw new \Exception('Aksi hanya boleh dijalankan jika status PROCESSING atau PENDING DIR.');
            }

            if (empty(trim($poNumber)) || empty(trim($vendorName)) || empty(trim($poDate))) {
                throw new \Exception('Nomor PO, Vendor, dan Tanggal PO wajib diisi.');
            }

            // If proceeding from PENDING_DIR, record the Director approval as SKIPPED under delegated purchasing authority
            if ($lockedCase->status === ProcurementStatus::PENDING_DIR) {
                $actingUserId = $user?->id ?? auth()->id() ?? $lockedCase->created_by;
                Approval::create([
                    'procurement_case_id' => $lockedCase->id,
                    'user_id' => $actingUserId,
                    'stage' => 2,
                    'decision' => ApprovalDecision::SKIPPED,
                    'note' => 'Director approval skipped under delegated purchasing authority.',
                ]);
            }

            $lockedCase->update([
                'po_number' => $poNumber,
                'vendor_name' => $vendorName,
                'po_date' => $poDate,
                'status' => ProcurementStatus::WAITING_DELIVERY,
                'current_owner' => 'Purchasing',
            ]);

            return $lockedCase;
        });
    }

    /**
     * Confirm Arrival (Admin Sparepart).
     */
    public function confirmArrival(ProcurementCase $case, string $rackLocation): ProcurementCase
    {
        if ($case->status !== ProcurementStatus::WAITING_DELIVERY) {
            throw new \Exception('Aksi hanya boleh dijalankan jika status WAITING DELIVERY.');
        }

        if (empty(trim($rackLocation))) {
            throw new \Exception('Lokasi rack wajib diisi saat konfirmasi barang datang.');
        }

        $case->update([
            'rack_location' => $rackLocation,
            'status' => ProcurementStatus::READY_TO_PICKUP,
            'current_owner' => 'Admin Maintenance',
        ]);

        return $case;
    }

    /**
     * Confirm Pickup (Admin Maintenance).
     */
    public function confirmPickup(ProcurementCase $case): ProcurementCase
    {
        if ($case->status !== ProcurementStatus::READY_TO_PICKUP) {
            throw new \Exception('Aksi hanya boleh dijalankan jika status READY TO PICKUP.');
        }

        $case->update([
            'status' => ProcurementStatus::CLOSED,
            'current_owner' => 'None',
        ]);

        return $case;
    }

    /**
     * Cancel Request.
     */
    public function cancel(ProcurementCase $case, string $reason, User $user): ProcurementCase
    {
        if ($case->status === ProcurementStatus::CLOSED || $case->status === ProcurementStatus::CANCELLED) {
            throw new \Exception('Kasus yang sudah CLOSED atau CANCELLED tidak dapat dibatalkan.');
        }

        if (empty(trim($reason))) {
            throw new \Exception('Alasan pembatalan wajib diisi.');
        }

        Approval::create([
            'procurement_case_id' => $case->id,
            'user_id' => $user->id,
            'stage' => 0,
            'decision' => ApprovalDecision::REJECTED,
            'note' => $reason,
        ]);

        $case->update([
            'status' => ProcurementStatus::CANCELLED,
            'current_owner' => 'None',
        ]);

        return $case;
    }

    /**
     * Reject Case (returns back to DRAFT and Owner back to Admin Maintenance).
     */
    public function reject(ProcurementCase $case, User $user, string $note): ProcurementCase
    {
        $validStatuses = [
            ProcurementStatus::PENDING_KABAG,
            ProcurementStatus::PENDING_DIR,
        ];

        if (!in_array($case->status, $validStatuses)) {
            throw new \Exception('Aksi reject hanya boleh dijalankan jika status PENDING KABAG atau PENDING DIR.');
        }

        if (empty(trim($note))) {
            throw new \Exception('Catatan penolakan (Review Note) wajib diisi.');
        }

        $stage = match ($case->status) {
            ProcurementStatus::PENDING_KABAG => 1,
            ProcurementStatus::PENDING_DIR => 2,
        };

        Approval::create([
            'procurement_case_id' => $case->id,
            'user_id' => $user->id,
            'stage' => $stage,
            'decision' => ApprovalDecision::REJECTED,
            'note' => $note,
        ]);

        $case->update([
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
        ]);

        return $case;
    }
}
