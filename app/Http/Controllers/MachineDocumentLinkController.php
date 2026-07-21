<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineDocumentLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MachineDocumentLinkController extends Controller
{
    /**
     * Get all document links for a machine.
     */
    public function indexLinks(string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();

        $links = $machine->documentLinks()
            ->with('creator')
            ->get()
            ->map(function ($link) {
                return [
                    'id' => $link->id,
                    'title' => $link->title,
                    'document_category' => $link->document_category,
                    'category_label' => $link->category_label,
                    'library_url' => $link->library_url,
                    'description' => $link->description,
                    'formatted_created_at' => $link->created_at ? $link->created_at->translatedFormat('d F Y H:i') : null,
                    'creator_name' => $link->creator ? $link->creator->name : 'System',
                ];
            });

        $latestUpdate = $links->first() ? $links->first()['formatted_created_at'] : null;

        return response()->json([
            'success' => true,
            'total_count' => $links->count(),
            'latest_update' => $latestUpdate ?: '-',
            'links' => $links,
        ]);
    }

    /**
     * Store a new document link for a machine.
     */
    public function storeLink(Request $request, string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'document_category' => ['required', 'string', 'max:100'],
            'library_url' => [
                'required',
                'url',
                'max:500',
                Rule::unique('machine_document_links', 'library_url')->where('machine_id', $machine->id)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'title.required' => 'Judul dokumen wajib diisi.',
            'document_category.required' => 'Kategori dokumen wajib dipilih.',
            'library_url.required' => 'URL Library ISO wajib diisi.',
            'library_url.url' => 'Format URL tidak valid. Pastikan diawali dengan http:// atau https://.',
            'library_url.unique' => 'URL dokumen ini sudah terhubung dengan mesin ini.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $link = MachineDocumentLink::create([
            'machine_id' => $machine->id,
            'title' => $request->input('title'),
            'document_category' => $request->input('document_category'),
            'library_url' => $request->input('library_url'),
            'description' => $request->input('description'),
            'created_by' => Auth::id(),
        ]);

        $machine->load('documentLinks', 'photos');

        return response()->json([
            'success' => true,
            'message' => 'Referensi dokumen berhasil dihubungkan.',
            'link' => [
                'id' => $link->id,
                'title' => $link->title,
                'document_category' => $link->document_category,
                'category_label' => $link->category_label,
                'library_url' => $link->library_url,
                'description' => $link->description,
                'formatted_created_at' => $link->created_at->translatedFormat('d F Y H:i'),
            ],
            'completion_progress' => $machine->completion_progress,
        ]);
    }

    /**
     * Update an existing document link.
     */
    public function updateLink(Request $request, string $code, int $id)
    {
        $machine = Machine::where('code', $code)->firstOrFail();
        $link = $machine->documentLinks()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'document_category' => ['required', 'string', 'max:100'],
            'library_url' => [
                'required',
                'url',
                'max:500',
                Rule::unique('machine_document_links', 'library_url')
                    ->where('machine_id', $machine->id)
                    ->ignore($link->id)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'title.required' => 'Judul dokumen wajib diisi.',
            'document_category.required' => 'Kategori dokumen wajib dipilih.',
            'library_url.required' => 'URL Library ISO wajib diisi.',
            'library_url.url' => 'Format URL tidak valid.',
            'library_url.unique' => 'URL dokumen ini sudah terhubung dengan mesin ini.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $link->update([
            'title' => $request->input('title'),
            'document_category' => $request->input('document_category'),
            'library_url' => $request->input('library_url'),
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Referensi dokumen berhasil diperbarui.',
            'link' => [
                'id' => $link->id,
                'title' => $link->title,
                'document_category' => $link->document_category,
                'category_label' => $link->category_label,
                'library_url' => $link->library_url,
                'description' => $link->description,
            ],
        ]);
    }

    /**
     * Delete a document link from a machine.
     */
    public function destroyLink(string $code, int $id)
    {
        $machine = Machine::where('code', $code)->firstOrFail();
        $link = $machine->documentLinks()->findOrFail($id);

        $link->delete();

        $machine->load('documentLinks', 'photos');

        return response()->json([
            'success' => true,
            'message' => 'Referensi dokumen berhasil dilepas.',
            'completion_progress' => $machine->completion_progress,
        ]);
    }
}
