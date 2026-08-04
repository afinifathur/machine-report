<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\ProcurementCase;
use App\Services\ProcurementWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProcurementCaseController extends Controller
{
    use AuthorizesRequests;

    protected ProcurementWorkflowService $workflowService;

    public function __construct(ProcurementWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ProcurementCase::class);

        $query = ProcurementCase::with(['machine', 'creator', 'category']);

        // Search (Global search matching Case Number, Item Name, Machine Name, Category Name, Reason, Current Owner)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('current_owner', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('machine', function ($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('category', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtering by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtering by Status Group (from summary card clicks)
        if ($request->filled('status_group')) {
            $group = $request->input('status_group');
            if ($group === 'draft') {
                $query->where('status', \App\Enums\ProcurementStatus::DRAFT);
            } elseif ($group === 'pending_approval') {
                $query->whereIn('status', [
                    \App\Enums\ProcurementStatus::PENDING_KABAG,
                    \App\Enums\ProcurementStatus::PENDING_DIR,
                    \App\Enums\ProcurementStatus::NEED_INFO
                ]);
            } elseif ($group === 'processing') {
                $query->whereIn('status', [
                    \App\Enums\ProcurementStatus::PROCESSING,
                    \App\Enums\ProcurementStatus::WAITING_DELIVERY
                ]);
            } elseif ($group === 'ready_pickup') {
                $query->where('status', \App\Enums\ProcurementStatus::READY_TO_PICKUP);
            } elseif ($group === 'closed') {
                $query->whereIn('status', [
                    \App\Enums\ProcurementStatus::CLOSED,
                    \App\Enums\ProcurementStatus::CANCELLED
                ]);
            }
        }

        // Filtering by Urgency
        if ($request->filled('urgency')) {
            $query->where('urgency', $request->input('urgency'));
        }

        // Filtering by Category
        if ($request->filled('category')) {
            $query->where('procurement_category_id', $request->input('category'));
        }

        // Filtering by Current Owner
        if ($request->filled('owner')) {
            $query->where('current_owner', $request->input('owner'));
        }

        // Filtering by My Cases (checks if current owner matches logged in user's roles)
        if ($request->boolean('my_cases')) {
            $user = auth()->user();
            if ($user) {
                $userRoles = $user->roles->pluck('name')->toArray();
                $query->whereIn('current_owner', $userRoles);
            }
        }

        // Summary Counts (calculating dynamic card counts based on search / other filters applied)
        $countQuery = ProcurementCase::query();
        if ($request->filled('search')) {
            $search = $request->input('search');
            $countQuery->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('current_owner', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('machine', function ($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('category', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('urgency')) {
            $countQuery->where('urgency', $request->input('urgency'));
        }
        if ($request->filled('category')) {
            $countQuery->where('procurement_category_id', $request->input('category'));
        }
        if ($request->filled('owner')) {
            $countQuery->where('current_owner', $request->input('owner'));
        }
        if ($request->boolean('my_cases')) {
            $user = auth()->user();
            if ($user) {
                $userRoles = $user->roles->pluck('name')->toArray();
                $countQuery->whereIn('current_owner', $userRoles);
            }
        }

        $draftCount = (clone $countQuery)->where('status', \App\Enums\ProcurementStatus::DRAFT)->count();
        $pendingCount = (clone $countQuery)->whereIn('status', [
            \App\Enums\ProcurementStatus::PENDING_KABAG,
            \App\Enums\ProcurementStatus::PENDING_DIR,
            \App\Enums\ProcurementStatus::NEED_INFO
        ])->count();
        $processingCount = (clone $countQuery)->whereIn('status', [
            \App\Enums\ProcurementStatus::PROCESSING,
            \App\Enums\ProcurementStatus::WAITING_DELIVERY
        ])->count();
        $readyCount = (clone $countQuery)->where('status', \App\Enums\ProcurementStatus::READY_TO_PICKUP)->count();
        $closedCount = (clone $countQuery)->whereIn('status', [
            \App\Enums\ProcurementStatus::CLOSED,
            \App\Enums\ProcurementStatus::CANCELLED
        ])->count();

        $cases = $query->latest()->paginate(20)->withQueryString();

        $categories = \App\Models\ProcurementCategory::where('is_active', true)->orderBy('name')->get();
        $owners = ProcurementCase::select('current_owner')->distinct()->pluck('current_owner');

        return view('procurements.index', compact(
            'cases', 'categories', 'owners',
            'draftCount', 'pendingCount', 'processingCount', 'readyCount', 'closedCount'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', ProcurementCase::class);

        $machines = Machine::orderBy('name')->get();
        $categories = \App\Models\ProcurementCategory::where('is_active', true)->orderBy('name')->get();

        return view('procurements.create', compact('machines', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ProcurementCase::class);

        $validated = $request->validate([
            'machine_id'                => 'required|exists:machines,id',
            'item_name'                 => 'required|string|max:255',
            'procurement_category_id'   => 'required|exists:procurement_categories,id',
            'urgency'                   => 'required|string|in:normal,urgent,emergency',
            'target_needed_date'        => 'required|date',
            'machine_down'              => 'required|boolean',
            'description'               => 'required|string',
            'reason'                    => 'required|string',
            // Optional attachments on create
            'attachments'               => 'nullable|array|max:10',
            'attachments.*'             => 'file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $user = auth()->user() ?? \App\Models\User::first();

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'User session not found.']);
        }

        $case = $this->workflowService->createDraft($validated, $user);

        // Save any attachments uploaded together with the draft
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $mimeType     = $file->getMimeType();
                $originalName = $file->getClientOriginalName();

                if (str_starts_with($mimeType, 'image/')) {
                    $extension = $file->getClientOriginalExtension() ?: 'jpg';
                    $storedFilename   = \Illuminate\Support\Str::random(40) . '.' . $extension;
                    $destinationPath  = \Illuminate\Support\Facades\Storage::disk('public')->path('procurements/' . $storedFilename);

                    if (!file_exists(dirname($destinationPath))) {
                        mkdir(dirname($destinationPath), 0755, true);
                    }

                    $this->resizeAndCompressImage($file->getRealPath(), $destinationPath, $mimeType);
                    $fileSize = filesize($destinationPath);
                } else {
                    $path           = $file->store('procurements', 'public');
                    $storedFilename = basename($path);
                    $fileSize       = $file->getSize();
                }

                $case->attachments()->create([
                    'uploaded_by'       => $user->id,
                    'original_filename' => $originalName,
                    'stored_filename'   => $storedFilename,
                    'mime_type'         => $mimeType,
                    'file_size'         => $fileSize,
                ]);
            }
        }

        return redirect()->route('procurements.show', $case->id)
            ->with('success', 'Draft pengadaan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProcurementCase $procurement)
    {
        $this->authorize('view', $procurement);

        return view('procurements.show', compact('procurement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProcurementCase $procurement)
    {
        $this->authorize('update', $procurement);

        $machines = Machine::orderBy('name')->get();
        $categories = \App\Models\ProcurementCategory::where('is_active', true)->orderBy('name')->get();

        return view('procurements.edit', compact('procurement', 'machines', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('update', $procurement);

        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'item_name' => 'required|string|max:255',
            'procurement_category_id' => 'required|exists:procurement_categories,id',
            'urgency' => 'required|string|in:normal,urgent,emergency',
            'target_needed_date' => 'required|date',
            'machine_down' => 'required|boolean',
            'description' => 'required|string',
            'reason' => 'required|string',
        ]);

        try {
            $this->workflowService->updateDraft($procurement, $validated);

            if ($request->input('action') === 'submit') {
                $this->workflowService->submit($procurement);
                return redirect()->route('procurements.show', $procurement->id)
                    ->with('success', 'Draft pengadaan berhasil diperbarui dan diajukan ke Kabag Maintenance.');
            }

            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Draft pengadaan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProcurementCase $procurement)
    {
        $this->authorize('delete', $procurement);

        try {
            $this->workflowService->deleteDraft($procurement);
            return redirect()->route('procurements.index')
                ->with('success', 'Draft pengadaan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Submit the procurement case to Kabag Maintenance.
     */
    public function submit(ProcurementCase $procurement)
    {
        $this->authorize('submit', $procurement);

        try {
            $this->workflowService->submit($procurement);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Pengadaan berhasil diajukan ke Kabag Maintenance.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve Stage 1 (Kabag).
     */
    public function approveStage1(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('approveStage1', $procurement);

        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $user = auth()->user() ?? \App\Models\User::first();
            $this->workflowService->approveStage1($procurement, $user, $validated['note'] ?? null);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Persetujuan Stage 1 (Kabag) berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve Stage 2 (Direktur).
     */
    public function approveStage2(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('approveStage2', $procurement);

        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $user = auth()->user() ?? \App\Models\User::first();
            $this->workflowService->approveStage2($procurement, $user, $validated['note'] ?? null);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Persetujuan Stage 2 (Direktur) berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Return for information.
     */
    public function returnInformation(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('returnForInformation', $procurement);

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        try {
            $user = auth()->user() ?? \App\Models\User::first();
            $this->workflowService->returnForInformation($procurement, $user, $validated['note']);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Permintaan berhasil dikembalikan untuk informasi tambahan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update information (after NEED_INFO status).
     */
    public function updateInformation(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('updateInformation', $procurement);

        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'item_name' => 'required|string|max:255',
            'procurement_category_id' => 'required|exists:procurement_categories,id',
            'urgency' => 'required|string|in:normal,urgent,emergency',
            'target_needed_date' => 'required|date',
            'machine_down' => 'required|boolean',
            'description' => 'required|string',
            'reason' => 'required|string',
        ]);

        try {
            $this->workflowService->updateInformation($procurement, $validated);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Informasi pengadaan diperbarui dan diajukan ulang.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Input PO.
     */
    public function inputPo(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('inputPO', $procurement);

        $validated = $request->validate([
            'po_number' => 'required|string|max:255',
            'vendor_name' => 'required|string|max:255',
            'po_date' => 'required|date',
        ]);

        try {
            $this->workflowService->inputPO($procurement, $validated['po_number'], $validated['vendor_name'], $validated['po_date']);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Informasi PO berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Confirm Arrival.
     */
    public function confirmArrival(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('confirmArrival', $procurement);

        $validated = $request->validate([
            'rack_location' => 'required|string|max:255',
        ]);

        try {
            $this->workflowService->confirmArrival($procurement, $validated['rack_location']);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Penerimaan barang berhasil dikonfirmasi.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Confirm Pickup.
     */
    public function confirmPickup(ProcurementCase $procurement)
    {
        $this->authorize('confirmPickup', $procurement);

        try {
            $this->workflowService->confirmPickup($procurement);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Pengambilan barang dikonfirmasi. Workflow selesai.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel.
     */
    public function cancel(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('cancel', $procurement);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $user = auth()->user() ?? \App\Models\User::first();
            $this->workflowService->cancel($procurement, $validated['reason'], $user);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Permintaan pengadaan telah dibatalkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Upload an attachment for the case.
     */
    public function uploadAttachment(Request $request, ProcurementCase $procurement)
    {
        $this->authorize('uploadAttachment', $procurement);

        if ($procurement->attachments()->count() >= 10) {
            return redirect()->back()->withErrors(['file' => 'Maksimal lampiran adalah 10 file per kasus.']);
        }

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $originalName = $file->getClientOriginalName();

        $user = auth()->user() ?? \App\Models\User::first();

        // Check if it's an image
        if (str_starts_with($mimeType, 'image/')) {
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = match ($mimeType) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => 'jpg'
                };
            }
            $storedFilename = \Illuminate\Support\Str::random(40) . '.' . $extension;
            $destinationPath = \Illuminate\Support\Facades\Storage::disk('public')->path('procurements/' . $storedFilename);

            if (!file_exists(dirname($destinationPath))) {
                mkdir(dirname($destinationPath), 0755, true);
            }

            // Resize and compress
            $this->resizeAndCompressImage($file->getRealPath(), $destinationPath, $mimeType);
            
            $fileSize = filesize($destinationPath);
        } else {
            // PDF or other non-image files
            $path = $file->store('procurements', 'public');
            $storedFilename = basename($path);
            $fileSize = $file->getSize();
        }

        $procurement->attachments()->create([
            'uploaded_by' => $user->id,
            'original_filename' => $originalName,
            'stored_filename' => $storedFilename,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
        ]);

        return redirect()->route('procurements.show', $procurement->id)
            ->with('success', 'File lampiran berhasil diunggah.');
    }

    /**
     * Helper to resize and compress image using GD.
     */
    private function resizeAndCompressImage($sourcePath, $destinationPath, $mimeType)
    {
        if (!extension_loaded('gd')) {
            copy($sourcePath, $destinationPath);
            return;
        }

        // Get original dimensions
        list($origWidth, $origHeight) = @getimagesize($sourcePath);
        if (!$origWidth || !$origHeight) {
            copy($sourcePath, $destinationPath);
            return;
        }

        // Resize maximum dimension to 1600 px
        $maxDimension = 1600;
        $width = $origWidth;
        $height = $origHeight;

        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth > $origHeight) {
                $width = $maxDimension;
                $height = intval($origHeight * ($maxDimension / $origWidth));
            } else {
                $height = $maxDimension;
                $width = intval($origWidth * ($maxDimension / $origHeight));
            }
        }

        // Load image based on mime type
        $srcImage = null;
        try {
            if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
                $srcImage = @imagecreatefromjpeg($sourcePath);
            } elseif ($mimeType === 'image/png') {
                $srcImage = @imagecreatefrompng($sourcePath);
            } elseif ($mimeType === 'image/webp') {
                $srcImage = @imagecreatefromwebp($sourcePath);
            }
        } catch (\Exception $e) {
            $srcImage = null;
        }

        if (!$srcImage) {
            copy($sourcePath, $destinationPath);
            return;
        }

        // Create new blank image
        $dstImage = imagecreatetruecolor($width, $height);

        // Preserve transparency for PNG and WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $width, $height, $transparent);
        }

        // Copy and resize
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

        // Compress and save
        if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
            imagejpeg($dstImage, $destinationPath, 75); // 75 quality is optimal
        } elseif ($mimeType === 'image/png') {
            // PNG compression level (0-9)
            imagepng($dstImage, $destinationPath, 7);
        } elseif ($mimeType === 'image/webp') {
            imagewebp($dstImage, $destinationPath, 75);
        } else {
            copy($sourcePath, $destinationPath);
        }

        // Free memory
        imagedestroy($srcImage);
        imagedestroy($dstImage);
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(\App\Models\ProcurementAttachment $attachment)
    {
        $this->authorize('deleteAttachment', $attachment);

        \Illuminate\Support\Facades\Storage::disk('public')->delete('procurements/' . $attachment->stored_filename);
        
        $caseId = $attachment->procurement_case_id;
        $attachment->delete();

        return redirect()->route('procurements.show', $caseId)
            ->with('success', 'File lampiran berhasil dihapus.');
    }

    /**
     * Download/View an attachment.
     */
    public function downloadAttachment(\App\Models\ProcurementAttachment $attachment)
    {
        $this->authorize('view', $attachment->case);

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            'procurements/' . $attachment->stored_filename,
            $attachment->original_filename
        );
    }

    /**
     * Reject a case and return to Draft.
     */
    public function reject(Request $request, ProcurementCase $procurement)
    {
        if ($procurement->status === \App\Enums\ProcurementStatus::PENDING_KABAG) {
            $this->authorize('approveStage1', $procurement);
        } elseif ($procurement->status === \App\Enums\ProcurementStatus::PENDING_DIR) {
            $this->authorize('approveStage2', $procurement);
        } else {
            return redirect()->back()->withErrors(['error' => 'Aksi reject tidak diizinkan pada status ini.']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $user = auth()->user() ?? \App\Models\User::first();
            $this->workflowService->reject($procurement, $user, $validated['reason']);
            return redirect()->route('procurements.show', $procurement->id)
                ->with('success', 'Permintaan pengadaan telah ditolak dan dikembalikan ke Draft.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Render printable Procurement Case Report (PDF).
     */
    public function print(ProcurementCase $procurement, \App\Services\DocumentPdfService $pdfService)
    {
        $this->authorize('view', $procurement);

        $pdfContent = $pdfService->generateProcurementCase($procurement);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="procurement_report_' . $procurement->case_number . '.pdf"',
        ]);
    }
}
