<?php

use Illuminate\Support\Facades\Route;

use App\Models\Machine;

use App\Http\Controllers\DashboardController;

// Morning Briefing Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineSparepartController;

// Machine Registry
Route::get('/machines', [MachineController::class, 'index'])->name('machines.index');
Route::get('/machines/create', [MachineController::class, 'create'])->name('machines.create');
Route::post('/machines', [MachineController::class, 'store'])->name('machines.store');
Route::get('/machines/{machine}', [MachineController::class, 'show'])->name('machines.show');
Route::get('/machines/{machine}/edit', [MachineController::class, 'edit'])->name('machines.edit');
Route::put('/machines/{machine}', [MachineController::class, 'update'])->name('machines.update');
Route::delete('/machines/{machine}', [MachineController::class, 'destroy'])->name('machines.destroy');

use App\Http\Controllers\MachineQrCodeController;

// Permanent Machine Passport QR Code
Route::post('/machines/{machine}/qr/generate', [MachineQrCodeController::class, 'generate'])->name('machines.qr.generate');
Route::get('/machines/{machine}/qr/download', [MachineQrCodeController::class, 'download'])->name('machines.qr.download');
Route::get('/machines/{machine}/qr/print', [MachineQrCodeController::class, 'print'])->name('machines.qr.print');

use App\Http\Controllers\MachineDocumentPhotoController;
use App\Http\Controllers\MachineDocumentLinkController;

// Machine Document Links (Library ISO Integration)
Route::get('/machines/{machine}/documents', [MachineDocumentLinkController::class, 'indexLinks'])->name('machines.documents.index');
Route::post('/machines/{machine}/documents', [MachineDocumentLinkController::class, 'storeLink'])->name('machines.documents.store');
Route::put('/machines/{machine}/documents/{document}', [MachineDocumentLinkController::class, 'updateLink'])->name('machines.documents.update');
Route::delete('/machines/{machine}/documents/{document}', [MachineDocumentLinkController::class, 'destroyLink'])->name('machines.documents.destroy');
Route::get('/machines/{machine}/photos', [MachineDocumentPhotoController::class, 'indexPhotos'])->name('machines.photos.index');
Route::post('/machines/{machine}/photos', [MachineDocumentPhotoController::class, 'storePhoto'])->name('machines.photos.store');
Route::put('/machines/{machine}/photos/{photo}', [MachineDocumentPhotoController::class, 'updatePhoto'])->name('machines.photos.update');
Route::delete('/machines/{machine}/photos/{photo}', [MachineDocumentPhotoController::class, 'destroyPhoto'])->name('machines.photos.destroy');

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

use App\Http\Controllers\MaintenancePlanController;
use App\Http\Controllers\MaintenanceExecutionController;

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
