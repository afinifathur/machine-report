<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MaintenancePlan;

class MaintenancePlanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('planning.view') 
            || $user->hasPermissionTo('preventive.view') 
            || $user->hasPermissionTo('breakdown.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MaintenancePlan $plan): bool
    {
        if ($plan->isCorrective()) {
            return $user->hasPermissionTo('breakdown.view') || $user->hasPermissionTo('planning.view');
        }
        return $user->hasPermissionTo('preventive.view') || $user->hasPermissionTo('planning.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, ?string $type = null): bool
    {
        if ($type === 'preventive' || $type === 'pm') {
            return $user->hasPermissionTo('preventive.create') || $user->hasPermissionTo('planning.create');
        }
        if ($type === 'corrective' || $type === 'breakdown') {
            return $user->hasPermissionTo('breakdown.create') || $user->hasPermissionTo('planning.create');
        }
        return $user->hasPermissionTo('planning.create') 
            || $user->hasPermissionTo('preventive.create') 
            || $user->hasPermissionTo('breakdown.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MaintenancePlan $plan): bool
    {
        if ($plan->isCorrective()) {
            return $user->hasPermissionTo('breakdown.create') || $user->hasPermissionTo('planning.create');
        }
        return $user->hasPermissionTo('preventive.create') || $user->hasPermissionTo('planning.create');
    }

    /**
     * Determine whether the user can assign a technician.
     */
    public function assign(User $user, MaintenancePlan $plan): bool
    {
        if ($plan->isCorrective()) {
            return $user->hasPermissionTo('breakdown.assign') || $user->hasPermissionTo('planning.assign');
        }
        return $user->hasPermissionTo('preventive.assign') || $user->hasPermissionTo('planning.assign');
    }

    /**
     * Determine whether the user can execute the plan.
     */
    public function execute(User $user, MaintenancePlan $plan): bool
    {
        return $user->hasPermissionTo('planning.execute');
    }

    /**
     * Determine whether the user can verify the plan completion.
     */
    public function verify(User $user, MaintenancePlan $plan): bool
    {
        if ($plan->isCorrective()) {
            return $user->hasPermissionTo('breakdown.verify') || $user->hasPermissionTo('planning.verify');
        }
        return $user->hasPermissionTo('preventive.verify') || $user->hasPermissionTo('planning.verify');
    }

    /**
     * Determine whether the user can print the plan.
     */
    public function print(User $user, MaintenancePlan $plan): bool
    {
        return $user->hasPermissionTo('planning.print');
    }
}
