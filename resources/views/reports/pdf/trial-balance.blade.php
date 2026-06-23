<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Neraca Lajur - {{ $selectedPeriod->label }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 7px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 3px; text-align: left; border-bottom: 1px solid #ddd; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-top { border-top: 2px solid #333; }
        .title { font-size: 13px; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .subtitle { font-size: 10px; text-align: center; margin-bottom: 4px; }
        .header { margin-bottom: 12px; }
        .signature { margin-top: 30px; width: 100%; }
        .signature td { border: none; padding: 0; }
        .signature-box { text-align: center; }
        .signature-line { margin-top: 40px; border-top: 1px solid #333; width: 180px; display: inline-block; }
        .footer { position: fixed; bottom: 0; right: 0; font-size: 7px; color: #999; }
        .bg-gray { background-color: #f5f5f5; }
        .bg-gray2 { background-color: #eee; }
        .rotate { writing-mode: vertical-lr; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">PT. TRANSKARGO SOLUSINDO</div>
        <div class="subtitle">NERACA LAJUR</div>
        <div class="subtitle" style="font-size:8px;">Periode: {{ $selectedPeriod->label }}</div>
    </div>

    <table>
        <thead>
            <tr class="bg-gray font-bold" style="font-size:7px;">
                <th rowspan="2" style="width:40px;">Kode</th>
                <th rowspan="2">Nama Akun</th>
                <th colspan="2" style="border-left:1px solid #ccc;">Saldo Awal</th>
                <th colspan="2" style="border-left:1px solid #ccc;">Mutasi</th>
                <th colspan="2" style="border-left:1px solid #ccc;">Saldo Akhir</th>
                <th colspan="2" style="border-left:1px solid #ccc;">Laba Rugi</th>
                <th colspan="2" style="border-left:1px solid #ccc;">Neraca</th>
            </tr>
            <tr class="bg-gray font-bold" style="font-size:7px;">
                <th class="text-right" style="border-left:1px solid #ccc;width:50px;">Debet</th>
                <th class="text-right" style="width:50px;">Kredit</th>
                <th class="text-right" style="border-left:1px solid #ccc;width:50px;">Debet</th>
                <th class="text-right" style="width:50px;">Kredit</th>
                <th class="text-right" style="border-left:1px solid #ccc;width:50px;">Debet</th>
                <th class="text-right" style="width:50px;">Kredit</th>
                <th class="text-right" style="border-left:1px solid #ccc;width:50px;">Debet</th>
                <th class="text-right" style="width:50px;">Kredit</th>
                <th class="text-right" style="border-left:1px solid #ccc;width:50px;">Debet</th>
                <th class="text-right" style="width:50px;">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['lines'] as $line)
            <tr style="{{ $line['account']->is_header ? 'font-weight:bold;background-color:#f5f5f5;' : '' }}">
                <td style="color:#999;">{{ $line['account']->code }}</td>
                <td style="padding-left: {{ 3 + ($line['account']->level - 1) * 10 }}px">{{ $line['account']->name }}</td>
                <td class="text-right">{{ $line['opening_debit'] > 0 ? number_format($line['opening_debit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $line['opening_credit'] > 0 ? number_format($line['opening_credit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right" style="border-left:1px solid #eee;">{{ $line['mutation_debit'] > 0 ? number_format($line['mutation_debit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $line['mutation_credit'] > 0 ? number_format($line['mutation_credit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right" style="border-left:1px solid #eee;">{{ $line['ending_debit'] > 0 ? number_format($line['ending_debit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $line['ending_credit'] > 0 ? number_format($line['ending_credit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right" style="border-left:1px solid #eee;">{{ $line['income_statement_debit'] > 0 ? number_format($line['income_statement_debit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $line['income_statement_credit'] > 0 ? number_format($line['income_statement_credit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right" style="border-left:1px solid #eee;">{{ $line['balance_sheet_debit'] > 0 ? number_format($line['balance_sheet_debit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $line['balance_sheet_credit'] > 0 ? number_format($line['balance_sheet_credit'], 0, ',', '.') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-top font-bold bg-gray2">
                <td colspan="2" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($data['totals']['opening_debit'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($data['totals']['opening_credit'], 0, ',', '.') }}</td>
                <td class="text-right" style="border-left:1px solid #ccc;">{{ number_format($data['totals']['mutation_debit'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($data['totals']['mutation_credit'], 0, ',', '.') }}</td>
                <td class="text-right" style="border-left:1px solid #ccc;">{{ number_format($data['totals']['ending_debit'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($data['totals']['ending_credit'], 0, ',', '.') }}</td>
                <td class="text-right" style="border-left:1px solid #ccc;">{{ number_format($data['totals']['income_statement_debit'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($data['totals']['income_statement_credit'], 0, ',', '.') }}</td>
                <td class="text-right" style="border-left:1px solid #ccc;">{{ number_format($data['totals']['balance_sheet_debit'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($data['totals']['balance_sheet_credit'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
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
