<!-- TODO: Future release will load historical readiness snapshots instead of live calculation context -->
<table style="margin-bottom: 4px;">
    <tr>
        <td style="width: 25%; padding: 0 2px 0 0;">
            <div class="readiness-item">
                @if ($readiness['machine_ready'] ?? false)
                    <span class="text-green">✓</span> Machine Ready
                @else
                    <span class="text-red">✗</span> Machine Down
                @endif
            </div>
        </td>
        <td style="width: 25%; padding: 0 2px;">
            <div class="readiness-item">
                @if ($readiness['technician_assigned'] ?? false)
                    <span class="text-green">✓</span> Technician Assigned
                @else
                    <span class="text-red">✗</span> Technician Unassigned
                @endif
            </div>
        </td>
        <td style="width: 25%; padding: 0 2px;">
            <div class="readiness-item">
                @if ($readiness['documents_available'] ?? false)
                    <span class="text-green">✓</span> Manual Available
                @else
                    <span class="text-amber">⚠</span> Manual Not Available
                @endif
            </div>
        </td>
        <td style="width: 25%; padding: 0 0 0 2px;">
            <div class="readiness-item">
                @if ($readiness['sparepart_readiness_ready'] ?? false)
                    <span class="text-green">✓</span> Sparepart Ready
                @else
                    <span class="text-amber">⚠</span> Spareparts Low/Reorder
                @endif
            </div>
        </td>
    </tr>
</table>
