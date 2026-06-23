<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap PPN - {{ $month }}/{{ $year }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 3px 5px; text-align: left; border-bottom: 1px solid #ddd; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .subtitle { font-size: 11px; text-align: center; margin-bottom: 4px; }
        .header { margin-bottom: 15px; }
        .signature { margin-top: 40px; width: 100%; }
        .signature td { border: none; padding: 0; }
        .signature-box { text-align: center; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; width: 200px; display: inline-block; }
        .footer { position: fixed; bottom: 0; right: 0; font-size: 8px; color: #999; }
        .bg-gray { background-color: #f5f5f5; }
        .bg-green { background-color: #f0fdf4; }
        .bg-red { background-color: #fef2f2; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">PT. TRANSKARGO SOLUSINDO</div>
        <div class="subtitle">REKAP PPN</div>
        <div class="subtitle" style="font-size:9px;">Periode: {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</div>
    </div>

    <table style="width:60%;margin-bottom:15px;">
        <tr class="bg-red"><th style="padding:6px;">PPN Keluaran</th><td class="text-right font-bold" style="padding:6px;">{{ number_format($totalKeluaran, 0, ',', '.') }}</td></tr>
        <tr class="bg-green"><th style="padding:6px;">PPN Masukan</th><td class="text-right font-bold" style="padding:6px;">{{ number_format($totalMasukan, 0, ',', '.') }}</td></tr>
        <tr class="{{ $netto >= 0 ? 'bg-red' : 'bg-green' }}"><th style="padding:6px;">{{ $netto >= 0 ? 'PPN Harus Disetor' : 'PPN Lebih Bayar' }}</th>
            <td class="text-right font-bold" style="padding:6px;">{{ number_format(abs($netto), 0, ',', '.') }}</td></tr>
    </table>

    <h3 style="font-size:11px;">PPN Keluaran</h3>
    <table>
        <thead><tr class="bg-gray"><th>Tgl</th><th>Counterparty</th><th>NPWP</th><th class="text-right">DPP</th><th class="text-right">PPN</th><th>Dokumen</th></tr></thead>
        <tbody>
        @forelse($keluaran as $k)
            <tr>
                <td>{{ \Carbon\Carbon::parse($k->transaction_date)->format('d/m/Y') }}</td>
                <td>{{ $k->counterparty_name }}</td>
                <td>{{ $k->counterparty_npwp }}</td>
                <td class="text-right">{{ number_format($k->dpp, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($k->tax_amount, 0, ',', '.') }}</td>
                <td>{{ $k->document_no }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center" style="color:#999;">Tidak ada transaksi PPN Keluaran.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h3 style="font-size:11px;margin-top:12px;">PPN Masukan</h3>
    <table>
        <thead><tr class="bg-gray"><th>Tgl</th><th>Counterparty</th><th>NPWP</th><th class="text-right">DPP</th><th class="text-right">PPN</th><th>Dokumen</th></tr></thead>
        <tbody>
        @forelse($masukan as $m)
            <tr>
                <td>{{ \Carbon\Carbon::parse($m->transaction_date)->format('d/m/Y') }}</td>
                <td>{{ $m->counterparty_name }}</td>
                <td>{{ $m->counterparty_npwp }}</td>
                <td class="text-right">{{ number_format($m->dpp, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($m->tax_amount, 0, ',', '.') }}</td>
                <td>{{ $m->document_no }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center" style="color:#999;">Tidak ada transaksi PPN Masukan.</td></tr>
        @endforelse
        </tbody>
    </table>

    <table class="signature">
        <tr>
            <td class="signature-box" style="width:50%;">
                <p style="margin-bottom:5px;font-size:8px;">Dibuat oleh,</p>
                <div class="signature-line"></div>
                <p style="margin-top:5px;font-size:9px;font-weight:bold;">(Staff Akunting)</p>
            </td>
            <td class="signature-box" style="width:50%;">
                <p style="margin-bottom:5px;font-size:8px;">Disetujui oleh,</p>
                <div class="signature-line"></div>
                <p style="margin-top:5px;font-size:9px;font-weight:bold;">(Direktur / Owner)</p>
            </td>
        </tr>
    </table>

    <div class="footer">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</div>
</body>
</html>
