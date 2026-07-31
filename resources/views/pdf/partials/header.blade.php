<table class="header-table">
    <tr>
        <td style="width: 40%; vertical-align: middle; padding-left: 0;">
            <div class="header-logo">PT PERONI KARYA SENTRA</div>
            <div style="font-size: 7pt; color: #6b7280; font-weight: bold; margin-top: 1px;">FACTORY MAINTENANCE DIVISION</div>
        </td>
        <td style="width: 45%; text-align: center; vertical-align: middle;">
            <div class="header-title uppercase">{{ $doc_type === 'Work Order' ? 'MAINTENANCE WORK ORDER' : 'MAINTENANCE COMPLETION REPORT' }}</div>
            <div class="header-wo font-mono">{{ $doc_number }}</div>
        </td>
        <td style="width: 15%; text-align: right; vertical-align: middle; padding-right: 0;">
            <img src="{{ $qrCodeImage }}" style="width: 40px; height: 40px;" alt="QR Code" />
        </td>
    </tr>
</table>
