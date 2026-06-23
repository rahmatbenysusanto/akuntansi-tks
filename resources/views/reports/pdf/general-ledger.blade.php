<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Besar - {{ $selectedAccount->code }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; text-align: left; border-bottom: 1px solid #ddd; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-top { border-top: 2px solid #333; }
        .title { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .subtitle { font-size: 11px; text-align: center; margin-bottom: 4px; }
        .header { margin-bottom: 20px; }
        .signature { margin-top: 40px; width: 100%; }
        .signature td { border: none; padding: 0; }
        .signature-box { text-align: center; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; width: 200px; display: inline-block; }
        .footer { position: fixed; bottom: 0; right: 0; font-size: 8px; color: #999; }
        .bg-gray { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">PT. TRANSKARGO SOLUSINDO</div>
        <div class="subtitle">BUKU BESAR</div>
        <div class="subtitle">{{ $data['account']->code }} - {{ $data['account']->name }}</div>
        <div class="subtitle" style="font-size:9px;">
            Periode: {{ $selectedPeriod->label }}
        </div>
    </div>

    <table>
        <thead>
            <tr class="bg-gray font-bold">
                <th style="width:60px;">Tanggal</th>
                <th style="width:80px;">No. Bukti</th>
                <th>Keterangan</th>
                <th class="text-right" style="width:90px;">Debet</th>
                <th class="text-right" style="width:90px;">Kredit</th>
                <th class="text-right" style="width:90px;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr class="font-bold">
                <td colspan="3">Saldo Awal</td>
                <td class="text-right">{{ $data['opening_balance_debit'] > 0 ? number_format($data['opening_balance_debit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $data['opening_balance_credit'] > 0 ? number_format($data['opening_balance_credit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ number_format($data['opening_balance'], 0, ',', '.') }}</td>
            </tr>
            @forelse($data['mutations'] as $mutation)
            <tr>
                <td>{{ $mutation['date']->format('d/m/Y') }}</td>
                <td>{{ $mutation['reference_no'] }}</td>
                <td>{{ $mutation['description'] }}</td>
                <td class="text-right">{{ $mutation['debit'] > 0 ? number_format($mutation['debit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $mutation['credit'] > 0 ? number_format($mutation['credit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ number_format($mutation['balance'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#999;">Tidak ada transaksi.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="border-top font-bold bg-gray">
                <td colspan="3" class="text-right">Saldo Akhir</td>
                <td class="text-right">{{ number_format($data['total_debit'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($data['total_credit'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($data['ending_balance'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature">
        <tr>
            <td class="signature-box" style="width:50%;">
                <p style="margin-bottom:5px;font-size:9px;">Dibuat oleh,</p>
                <div class="signature-line"></div>
                <p style="margin-top:5px;font-size:10px;font-weight:bold;">(Staff Akunting)</p>
            </td>
            <td class="signature-box" style="width:50%;">
                <p style="margin-bottom:5px;font-size:9px;">Disetujui oleh,</p>
                <div class="signature-line"></div>
                <p style="margin-top:5px;font-size:10px;font-weight:bold;">(Direktur / Owner)</p>
            </td>
        </tr>
    </table>

    <div class="footer">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</div>
</body>
</html>
