@props([
    'attachments',
    'downloadRoute',
    'deleteRoute' => null,
    'deletePermission' => null,
    'storagePath' => 'storage/procurements/',
])

@php
    $images = $attachments->filter(fn($a) => str_starts_with($a->mime_type, 'image/'));
    $nonImages = $attachments->filter(fn($a) => !str_starts_with($a->mime_type, 'image/'));
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('vendor/glightbox/glightbox.min.css') }}" />
        <style>
            .gslide-title {
                font-family: inherit;
                font-weight: 700;
            }
            .gslide-desc {
                font-family: inherit;
                font-style: italic;
            }
        </style>
    @endpush
    @push('scripts')
        <script src="{{ asset('vendor/glightbox/glightbox.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                GLightbox({
                    selector: '.glightbox-gallery',
                    touchNavigation: true,
                    loop: true,
                    zoomable: true,
                    draggable: true
                });
            });
        </script>
    @endpush
@endonce

<div class="space-y-6">
    {{-- Image Gallery Section --}}
    @if($images->isNotEmpty())
        <div class="space-y-3">
            <h4 class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Dokumentasi Visual (Gambar)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($images as $attachment)
                    <div class="group relative bg-surface-container rounded-xl border border-outline-variant overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                        {{-- Image Preview Card --}}
                        <div class="relative aspect-video bg-surface-bright overflow-hidden">
                            <a href="{{ asset($storagePath . $attachment->stored_filename) }}" 
                               class="glightbox-gallery block w-full h-full"
                               data-gallery="attachment-gallery"
                               data-title="{{ $attachment->original_filename }}"
                               data-description="Diunggah oleh: {{ $attachment->uploader->name ?? 'System' }} | Tanggal: {{ $attachment->created_at->format('d M Y H:i') }}">
                                <img src="{{ asset($storagePath . $attachment->stored_filename) }}" 
                                     alt="{{ $attachment->original_filename }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy" />
                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-[28px] drop-shadow-md">zoom_in</span>
                                </div>
                            </a>
                        </div>
                        
                        {{-- Metadata & Action Section --}}
                        <div class="p-3 space-y-2 bg-surface-container-lowest">
                            <div class="min-w-0">
                                <p class="text-[10px] text-on-surface-variant font-semibold truncate" title="{{ $attachment->original_filename }}">
                                    {{ $attachment->original_filename }}
                                </p>
                                <p class="text-[9px] text-on-surface-variant/80 mt-0.5 font-medium">
                                    @if($attachment->file_size >= 1048576)
                                        {{ number_format($attachment->file_size / 1048576, 2) }} MB
                                    @else
                                        {{ number_format($attachment->file_size / 1024, 1) }} KB
                                    @endif
                                    • {{ $attachment->created_at->format('d M Y') }}
                                </p>
                                <p class="text-[9px] text-on-surface-variant/70 italic mt-0.5 truncate">
                                    Oleh: {{ $attachment->uploader->name ?? 'System' }}
                                </p>
                            </div>
                            
                            <div class="pt-2 border-t border-outline-variant/40 flex items-center justify-between gap-1.5">
                                <a href="{{ route($downloadRoute, $attachment->id) }}" class="inline-flex items-center gap-1 text-primary hover:text-primary-container text-[11px] font-bold transition-all">
                                    <span class="material-symbols-outlined text-[14px]">download</span>
                                    <span>Unduh</span>
                                </a>

                                @php
                                    $canDelete = false;
                                    if ($deleteRoute) {
                                        if ($deletePermission) {
                                            $canDelete = auth()->user()->can($deletePermission, $attachment);
                                        } else {
                                            $canDelete = true;
                                        }
                                    }
                                @endphp

                                @if($canDelete)
                                    <form action="{{ route($deleteRoute, $attachment->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lampiran ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-0.5 text-error hover:text-red-700 text-[11px] font-bold transition-all">
                                            <span class="material-symbols-outlined text-[14px]">delete</span>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Non-Image Documents Section --}}
    @if($nonImages->isNotEmpty())
        <div class="space-y-3">
            <h4 class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Dokumen Lampiran (PDF, Excel, Word, dll.)</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($nonImages as $attachment)
                    @php
                        $ext = strtolower(pathinfo($attachment->original_filename, PATHINFO_EXTENSION));
                        $icon = 'description';
                        $iconColor = 'text-primary';
                        
                        if ($ext === 'pdf') {
                            $icon = 'picture_as_pdf';
                            $iconColor = 'text-red-500';
                        } elseif (in_array($ext, ['xlsx', 'xls', 'csv'])) {
                            $icon = 'table_chart';
                            $iconColor = 'text-green-600';
                        } elseif (in_array($ext, ['docx', 'doc'])) {
                            $icon = 'article';
                            $iconColor = 'text-blue-500';
                        }
                    @endphp
                    <div class="p-3.5 bg-surface-container rounded-xl border border-outline-variant flex items-center justify-between gap-3 shadow-2xs hover:shadow-xs transition-all text-xs">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="material-symbols-outlined {{ $iconColor }} text-[24px] shrink-0">
                                {{ $icon }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-bold text-on-surface truncate text-xs" title="{{ $attachment->original_filename }}">
                                    {{ $attachment->original_filename }}
                                </p>
                                <p class="text-[10px] text-on-surface-variant mt-0.5 font-medium">
                                    @if($attachment->file_size >= 1048576)
                                        {{ number_format($attachment->file_size / 1048576, 2) }} MB
                                    @else
                                        {{ number_format($attachment->file_size / 1024, 1) }} KB
                                    @endif
                                    • Oleh: {{ $attachment->uploader->name ?? 'System' }}
                                </p>
                                <p class="text-[9px] text-on-surface-variant opacity-80 mt-0.5">
                                    {{ $attachment->created_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ asset($storagePath . $attachment->stored_filename) }}" target="_blank" class="text-primary hover:underline text-[11px] font-bold">
                                Lihat
                            </a>
                            <a href="{{ route($downloadRoute, $attachment->id) }}" class="text-secondary hover:underline text-[11px] font-bold">
                                Unduh
                            </a>
                            
                            @php
                                $canDelete = false;
                                if ($deleteRoute) {
                                    if ($deletePermission) {
                                        $canDelete = auth()->user()->can($deletePermission, $attachment);
                                    } else {
                                        $canDelete = true;
                                    }
                                }
                            @endphp

                            @if($canDelete)
                                <form action="{{ route($deleteRoute, $attachment->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lampiran ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-error hover:underline text-[11px] font-bold">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
