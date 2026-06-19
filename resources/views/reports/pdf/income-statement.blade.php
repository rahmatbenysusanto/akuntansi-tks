<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laba Rugi - {{ $period->label }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; text-align: left; border-bottom: 1px solid #ddd; }
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
        .periode { font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">PT. TRANSKARGO SOLUSINDO</div>
        <div class="subtitle">LAPORAN LABA RUGI</div>
        <div class="subtitle periode">
            Periode {{ \Carbon\Carbon::create($period->year, $period->month, 1)->translatedFormat('d F Y') }}
            s/d {{ \Carbon\Carbon::create($period->year, $period->month, 1)->endOfMonth()->translatedFormat('d F Y') }}
        </div>
    </div>

    <table>
        <tr class="bg-gray font-bold">
            <td colspan="2" style="padding: 6px;">PENDAPATAN USAHA</td>
            <td class="text-right" style="padding: 6px;">{{ formatRupiah($data['total_revenue']) }}</td>
        </tr>
        @foreach($data['revenues'] as $rev)
        <tr>
            <td width="20"></td>
            <td>{{ $rev['account']->name }}</td>
            <td class="text-right">{{ formatRupiah($rev['balance']) }}</td>
        </tr>
        @endforeach

        <tr class="bg-gray font-bold">
            <td colspan="2" style="padding: 6px;">BEBAN POKOK PENDAPATAN (HPP)</td>
            <td class="text-right" style="padding: 6px;">({{ formatRupiah($data['total_hpp']) }})</td>
        </tr>

        <tr class="bg-gray font-bold">
            <td colspan="2" style="padding: 6px;">LABA KOTOR</td>
            <td class="text-right {{ $data['gross_profit'] >= 0 ? 'text-green' : 'text-red' }}" style="padding: 6px;">
                {{ $data['gross_profit'] >= 0 ? '' : '(' }}{{ formatRupiah(abs($data['gross_profit'])) }}{{ $data['gross_profit'] < 0 ? ')' : '' }}
            </td>
        </tr>

        <tr class="bg-gray font-bold">
            <td colspan="2" style="padding: 6px;">BIAYA OPERASIONAL</td>
            <td class="text-right" style="padding: 6px;">({{ formatRupiah($data['total_operating_expenses']) }})</td>
        </tr>

        <tr class="bg-gray font-bold">
            <td colspan="2" style="padding: 6px;">LABA USAHA</td>
            <td class="text-right {{ $data['operating_profit'] >= 0 ? 'text-green' : 'text-red' }}" style="padding: 6px;">
                {{ $data['operating_profit'] >= 0 ? '' : '(' }}{{ formatRupiah(abs($data['operating_profit'])) }}{{ $data['operating_profit'] < 0 ? ')' : '' }}
            </td>
        </tr>

        <tr class="bg-gray font-bold">
            <td colspan="2" style="padding: 6px;">PENDAPATAN / BIAYA LAIN-LAIN</td>
            <td class="text-right" style="padding: 6px;">{{ formatRupiah($data['total_other']) }}</td>
        </tr>

        <tr class="bg-gray font-bold">
            <td colspan="2" style="padding: 6px;">BIAYA BUNGA</td>
            <td class="text-right" style="padding: 6px;">({{ formatRupiah($data['total_interest']) }})</td>
        </tr>

        <tr class="bg-gray font-bold">
            <td colspan="2" style="padding: 6px;">LABA SEBELUM PAJAK</td>
            <td class="text-right {{ $data['profit_before_tax'] >= 0 ? 'text-green' : 'text-red' }}" style="padding: 6px;">
                {{ $data['profit_before_tax'] >= 0 ? '' : '(' }}{{ formatRupiah(abs($data['profit_before_tax'])) }}{{ $data['profit_before_tax'] < 0 ? ')' : '' }}
            </td>
        </tr>

        <tr class="bg-gray font-bold">
            <td colspan="2" style="padding: 6px;">PAJAK PENGHASILAN</td>
            <td class="text-right" style="padding: 6px;">({{ formatRupiah($data['total_tax']) }})</td>
        </tr>

        <tr class="border-top font-bold bg-blue">
            <td colspan="2" style="padding: 8px;">LABA BERSIH TAHUN BERJALAN</td>
            <td class="text-right {{ $data['net_income'] >= 0 ? 'text-green' : 'text-red' }}" style="padding: 8px;">
                {{ $data['net_income'] >= 0 ? '' : '(' }}{{ formatRupiah(abs($data['net_income'])) }}{{ $data['net_income'] < 0 ? ')' : '' }}
            </td>
        </tr>
    </table>

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
