<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Neraca - {{ $period->label }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 4px; text-align: left; border-bottom: 1px solid #ddd; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-top { border-top: 2px solid #333; }
        .border-bottom { border-bottom: 2px solid #333; }
        .bg-gray { background-color: #f5f5f5; }
        .bg-blue { background-color: #eff6ff; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-center { text-align: center; }
        .title { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .subtitle { font-size: 11px; text-align: center; margin-bottom: 4px; }
        .header { margin-bottom: 20px; }
        .signature { margin-top: 40px; width: 100%; }
        .signature td { border: none; padding: 0; }
        .signature-box { text-align: center; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; width: 200px; display: inline-block; }
        .footer { position: fixed; bottom: 0; right: 0; font-size: 8px; color: #999; }
        .col-left { width: 48%; float: left; }
        .col-right { width: 48%; float: right; }
        .clearfix { clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">PT. TRANSKARGO SOLUSINDO</div>
        <div class="subtitle">NERACA</div>
        <div class="subtitle" style="font-size: 9px;">
            Per {{ \Carbon\Carbon::create($period->year, $period->month, 1)->endOfMonth()->translatedFormat('d F Y') }}
        </div>
    </div>

    <div class="col-left">
        <table>
            <tr class="bg-gray font-bold"><td colspan="2" style="padding: 5px;">AKTIVA</td></tr>
            @foreach($data['aktiva']['details'] as $a)
            <tr>
                <td style="padding-left: {{ $a['account']->level * 8 }}px">
                    @if($a['is_negative'])<span style="color:#999;">(-) </span>@endif
                    {{ $a['account']->name }}
                </td>
                <td class="text-right" width="80" style="{{ $a['is_negative'] ? 'color:#dc2626;' : '' }}">
                    @if($a['is_negative'])({{ number_format($a['balance'], 0, ',', '.') }})@else{{ formatRupiah($a['balance']) }}@endif
                </td>
            </tr>
            @endforeach
            <tr class="border-top bg-gray font-bold">
                <td style="padding: 5px;">TOTAL AKTIVA</td>
                <td class="text-right" style="padding: 5px;">{{ formatRupiah($data['total_aktiva']) }}</td>
            </tr>
        </table>
    </div>

    <div class="col-right">
        <table>
            <tr class="bg-gray font-bold"><td colspan="2" style="padding: 5px;">KEWAJIBAN DAN MODAL</td></tr>
            <tr class="font-bold"><td colspan="2" style="padding: 3px 4px;">KEWAJIBAN</td></tr>
            @foreach($data['kewajiban']['details'] as $k)
            <tr>
                <td style="padding-left: {{ $k['account']->level * 8 }}px">{{ $k['account']->name }}</td>
                <td class="text-right" width="80">{{ formatRupiah($k['balance']) }}</td>
            </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td style="padding: 4px;">TOTAL KEWAJIBAN</td>
                <td class="text-right" style="padding: 4px;">{{ formatRupiah($data['total_kewajiban']) }}</td>
            </tr>

            <tr class="font-bold"><td colspan="2" style="padding: 3px 4px; padding-top: 8px;">MODAL</td></tr>
            @foreach($data['modal']['details'] as $m)
            <tr>
                <td style="padding-left: {{ $m['account']->level * 8 }}px">{{ $m['account']->name }}</td>
                <td class="text-right">{{ formatRupiah($m['balance']) }}</td>
            </tr>
            @endforeach
            <tr class="font-bold bg-gray">
                <td style="padding: 4px;">TOTAL MODAL</td>
                <td class="text-right" style="padding: 4px;">{{ formatRupiah($data['total_modal']) }}</td>
            </tr>

            <tr class="border-top bg-blue font-bold">
                <td style="padding: 5px;">TOTAL KEWAJIBAN & MODAL</td>
                <td class="text-right" style="padding: 5px;">{{ formatRupiah($data['total_kewajiban_modal']) }}</td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    @if($data['is_balanced'])
    <div style="text-align: center; margin-top: 15px; font-size: 9px; color: #16a34a; font-weight: bold;">
        ✓ BALANCE (Total Aktiva = Total Kewajiban & Modal)
    </div>
    @else
    <div style="text-align: center; margin-top: 15px; font-size: 9px; color: #dc2626; font-weight: bold;">
        ✗ TIDAK BALANCE (Selisih: {{ formatRupiah($data['difference']) }})
    </div>
    @endif

    <!-- Signature Block -->
    <table class="signature">
        <tr>
            <td class="signature-box" style="width: 50%;">
                <p style="margin-bottom: 5px; font-size: 9px;">Dibuat oleh,</p>
                <div class="signature-line"></div>
                <p style="margin-top: 5px; font-size: 10px; font-weight: bold;">(Staff Akunting)</p>
            </td>
            <td class="signature-box" style="width: 50%;">
                <p style="margin-bottom: 5px; font-size: 9px;">Disetujui oleh,</p>
                <div class="signature-line"></div>
                <p style="margin-top: 5px; font-size: 10px; font-weight: bold;">(Direktur / Owner)</p>
            </td>
        </tr>
    </table>

    <div class="footer">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</div>
</body>
</html>
