<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineSparepartController;
use App\Http\Controllers\MachineQrCodeController;
use App\Http\Controllers\MachineDocumentPhotoController;
use App\Http\Controllers\MachineDocumentLinkController;
use App\Http\Controllers\MaintenancePlanController;
use App\Http\Controllers\MaintenanceExecutionController;
use App\Http\Controllers\ProcurementCaseController;

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
    Route::delete('/machines/{machine}/spareparts/{mapping}', [MachineSparepartController::class, 'destroy'])->name('machines.spareparts.destroy');

    // Maintenance Management
    Route::get('/maintenances', function () {
        return view('maintenances.index');
    })->name('maintenances.index');

    // Breakdowns & Downtime
    Route::get('/breakdowns', function () {
        return view('breakdowns.index');
    })->name('breakdowns.index');

    // Spareparts Integration
    Route::get('/spareparts', function () {
        return view('spareparts.index');
    })->name('spareparts.index');

    // Planning
    Route::get('/planning', [MaintenancePlanController::class, 'index'])->name('planning.index');
    Route::get('/planning/{plan}', [MaintenancePlanController::class, 'show'])->name('planning.show');

    // Mobile/QR Checklist Execution
    Route::get('/machines/qr/{machineCode}/execute', [MaintenanceExecutionController::class, 'qrEntry'])->name('planning.qr-entry');
    Route::get('/planning/{plan}/execute', [MaintenanceExecutionController::class, 'create'])->name('planning.execute');
    Route::post('/planning/{plan}/execute', [MaintenanceExecutionController::class, 'store'])->name('planning.store-execute');
    Route::get('/planning/{plan}/print', [MaintenanceExecutionController::class, 'print'])->name('planning.print');

    // Reports
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    // Administration
    Route::get('/admin', function () {
        return view('admin.index');
    })->name('admin.index');

    // Procurement Workflow Module
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
