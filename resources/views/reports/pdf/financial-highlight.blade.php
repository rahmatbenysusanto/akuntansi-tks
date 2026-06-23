<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Highlight - {{ $selectedPeriod->label }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 5px 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
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
        .section-title { font-size: 12px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 4px; margin-bottom: 8px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">PT. TRANSKARGO SOLUSINDO</div>
        <div class="subtitle">FINANCIAL HIGHLIGHT</div>
        <div class="subtitle" style="font-size:9px;">Periode: {{ $selectedPeriod->label }}</div>
    </div>

    <div class="section-title">PROFITABILITY RATIOS</div>
    <table>
        <tr class="bg-gray"><th style="width:40%;">Rasio</th><th style="width:35%;">Nilai</th><th>Formula</th></tr>
        @foreach(['net_profit_margin', 'roi', 'roe', 'roce'] as $key)
            @if(isset($ratios[$key]))
            <tr>
                <td>{{ $ratios[$key]['label'] }}</td>
                <td class="text-center font-bold">{{ number_format($ratios[$key]['value'], 2) }}{{ $ratios[$key]['unit'] }}</td>
                <td style="font-size:8px;color:#666;">{{ $ratios[$key]['formula'] }}</td>
            </tr>
            @endif
        @endforeach
    </table>

    <div class="section-title">LIQUIDITY RATIOS</div>
    <table>
        <tr class="bg-gray"><th style="width:40%;">Rasio</th><th style="width:35%;">Nilai</th><th>Formula</th></tr>
        @foreach(['current_ratio', 'quick_ratio', 'absolute_liquidity_ratio'] as $key)
            @if(isset($ratios[$key]))
            <tr>
                <td>{{ $ratios[$key]['label'] }}</td>
                <td class="text-center font-bold">{{ number_format($ratios[$key]['value'], 2) }}{{ $ratios[$key]['unit'] }}</td>
                <td style="font-size:8px;color:#666;">{{ $ratios[$key]['formula'] }}</td>
            </tr>
            @endif
        @endforeach
    </table>

    <div class="section-title">EFFICIENCY & LEVERAGE RATIOS</div>
    <table>
        <tr class="bg-gray"><th style="width:40%;">Rasio</th><th style="width:35%;">Nilai</th><th>Formula</th></tr>
        @foreach(['sales_to_liquid_assets', 'debt_to_equity'] as $key)
            @if(isset($ratios[$key]))
            <tr>
                <td>{{ $ratios[$key]['label'] }}</td>
                <td class="text-center font-bold">{{ number_format($ratios[$key]['value'], 2) }}{{ $ratios[$key]['unit'] }}</td>
                <td style="font-size:8px;color:#666;">{{ $ratios[$key]['formula'] }}</td>
            </tr>
            @endif
        @endforeach
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
