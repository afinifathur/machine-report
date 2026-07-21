<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Services\MachineQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MachineQrCodeController extends Controller
{
    protected MachineQrCodeService $qrCodeService;

    public function __construct(MachineQrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Legacy migration helper: Generates permanent QR code for legacy machines without QR.
     */
    public function generate(string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();
        $this->qrCodeService->ensureExists($machine);

        return redirect()->route('machines.show', $machine->code)
            ->with('success', "QR Code Paspor untuk mesin {$machine->code} berhasil dibuat.");
    }

    /**
     * Download original high-resolution QR PNG file.
     */
    public function download(string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();
        $this->qrCodeService->ensureExists($machine);

        $filePath = storage_path('app/public/qr_codes/machine-' . $machine->id . '.png');
        if (!File::exists($filePath)) {
            abort(404, 'File QR Code tidak ditemukan.');
        }

        $downloadFilename = 'QR-' . $machine->code . '.png';
        return response()->download($filePath, $downloadFilename, [
            'Content-Type' => 'image/png'
        ]);
    }

    /**
     * Render dedicated thermal sticker print view.
     */
    public function print(string $code)
    {
        $machine = Machine::where('code', $code)->firstOrFail();
        $this->qrCodeService->ensureExists($machine);

        $qrCodeUrl = asset($machine->qr_code_path);
        $passportUrl = route('machines.show', $machine->code);

        return view('machines.qr_print', compact('machine', 'qrCodeUrl', 'passportUrl'));
    }
}
