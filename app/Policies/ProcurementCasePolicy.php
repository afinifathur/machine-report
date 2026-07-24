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
        return $user->hasAnyRole([
            'Admin Maintenance',
            'Kabag Maintenance',
            'Direktur',
            'Purchasing',
            'Admin Sparepart',
            'MR',
            'Auditor',
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
        return $user->hasPermissionTo('create procurement') || $user->hasRole('Admin Maintenance');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProcurementCase $procurementCase): bool
    {
        // Hanya Draft yang boleh diedit secara konvensional
        return $procurementCase->status === ProcurementStatus::DRAFT 
            && ($user->id === $procurementCase->created_by || $user->hasRole('Admin Maintenance'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProcurementCase $procurementCase): bool
    {
        // Hanya Draft yang boleh dihapus
        return $procurementCase->status === ProcurementStatus::DRAFT 
            && ($user->id === $procurementCase->created_by || $user->hasRole('Admin Maintenance'));
    }

    /**
     * Determine whether the user can submit the model.
     */
    public function submit(User $user, ProcurementCase $procurementCase): bool
    {
        return $procurementCase->status === ProcurementStatus::DRAFT 
            && ($user->id === $procurementCase->created_by || $user->hasRole('Admin Maintenance'));
    }

    /**
     * Determine whether the user can approve Stage 1.
     */
    public function approveStage1(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::PENDING_KABAG && $user->hasRole('Kabag Maintenance');
    }

    /**
     * Determine whether the user can approve Stage 2.
     */
    public function approveStage2(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::PENDING_DIR && $user->hasRole('Direktur');
    }

    /**
     * Determine whether the user can request more info.
     */
    public function returnForInformation(User $user, ProcurementCase $case): bool
    {
        return match ($case->status) {
            ProcurementStatus::PENDING_KABAG => $user->hasRole('Kabag Maintenance'),
            ProcurementStatus::PENDING_DIR => $user->hasRole('Direktur'),
            ProcurementStatus::PROCESSING => $user->hasRole('Purchasing'),
            default => false,
        };
    }

    /**
     * Determine whether the user can update info after returned.
     */
    public function updateInformation(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::NEED_INFO 
            && ($user->id === $case->created_by || $user->hasRole('Admin Maintenance'));
    }

    /**
     * Determine whether the user can input PO details.
     */
    public function inputPO(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::PROCESSING && $user->hasRole('Purchasing');
    }

    /**
     * Determine whether the user can confirm arrival of goods.
     */
    public function confirmArrival(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::WAITING_DELIVERY && $user->hasRole('Admin Sparepart');
    }

    /**
     * Determine whether the user can confirm pickup of goods.
     */
    public function confirmPickup(User $user, ProcurementCase $case): bool
    {
        return $case->status === ProcurementStatus::READY_TO_PICKUP 
            && ($user->id === $case->created_by || $user->hasRole('Admin Maintenance'));
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
                $user->id === $case->created_by || $user->hasRole('Admin Maintenance'),
            ProcurementStatus::PENDING_KABAG => $user->hasRole('Kabag Maintenance') || $user->hasRole('Admin Maintenance'),
            ProcurementStatus::PENDING_DIR => $user->hasRole('Direktur') || $user->hasRole('Kabag Maintenance'),
            ProcurementStatus::PROCESSING, ProcurementStatus::WAITING_DELIVERY => $user->hasRole('Purchasing') || $user->hasRole('Kabag Maintenance'),
            default => false,
        };
    }
}
