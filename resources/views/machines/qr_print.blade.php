<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Label - {{ $machine->code }}</title>
    <style>
        @page {
            size: 70mm 50mm;
            margin: 0;
        }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .label-card {
            width: 70mm;
            height: 50mm;
            background: #ffffff;
            box-sizing: border-box;
            padding: 3mm 4mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .company-header {
            font-size: 8pt;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: #1e293b;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            width: 100%;
            padding-bottom: 1.5mm;
            line-height: 1;
        }

        .machine-info {
            width: 100%;
            margin-top: 1mm;
        }

        .machine-name {
            font-size: 8.5pt;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.1;
        }

        .machine-code {
            font-size: 11pt;
            font-weight: 900;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #0284c7;
            letter-spacing: 0.05em;
            line-height: 1.2;
        }

        .qr-container {
            width: 25mm;
            height: 25mm;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1mm 0;
        }

        .qr-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            image-rendering: pixelated;
        }

        .footer-text {
            font-size: 6.5pt;
            font-weight: 600;
            color: #64748b;
            line-height: 1;
        }

        .screen-toolbar {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
            z-index: 50;
        }

        @media print {
            body {
                background: none;
                min-height: auto;
            }

            .screen-toolbar {
                display: none !important;
            }

            .label-card {
                border: none;
                box-shadow: none;
                width: 70mm;
                height: 50mm;
                page-break-after: always;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen action bar -->
    <div class="screen-toolbar">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded shadow hover:bg-blue-700 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak Label
        </button>
        <button onclick="window.close()" class="px-4 py-2 bg-slate-200 text-slate-700 font-semibold text-sm rounded hover:bg-slate-300 transition">
            Tutup
        </button>
    </div>

    <!-- Printable Thermal Sticker Label Container -->
    <div class="label-card">
        <div class="company-header">
            PT PERONI KARYA SENTRA
        </div>

        <div class="machine-info">
            <div class="machine-name">{{ $machine->name }}</div>
            <div class="machine-code">{{ $machine->code }}</div>
        </div>

        <div class="qr-container">
            <img src="{{ $qrCodeUrl }}" alt="QR Code {{ $machine->code }}" class="qr-image">
        </div>

        <div class="footer-text">
            Scan untuk membuka Machine Passport
        </div>
    </div>

    <script>
        // Auto invoke window.print() upon page load
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
