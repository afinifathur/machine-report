<?php

namespace App\Services;

use App\Models\Machine;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use Illuminate\Support\Facades\File;

class MachineQrCodeService
{
    /**
     * Ensures every machine owns a permanent QR Code image file.
     * Automatically generates initial QR or recovers missing file using stable primary key filename.
     */
    public function ensureExists(Machine $machine): string
    {
        $directory = storage_path('app/public/qr_codes');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        $filename = 'machine-' . $machine->id . '.png';
        $fullPath = $directory . '/' . $filename;
        $relativePath = 'storage/qr_codes/' . $filename;

        // If file already exists on disk and model path is correct, return path
        if (File::exists($fullPath) && $machine->qr_code_path === $relativePath) {
            return $relativePath;
        }

        // Permanent Machine Passport URL encoded in QR
        $passportUrl = route('machines.show', $machine->code);

        // Output interface: GD PNG when available, fallback to SVG markup class
        $outputInterface = extension_loaded('gd') ? QRGdImagePNG::class : QRMarkupSVG::class;

        $options = new QROptions([
            'scale' => 10,
            'eccLevel' => EccLevel::M,
            'outputInterface' => $outputInterface,
            'outputBase64' => false,
        ]);

        $qrCode = new QRCode($options);
        $qrCode->render($passportUrl, $fullPath);

        // Update machine qr_code_path if changed or null
        if ($machine->qr_code_path !== $relativePath) {
            $machine->qr_code_path = $relativePath;
            $machine->save();
        }

        return $relativePath;
    }
}
