<?php

namespace App\Services;

use App\Enums\ApprovalDecision;
use App\Enums\ProcurementStatus;
use App\Models\Approval;
use App\Models\ProcurementCase;
use App\Models\User;
use Illuminate\Support\Str;

class ProcurementWorkflowService
{
    /**
     * Create a new procurement case draft.
     */
    public function createDraft(array $data, User $creator): ProcurementCase
    {
        $today = now()->format('Ymd');
        $count = ProcurementCase::whereDate('created_at', now()->toDateString())->count() + 1;
        $caseNumber = 'PR-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        return ProcurementCase::create([
            'case_number' => $caseNumber,
            'machine_id' => $data['machine_id'],
            'item_name' => $data['item_name'],
            'urgency' => $data['urgency'] ?? 'normal',
            'status' => ProcurementStatus::DRAFT,
            'current_owner' => 'Admin Maintenance',
            'description' => $data['description'],
            'target_needed_date' => $data['target_needed_date'],
            'created_by' => $creator->id,
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
            'item_name' => $data['item_name'],
            'urgency' => $data['urgency'] ?? $case->urgency,
            'description' => $data['description'],
            'target_needed_date' => $data['target_needed_date'],
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
        if ($case->status !== ProcurementStatus::PENDING_DIR) {
            throw new \Exception('Aksi hanya boleh dijalankan jika status PENDING DIR.');
        }

        Approval::create([
            'procurement_case_id' => $case->id,
            'user_id' => $user->id,
            'stage' => 2,
            'decision' => ApprovalDecision::APPROVED,
            'note' => $note,
        ]);

        $case->update([
            'status' => ProcurementStatus::PROCESSING,
            'current_owner' => 'Purchasing',
        ]);

        return $case;
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
            'item_name' => $data['item_name'],
            'urgency' => $data['urgency'] ?? $case->urgency,
            'description' => $data['description'],
            'target_needed_date' => $data['target_needed_date'],
            'status' => ProcurementStatus::PENDING_KABAG,
            'current_owner' => 'Kabag Maintenance',
        ]);

        return $case;
    }

    /**
     * Input PO (Purchasing).
     */
    public function inputPO(ProcurementCase $case, string $poNumber, string $vendorName, string $poDate): ProcurementCase
    {
        if ($case->status !== ProcurementStatus::PROCESSING) {
            throw new \Exception('Aksi hanya boleh dijalankan jika status PROCESSING.');
        }

        if (empty(trim($poNumber)) || empty(trim($vendorName)) || empty(trim($poDate))) {
            throw new \Exception('Nomor PO, Vendor, dan Tanggal PO wajib diisi.');
        }

        $case->update([
            'po_number' => $poNumber,
            'vendor_name' => $vendorName,
            'po_date' => $poDate,
            'status' => ProcurementStatus::WAITING_DELIVERY,
            'current_owner' => 'Purchasing',
        ]);

        return $case;
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
}
