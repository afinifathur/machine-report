<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineDocument;
use App\Models\MachinePhoto;
use App\Services\ImageCompressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MachineDocumentPhotoController extends Controller
{
    protected ImageCompressionService $compressionService;

    public function __construct(ImageCompressionService $compressionService)
    {
        $this->compressionService = $compressionService;
    }

    /**
     * Store or replace a machine document.
     */
    public function storeDocument(Request $request, string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();

        $allowedTypes = [
            'manual_book',
            'electrical_diagram',
            'hydraulic_diagram',
            'pneumatic_diagram',
            'plc_backup',
            'parameter_backup',
            'certificate',
            'vendor_document'
        ];

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', Rule::in($allowedTypes)],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,zip,rar', 'max:51200'], // max 50MB
        ], [
            'file.mimes' => 'Format file dokumen tidak didukung. Gunakan pdf, doc, docx, xls, xlsx, zip, atau rar.',
            'file.max' => 'Ukuran file dokumen maksimal adalah 50 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->input('type');
        $file = $request->file('file');

        // Get extension and original filename
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
        $originalName = $file->getClientOriginalName();

        // Target path under storage/app/public/machines/{code}/documents/{type}.{ext}
        $targetFileName = "{$type}.{$ext}";
        $relativeDir = "machines/{$code}/documents";
        $relativePath = "{$relativeDir}/{$targetFileName}";

        // Check if there is an existing document in this slot
        $existingDoc = $machine->documents()->where('type', $type)->first();
        if ($existingDoc) {
            // Delete old file from storage
            if (Storage::disk('public')->exists($existingDoc->file_path)) {
                Storage::disk('public')->delete($existingDoc->file_path);
            }
        }

        // Store new file
        $path = Storage::disk('public')->putFileAs($relativeDir, $file, $targetFileName);

        // Update or create DB record
        $document = $machine->documents()->updateOrCreate(
            ['type' => $type],
            [
                'file_name' => $originalName,
                'file_path' => $relativePath,
            ]
        );

        // Refresh machine to compute progress correctly
        $machine->load('documents', 'photos');

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diunggah.',
            'document' => [
                'type' => $document->type,
                'file_name' => $document->file_name,
                'file_path' => asset('storage/' . $document->file_path),
                'formatted_size' => $document->formatted_size,
                'formatted_upload_date' => $document->formatted_upload_date,
            ],
            'completion_progress' => $machine->completion_progress,
        ]);
    }

    /**
     * Download a machine document.
     */
    public function downloadDocument(string $code, string $type)
    {
        $machine = Machine::where('code', $code)->firstOrFail();
        $document = $machine->documents()->where('type', $type)->firstOrFail();

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Delete a machine document (clears the slot).
     */
    public function destroyDocument(string $code, string $type)
    {
        $machine = Machine::where('code', $code)->firstOrFail();
        $document = $machine->documents()->where('type', $type)->firstOrFail();

        // Delete physical file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete database record
        $document->delete();

        // Refresh machine to compute progress correctly
        $machine->load('documents', 'photos');

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dihapus.',
            'completion_progress' => $machine->completion_progress,
        ]);
    }

    /**
     * Get paginated, filtered, searched, and sorted photos for a machine (AJAX/JSON).
     */
    public function indexPhotos(Request $request, string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();

        $query = $machine->photos()->with('uploader');

        // Category filter
        $category = $request->input('category', 'all');
        if ($category !== 'all' && !empty($category)) {
            $query->where(function ($q) use ($category) {
                $q->where('photo_type', $category)
                  ->orWhere('type', $category);
            });
        }

        // Search title & description
        $search = $request->input('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'oldest' => $query->oldest('created_at'),
            'title_asc' => $query->orderBy('title', 'asc'),
            'title_desc' => $query->orderBy('title', 'desc'),
            'manual' => $query->orderBy('sort_order', 'asc')->latest('created_at'),
            default => $query->latest('created_at'),
        };

        $perPage = (int) $request->input('per_page', 50);
        $photos = $query->paginate($perPage);

        $latestPhoto = $machine->photos()->latest('created_at')->first();
        $latestUploadDate = $latestPhoto ? $latestPhoto->formatted_upload_date : '-';

        return response()->json([
            'success' => true,
            'total_count' => $machine->photos()->count(),
            'filtered_count' => $photos->total(),
            'latest_upload' => $latestUploadDate,
            'photos' => $photos->items(),
            'pagination' => [
                'current_page' => $photos->currentPage(),
                'last_page' => $photos->lastPage(),
                'total' => $photos->total(),
                'per_page' => $photos->perPage(),
            ],
        ]);
    }

    /**
     * Store a new machine photo.
     */
    public function storePhoto(Request $request, string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();

        $allowedCategories = ['reference', 'name_plate', 'inspection', 'breakdown', 'repair', 'other'];
        $legacyTypes = ['overall', 'electrical_cabinet', 'hydraulic_unit', 'control_panel', 'before_repair', 'after_repair'];
        $allAllowed = array_merge($allowedCategories, $legacyTypes);

        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'photo_type' => ['nullable', 'string', Rule::in($allAllowed)],
            'type' => ['nullable', 'string', Rule::in($allAllowed)],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'], // max 10MB
        ], [
            'file.required' => 'File foto wajib diunggah.',
            'file.image' => 'File harus berupa gambar.',
            'file.mimes' => 'Format gambar tidak didukung. Gunakan jpg, jpeg, png, atau webp.',
            'file.max' => 'Ukuran gambar sebelum kompresi maksimal adalah 10 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $photoType = $request->input('photo_type') ?: $request->input('type') ?: 'other';
        $title = $request->input('title') ?: ucfirst(str_replace('_', ' ', $photoType));
        $description = $request->input('description');
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        $directory = "machines/{$code}/photos";

        try {
            $compressedResult = $this->compressionService->compressAndStoreGalleryPhoto($file, $directory);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses dan mengompresi gambar: ' . $e->getMessage(),
            ], 500);
        }

        $photo = $machine->photos()->create([
            'title' => $title,
            'photo_type' => $photoType,
            'type' => $photoType,
            'description' => $description,
            'file_name' => $originalName,
            'file_path' => $compressedResult['file_path'],
            'uploaded_by' => auth()->id(),
            'taken_at' => now(),
        ]);

        $machine->load('documents', 'photos');

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil diunggah dan dikompresi.',
            'photo' => $photo,
            'completion_progress' => $machine->completion_progress,
        ]);
    }

    /**
     * Update metadata (title, category, description) of a photo.
     */
    public function updatePhoto(Request $request, string $code, $photoId)
    {
        $machine = Machine::where('code', $code)->firstOrFail();
        $photo = $machine->photos()->where('id', $photoId)->firstOrFail();

        $allowedCategories = ['reference', 'name_plate', 'inspection', 'breakdown', 'repair', 'other'];

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'photo_type' => ['required', 'string', Rule::in($allowedCategories)],
            'description' => ['nullable', 'string'],
        ], [
            'title.required' => 'Judul foto wajib diisi.',
            'photo_type.required' => 'Kategori foto wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $photo->update([
            'title' => $request->input('title'),
            'photo_type' => $request->input('photo_type'),
            'type' => $request->input('photo_type'),
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metadata foto berhasil diperbarui.',
            'photo' => $photo,
        ]);
    }

    /**
     * Delete a machine photo by ID or legacy type string.
     */
    public function destroyPhoto(string $code, $photoIdentifier)
    {
        $machine = Machine::where('code', $code)->firstOrFail();

        if (is_numeric($photoIdentifier)) {
            $photo = $machine->photos()->where('id', $photoIdentifier)->firstOrFail();
        } else {
            $photo = $machine->photos()->where(function ($q) use ($photoIdentifier) {
                $q->where('photo_type', $photoIdentifier)->orWhere('type', $photoIdentifier);
            })->firstOrFail();
        }

        // Delete physical main file
        if (Storage::disk('public')->exists($photo->file_path)) {
            Storage::disk('public')->delete($photo->file_path);
        }

        // Delete thumbnail file if exists
        $dir = dirname($photo->file_path);
        $filename = basename($photo->file_path);
        $thumbPath = "{$dir}/thumbs/{$filename}";
        if (Storage::disk('public')->exists($thumbPath)) {
            Storage::disk('public')->delete($thumbPath);
        }

        $photo->delete();

        $machine->load('documents', 'photos');

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil dihapus.',
            'completion_progress' => $machine->completion_progress,
        ]);
    }
}
