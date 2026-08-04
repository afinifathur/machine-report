<?php

namespace App\Policies;

use App\Enums\ProcurementStatus;
use App\Models\ProcurementCase;
use App\Models\User;

class ProcurementCasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('procurement.view') || $user->hasAnyRole([
            'Admin Maintenance',
            'Kabag Maintenance',
            'Direktur',
            'Purchasing',
            'Admin Sparepart',
            'MR',
            'Auditor',
            'Maintenance Administrator',
            'Maintenance Manager',
            'Director',
            'Warehouse Administrator',
            'Management Representative',
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProcurementCase $procurementCase): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('procurement.create') 
            || $user->hasPermissionTo('create procurement') 
            || $user->hasAnyRole(['Admin Maintenance', 'Maintenance Administrator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProcurementCase $procurementCase): bool
    {
        // Hanya Draft yang boleh diedit secara konvensional
        return $procurementCase->status === ProcurementStatus::DRAFT 
            && ($user->id === $procurementCase->created_by || $user->hasAnyRole(['Admin Maintenance', 'Maintenance Administrator']));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProcurementCase $procurementCase): bool
    {
        // Hanya Draft yang boleh dihapus
        return $procurementCase->status === ProcurementStatus::DRAFT 
            && ($user->id === $procurementCase->created_by || $user->hasAnyRole(['Admin Maintenance', 'Maintenance Administrator']));
    }

    /**
     * Determine whether the user can submit the model.
     */
    public function submit(User $user, ProcurementCase $procurementCase): bool
    {
        return $procurementCase->status === ProcurementStatus::DRAFT 
            && ($user->id === $procurementCase->created_by || $user->hasAnyRole(['Admin Maintenance', 'Maintenance Administrator']));
    }

    /**
     * Determine whether the user can approve Stage 1.
     */
    public function approveStage1(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::PENDING_KABAG 
            && ($user->hasPermissionTo('procurement.approve.stage1') || $user->hasAnyRole(['Kabag Maintenance', 'Maintenance Manager']));
    }

    /**
     * Determine whether the user can approve Stage 2.
     */
    public function approveStage2(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::PENDING_DIR 
            && ($user->hasPermissionTo('procurement.approve.stage2') || $user->hasAnyRole(['Direktur', 'Director']));
    }

    /**
     * Determine whether the user can request more info.
     */
    public function returnForInformation(User $user, ProcurementCase $case): bool
    {
        return match ($case->status) {
            ProcurementStatus::PENDING_KABAG => $user->hasAnyRole(['Kabag Maintenance', 'Maintenance Manager']),
            ProcurementStatus::PENDING_DIR => $user->hasAnyRole(['Direktur', 'Director']),
            ProcurementStatus::PROCESSING => $user->hasAnyRole(['Purchasing']),
            default => false,
        };
    }

    /**
     * Determine whether the user can update info after returned.
     */
    public function updateInformation(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::NEED_INFO 
            && ($user->id === $case->created_by || $user->hasAnyRole(['Admin Maintenance', 'Maintenance Administrator']));
    }

    /**
     * Determine whether the user can input PO details.
     */
    public function inputPO(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::PROCESSING 
            && ($user->hasPermissionTo('procurement.process') || $user->hasAnyRole(['Purchasing']));
    }

    /**
     * Determine whether the user can confirm arrival of goods.
     */
    public function confirmArrival(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::WAITING_DELIVERY 
            && ($user->hasPermissionTo('procurement.receive') || $user->hasAnyRole(['Admin Sparepart', 'Warehouse Administrator']));
    }

    /**
     * Determine whether the user can confirm pickup of goods.
     */
    public function confirmPickup(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::READY_TO_PICKUP 
            && ($user->hasPermissionTo('procurement.pickup') 
                || $user->id === $case->created_by 
                || $user->hasAnyRole(['Admin Maintenance', 'Maintenance Administrator']));
    }

    /**
     * Determine whether the user can cancel the request.
     */
    public function cancel(User $user, ProcurementCase $case): bool
    {
        if ($case->status === ProcurementStatus::CLOSED || $case->status === ProcurementStatus::CANCELLED) {
            return false;
        }

        // Pembatalan disesuaikan dengan current owner status
        return match ($case->status) {
            ProcurementStatus::DRAFT, ProcurementStatus::NEED_INFO, ProcurementStatus::READY_TO_PICKUP => 
                $user->id === $case->created_by || $user->hasAnyRole(['Admin Maintenance', 'Maintenance Administrator']),
            ProcurementStatus::PENDING_KABAG => 
                $user->hasAnyRole(['Kabag Maintenance', 'Maintenance Manager', 'Admin Maintenance', 'Maintenance Administrator']),
            ProcurementStatus::PENDING_DIR => 
                $user->hasAnyRole(['Direktur', 'Director', 'Kabag Maintenance', 'Maintenance Manager']),
            ProcurementStatus::PROCESSING, ProcurementStatus::WAITING_DELIVERY => 
                $user->hasAnyRole(['Purchasing', 'Kabag Maintenance', 'Maintenance Manager']),
            default => false,
        };
    }

    /**
     * Determine whether the user can upload attachments.
     */
    public function uploadAttachment(User $user, ProcurementCase $case): bool
    {
        if (!$this->view($user, $case)) {
            return false;
        }

        return $case->status === ProcurementStatus::DRAFT;
    }

    /**
     * Determine whether the user can delete attachments.
     */
    public function deleteAttachment(User $user, \App\Models\ProcurementAttachment $attachment): bool
    {
        $case = $attachment->case;

        if ($case->status !== ProcurementStatus::DRAFT) {
            return false;
        }

        return $attachment->uploaded_by === $user->id || $user->hasAnyRole(['Admin Maintenance', 'Maintenance Administrator']);
    }
}
