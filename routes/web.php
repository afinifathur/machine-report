<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineSparepartController;
use App\Http\Controllers\SparepartIntegrationController;
use App\Http\Controllers\MachineQrCodeController;
use App\Http\Controllers\MachineDocumentPhotoController;
use App\Http\Controllers\MachineDocumentLinkController;
use App\Http\Controllers\MaintenancePlanController;
use App\Http\Controllers\MaintenanceExecutionController;
use App\Http\Controllers\ProcurementCaseController;
use App\Http\Controllers\BreakdownController;

// =========================================================================
// GUEST ROUTES (Unauthenticated Users)
// =========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// =========================================================================
// AUTHENTICATED ROUTES (Protected by 'auth' middleware)
// =========================================================================
Route::middleware('auth')->group(function () {
    // Logout Action
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Morning Briefing Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Machine Registry
    Route::get('/machines', [MachineController::class, 'index'])->name('machines.index');
    Route::get('/machines/create', [MachineController::class, 'create'])->name('machines.create');
    Route::post('/machines', [MachineController::class, 'store'])->name('machines.store');
    Route::get('/machines/{machine}', [MachineController::class, 'show'])->name('machines.show');
    Route::get('/machines/{machine}/edit', [MachineController::class, 'edit'])->name('machines.edit');
    Route::put('/machines/{machine}', [MachineController::class, 'update'])->name('machines.update');
    Route::delete('/machines/{machine}', [MachineController::class, 'destroy'])->name('machines.destroy');

    // Permanent Machine Passport QR Code
    Route::post('/machines/{machine}/qr/generate', [MachineQrCodeController::class, 'generate'])->name('machines.qr.generate');
    Route::get('/machines/{machine}/qr/download', [MachineQrCodeController::class, 'download'])->name('machines.qr.download');
    Route::get('/machines/{machine}/qr/print', [MachineQrCodeController::class, 'print'])->name('machines.qr.print');

    // Machine Document Links (Library ISO Integration)
    Route::get('/machines/{machine}/documents', [MachineDocumentLinkController::class, 'indexLinks'])->name('machines.documents.index');
    Route::post('/machines/{machine}/documents', [MachineDocumentLinkController::class, 'storeLink'])->name('machines.documents.store');
    Route::put('/machines/{machine}/documents/{document}', [MachineDocumentLinkController::class, 'updateLink'])->name('machines.documents.update');
    Route::delete('/machines/{machine}/documents/{document}', [MachineDocumentLinkController::class, 'destroyLink'])->name('machines.documents.destroy');
    
    Route::get('/machines/{machine}/photos', [MachineDocumentPhotoController::class, 'indexPhotos'])->name('machines.photos.index');
    Route::post('/machines/{machine}/photos', [MachineDocumentPhotoController::class, 'storePhoto'])->name('machines.photos.store');
    Route::put('/machines/{machine}/photos/{photo}', [MachineDocumentPhotoController::class, 'updatePhoto'])->name('machines.photos.update');
    Route::delete('/machines/{machine}/photos/{photo}', [MachineDocumentPhotoController::class, 'destroyPhoto'])->name('machines.photos.destroy');
    Route::post('/machines/{machine}/photos/{photo}/rotate', [MachineDocumentPhotoController::class, 'rotatePhoto'])->name('machines.photos.rotate');

    // Machine Spareparts Mapping
    Route::get('/machines/{machine}/spareparts/search', [MachineSparepartController::class, 'search'])->name('machines.spareparts.search');
    Route::post('/machines/{machine}/spareparts', [MachineSparepartController::class, 'store'])->name('machines.spareparts.store');
    Route::put('/machines/{machine}/spareparts/{mapping}', [MachineSparepartController::class, 'update'])->name('machines.spareparts.update');
    Route::delete('/machines/{machine}/spareparts/{mapping}', [MachineSparepartController::class, 'destroy'])->name('machines.spareparts.destroy');



    // Breakdowns & Downtime
    Route::get('/breakdowns', [BreakdownController::class, 'index'])->name('breakdowns.index');
    Route::get('/breakdowns/{plan}', [MaintenancePlanController::class, 'show'])->name('breakdowns.show');

    // Spareparts Integration
    Route::get('/spareparts', [SparepartIntegrationController::class, 'index'])->name('spareparts.index');
    Route::get('/spareparts/unmapped-machines', [SparepartIntegrationController::class, 'unmappedMachines'])->name('spareparts.unmapped-machines');
    Route::get('/spareparts/{code}', [SparepartIntegrationController::class, 'show'])->name('spareparts.show');

    // Planning
    Route::get('/planning', [MaintenancePlanController::class, 'index'])->name('planning.index');
    Route::get('/planning/create', [MaintenancePlanController::class, 'create'])->name('planning.create');
    Route::post('/planning', [MaintenancePlanController::class, 'store'])->name('planning.store');
    Route::get('/planning/breakdown/report', [MaintenancePlanController::class, 'reportBreakdown'])->name('planning.report-breakdown');
    Route::post('/planning/breakdowns', [MaintenancePlanController::class, 'storeBreakdown'])->name('planning.store-breakdown');
    Route::post('/planning/{plan}/assign', [MaintenancePlanController::class, 'assignTechnician'])->name('planning.assign-technician');
    Route::get('/planning/{plan}', [MaintenancePlanController::class, 'show'])->name('planning.show');
    Route::put('/planning/{plan}', [MaintenancePlanController::class, 'update'])->name('planning.update');
    Route::post('/planning/{plan}/cancel', [MaintenancePlanController::class, 'cancel'])->name('planning.cancel');
    Route::get('/planning/cancel/autocomplete', [MaintenancePlanController::class, 'autocompleteReplacements'])->name('planning.autocomplete-replacements');

    // Preventive Maintenance Workspace
    Route::get('/preventive-maintenance', [MaintenancePlanController::class, 'preventiveIndex'])->name('preventive.index');
    Route::get('/preventive-maintenance/create', [MaintenancePlanController::class, 'create'])->name('preventive.create');
    Route::get('/preventive-maintenance/{plan}', [MaintenancePlanController::class, 'show'])->name('preventive.show');

    // Mobile/QR Checklist Execution
    Route::get('/machines/qr/{machineCode}/execute', [MaintenanceExecutionController::class, 'qrEntry'])->name('planning.qr-entry');
    Route::get('/planning/{plan}/execute', [MaintenanceExecutionController::class, 'create'])->name('planning.execute');
    Route::post('/planning/{plan}/execute', [MaintenanceExecutionController::class, 'store'])->name('planning.store-execute');
    Route::get('/planning/{plan}/print', [MaintenanceExecutionController::class, 'print'])->name('planning.print');
    Route::get('/planning/{plan}/report', [MaintenanceExecutionController::class, 'report'])->name('planning.report');

    // Reports
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    Route::get('/admin', function () {
        abort_unless(auth()->user()->can('employee.view') || auth()->user()->can('admin.manage.users'), 403);
        $employees = \App\Models\Employee::with(['department', 'position', 'user'])->orderBy('full_name')->get();
        $users = \App\Models\User::with(['roles', 'employee'])->orderBy('name')->get();
        $departments = \App\Models\MasterDepartment::orderBy('sort_order')->get();
        $positions = \App\Models\MasterPosition::orderBy('sort_order')->get();
        $categories = \App\Models\MasterMachineCategory::orderBy('sort_order')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        
        $unlinkedUsers = \App\Models\User::whereDoesntHave('employee')->orderBy('name')->get();
        $unlinkedEmployees = \App\Models\Employee::whereNull('linked_user_id')->orderBy('full_name')->get();

        return view('admin.index', compact(
            'employees', 'users', 'departments', 'positions', 'categories', 'roles', 'unlinkedUsers', 'unlinkedEmployees'
        ));
    })->name('admin.index');

    Route::post('/admin/employees', function (Illuminate\Http\Request $request, \App\Services\EmployeeNumberService $empNumService) {
        abort_unless(auth()->user()->can('employee.manage'), 403);
        $validated = $request->validate([
            'employee_number' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'department_id' => 'required|exists:master_departments,id',
            'position_id' => 'required|exists:master_positions,id',
            'employment_status' => 'required|string|in:ACTIVE,RESIGNED,RETIRED,TRANSFERRED,LEAVE',
            'employment_start_date' => 'required|date',
            'employment_end_date' => 'nullable|date',
            'is_assignable' => 'boolean',
            'primary_skill' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'linked_user_id' => 'nullable|exists:users,id|unique:employees,linked_user_id',
            'remarks' => 'nullable|string',
        ]);

        $isAssignable = $request->boolean('is_assignable');
        
        $res = $empNumService->generateNextCode($validated['employee_number']);

        $employee = \App\Models\Employee::create(array_merge($validated, [
            'employee_index' => $res['employee_index'],
            'employee_code' => $res['employee_code'],
            'is_assignable' => $isAssignable,
        ]));

        return redirect()->route('admin.index')->with('success', "Karyawan {$employee->full_name} berhasil didaftarkan dengan kode {$employee->employee_code}.");
    })->name('admin.employees.store');

    Route::put('/admin/employees/{employee}', function (\App\Models\Employee $employee, Illuminate\Http\Request $request, \App\Services\EmployeeNumberService $empNumService) {
        abort_unless(auth()->user()->can('employee.manage'), 403);
        $validated = $request->validate([
            'employee_number' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'department_id' => 'required|exists:master_departments,id',
            'position_id' => 'required|exists:master_positions,id',
            'employment_status' => 'required|string|in:ACTIVE,RESIGNED,RETIRED,TRANSFERRED,LEAVE',
            'employment_start_date' => 'required|date',
            'employment_end_date' => 'nullable|date',
            'is_assignable' => 'boolean',
            'primary_skill' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'linked_user_id' => 'nullable|exists:users,id|unique:employees,linked_user_id,' . $employee->id,
            'remarks' => 'nullable|string',
        ]);

        $isAssignable = $request->boolean('is_assignable');

        if ($employee->employee_number !== $validated['employee_number']) {
            $res = $empNumService->generateNextCode($validated['employee_number'], $employee->id);
            $employee->employee_index = $res['employee_index'];
            $employee->employee_code = $res['employee_code'];
        }

        $employee->update(array_merge($validated, [
            'is_assignable' => $isAssignable,
        ]));

        return redirect()->route('admin.index')->with('success', "Data karyawan {$employee->full_name} berhasil diperbarui.");
    })->name('admin.employees.update');

    Route::post('/admin/users', function (Illuminate\Http\Request $request) {
        abort_unless(auth()->user()->can('admin.manage.users'), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'linked_employee_id' => 'nullable|exists:employees,id',
        ]);
        
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);
        
        $user->assignRole($validated['role']);
        
        if (!empty($validated['linked_employee_id'])) {
            $emp = \App\Models\Employee::findOrFail($validated['linked_employee_id']);
            $emp->update(['linked_user_id' => $user->id]);
        }
        
        return redirect()->route('admin.index')->with('success', "Akun login {$user->email} berhasil ditambahkan.");
    })->name('admin.users.store');

    Route::put('/admin/users/{user}', function (\App\Models\User $user, Illuminate\Http\Request $request) {
        abort_unless(auth()->user()->can('admin.manage.users'), 403);
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string|exists:roles,name',
            'linked_employee_id' => 'nullable|exists:employees,id',
        ]);

        $user->update([
            'email' => $validated['email'],
        ]);

        $user->syncRoles([$validated['role']]);

        \App\Models\Employee::where('linked_user_id', $user->id)->update(['linked_user_id' => null]);

        if (!empty($validated['linked_employee_id'])) {
            $emp = \App\Models\Employee::findOrFail($validated['linked_employee_id']);
            $emp->update(['linked_user_id' => $user->id]);
        }

        return redirect()->route('admin.index')->with('success', "Akun login {$user->email} berhasil diperbarui.");
    })->name('admin.users.update');

    Route::delete('/admin/users/{user}', function (\App\Models\User $user) {
        abort_unless(auth()->user()->can('admin.manage.users'), 403);
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        $user->delete();
        return redirect()->route('admin.index')->with('success', 'Akun login berhasil dihapus.');
    })->name('admin.users.destroy');

    // Procurement Workflow Module
    Route::get('procurements/{procurement}/print', [ProcurementCaseController::class, 'print'])->name('procurements.print');
    Route::resource('procurements', ProcurementCaseController::class);
    Route::post('procurements/{procurement}/submit', [ProcurementCaseController::class, 'submit'])->name('procurements.submit');
    Route::post('procurements/{procurement}/approve-stage-1', [ProcurementCaseController::class, 'approveStage1'])->name('procurements.approve-stage-1');
    Route::post('procurements/{procurement}/approve-stage-2', [ProcurementCaseController::class, 'approveStage2'])->name('procurements.approve-stage-2');
    Route::post('procurements/{procurement}/return-information', [ProcurementCaseController::class, 'returnInformation'])->name('procurements.return-information');
    Route::post('procurements/{procurement}/update-information', [ProcurementCaseController::class, 'updateInformation'])->name('procurements.update-information');
    Route::post('procurements/{procurement}/input-po', [ProcurementCaseController::class, 'inputPo'])->name('procurements.input-po');
    Route::post('procurements/{procurement}/confirm-arrival', [ProcurementCaseController::class, 'confirmArrival'])->name('procurements.confirm-arrival');
    Route::post('procurements/{procurement}/confirm-pickup', [ProcurementCaseController::class, 'confirmPickup'])->name('procurements.confirm-pickup');
    Route::post('procurements/{procurement}/cancel', [ProcurementCaseController::class, 'cancel'])->name('procurements.cancel');
    Route::post('procurements/{procurement}/reject', [ProcurementCaseController::class, 'reject'])->name('procurements.reject');
    
    // Attachment routes
    Route::post('procurements/{procurement}/attachments', [ProcurementCaseController::class, 'uploadAttachment'])->name('procurements.attachments.upload');
    Route::delete('procurements/attachments/{attachment}', [ProcurementCaseController::class, 'deleteAttachment'])->name('procurements.attachments.destroy');
    Route::get('procurements/attachments/{attachment}/download', [ProcurementCaseController::class, 'downloadAttachment'])->name('procurements.attachments.download');
});
