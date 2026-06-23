<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Catatan Atas Laporan Keuangan - {{ $selectedPeriod->label }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 4px 6px; text-align: left; border-bottom: 1px solid #ddd; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .subtitle { font-size: 11px; text-align: center; margin-bottom: 4px; }
        .header { margin-bottom: 20px; }
        .signature { margin-top: 40px; width: 100%; }
        .signature td { border: none; padding: 0; }
        .signature-box { text-align: center; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; width: 200px; display: inline-block; }
        .footer { position: fixed; bottom: 0; right: 0; font-size: 8px; color: #999; }
        .bg-gray { background-color: #f5f5f5; }
        .section-title { font-size: 11px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 4px; margin-bottom: 8px; }
        .subsection-title { font-size: 10px; font-weight: bold; margin-top: 10px; margin-bottom: 4px; }
        p { margin: 3px 0; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">PT. TRANSKARGO SOLUSINDO</div>
        <div class="subtitle">CATATAN ATAS LAPORAN KEUANGAN</div>
        <div class="subtitle" style="font-size:9px;">Periode: {{ $selectedPeriod->label }}</div>
    </div>

    <div class="section-title">1. UMUM</div>
    <p>PT. Transkargo Solusindo adalah perusahaan yang bergerak di bidang jasa cargo dan logistik. Laporan keuangan disusun berdasarkan sistem akuntansi double-entry dengan periode tahunan.</p>

    <div class="section-title" style="margin-top:15px;">2. IKHTISAR KEBIJAKAN AKUNTANSI</div>
    <p><b>a. Dasar Penyusunan</b><br>Laporan keuangan disusun menggunakan basis akrual dengan konsep biaya historis.</p>
    <p><b>b. Piutang Usaha</b><br>Piutang usaha disajikan sebesar jumlah bruto dikurangi cadangan kerugian piutang tak tertagih.</p>
    <p><b>c. Aset Tetap</b><br>Aset tetap disusutkan menggunakan metode garis lurus selama masa manfaat ekonomis aset.</p>
    <p><b>d. Pengakuan Pendapatan</b><br>Pendapatan diakui pada saat jasa telah diberikan atau barang telah diserahkan kepada pelanggan.</p>

    <div class="section-title" style="margin-top:15px;">3. RINCIAN POS NERACA</div>

    <div class="subsection-title">3.1 Aktiva</div>
    <table>
        <tr class="bg-gray"><th>Akun</th><th class="text-right">Jumlah (Rp)</th></tr>
        @foreach($balanceData['aktiva']['details'] as $a)
        <tr><td style="padding-left:{{ 5 + ($a['account']->level - 1) * 12 }}px">{{ $a['account']->name }}</td>
            <td class="text-right">{{ $a['balance'] > 0 ? number_format($a['balance'], 0, ',', '.') : '-' }}</td></tr>
        @endforeach
        <tr class="font-bold bg-gray"><td>TOTAL AKTIVA</td><td class="text-right">{{ number_format($balanceData['total_aktiva'], 0, ',', '.') }}</td></tr>
    </table>

    <div class="subsection-title">3.2 Kewajiban</div>
    <table>
        <tr class="bg-gray"><th>Akun</th><th class="text-right">Jumlah (Rp)</th></tr>
        @foreach($balanceData['kewajiban']['details'] as $k)
        <tr><td style="padding-left:{{ 5 + ($k['account']->level - 1) * 12 }}px">{{ $k['account']->name }}</td>
            <td class="text-right">{{ $k['balance'] > 0 ? number_format($k['balance'], 0, ',', '.') : '-' }}</td></tr>
        @endforeach
        <tr class="font-bold bg-gray"><td>TOTAL KEWAJIBAN</td><td class="text-right">{{ number_format($balanceData['total_kewajiban'], 0, ',', '.') }}</td></tr>
    </table>

    <div class="subsection-title">3.3 Modal</div>
    <table>
        <tr class="bg-gray"><th>Akun</th><th class="text-right">Jumlah (Rp)</th></tr>
        @foreach($balanceData['modal']['details'] as $m)
        <tr><td style="padding-left:{{ 5 + ($m['account']->level - 1) * 12 }}px">{{ $m['account']->name }}</td>
            <td class="text-right">{{ $m['balance'] > 0 ? number_format($m['balance'], 0, ',', '.') : '-' }}</td></tr>
        @endforeach
        <tr class="font-bold bg-gray"><td>TOTAL MODAL</td><td class="text-right">{{ number_format($balanceData['total_modal'], 0, ',', '.') }}</td></tr>
    </table>

    <div class="section-title" style="margin-top:15px;">4. RINCIAN POS LABA RUGI</div>
    <table>
        <tr class="bg-gray"><th>Akun</th><th class="text-right">Jumlah (Rp)</th></tr>
        <tr><td class="font-bold">PENDAPATAN USAHA</td><td class="text-right">{{ number_format($incomeData['total_revenue'], 0, ',', '.') }}</td></tr>
        @foreach($incomeData['revenues'] as $rev)
        <tr><td style="padding-left:15px;">{{ $rev['account']->name }}</td><td class="text-right">{{ $rev['balance'] > 0 ? number_format($rev['balance'], 0, ',', '.') : '-' }}</td></tr>
        @endforeach
        <tr><td class="font-bold">BEBAN POKOK PENDAPATAN</td><td class="text-right">({{ number_format($incomeData['total_hpp'], 0, ',', '.') }})</td></tr>
        <tr><td class="font-bold">LABA KOTOR</td><td class="text-right">{{ number_format($incomeData['gross_profit'], 0, ',', '.') }}</td></tr>
        <tr><td class="font-bold">BIAYA OPERASIONAL</td><td class="text-right">({{ number_format($incomeData['total_operating_expenses'], 0, ',', '.') }})</td></tr>
        <tr><td class="font-bold">LABA USAHA</td><td class="text-right">{{ number_format($incomeData['operating_profit'], 0, ',', '.') }}</td></tr>
        <tr><td class="font-bold">LABA BERSIH</td><td class="text-right">{{ number_format($incomeData['net_income'], 0, ',', '.') }}</td></tr>
    </table>

    <div class="section-title" style="margin-top:15px;">5. ANALISIS RASIO KEUANGAN</div>
    <p>Rasio keuangan dapat dilihat pada laporan Financial Highlight yang merupakan bagian tidak terpisahkan dari Catatan Atas Laporan Keuangan ini.</p>

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
