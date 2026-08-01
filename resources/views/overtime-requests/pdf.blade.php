<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Overtime Request — {{ $request->request_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.6;
            padding: 40px 50px;
        }

        /* ── Header / Kop ── */
        .header { margin-bottom: 25px; border-bottom: 2px solid #1e3a5f; padding-bottom: 15px; }
        .header-table { width: 100%; }
        .header-table td { vertical-align: middle; padding: 0; }
        .logo-cell { width: 70px; }
        .logo { width: 60px; height: auto; }
        .company-name { font-size: 16px; font-weight: bold; color: #1e3a5f; }
        .company-address { font-size: 8px; color: #64748b; margin-top: 2px; }
        .header-line { border-top: 2px solid #1e3a5f; }

        /* ── Title ── */
        .title-block { text-align: center; margin-bottom: 20px; }
        .title-main { font-size: 14px; font-weight: bold; color: #1e3a5f; text-transform: uppercase; letter-spacing: 1px; }
        .title-sub { font-size: 10px; color: #64748b; margin-top: 4px; }

        /* ── Info Grid ── */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px 8px; vertical-align: top; border: 1px solid #cbd5e1; }
        .info-label { background-color: #f1f5f9; font-weight: bold; font-size: 9px; width: 28%; color: #475569; }
        .info-value { font-size: 10px; color: #1e293b; }

        /* ── Activity Detail ── */
        .section-title {
            font-size: 11px; font-weight: bold; color: #1e3a5f;
            background-color: #f8fafc; padding: 6px 10px;
            border-left: 4px solid #1e3a5f; margin-bottom: 10px; margin-top: 20px;
        }
        .description-box {
            border: 1px solid #cbd5e1; padding: 12px 14px;
            min-height: 80px; font-size: 10px; color: #334155;
            background-color: #fafafa; border-radius: 2px;
            margin-bottom: 15px;
            line-height: 1.7;
        }

        /* ── Signature Block ── */
        .signature-section { margin-top: 40px; }
        .sig-table { width: 100%; }
        .sig-table td { width: 50%; padding: 0 20px; text-align: center; vertical-align: top; }
        .sig-label { font-size: 9px; color: #64748b; margin-bottom: 5px; }
        .sig-line { border-bottom: 1px solid #334155; margin-top: 55px; margin-bottom: 6px; width: 80%; margin-left: auto; margin-right: auto; }
        .sig-name { font-size: 10px; font-weight: bold; color: #1e293b; margin-top: 4px; }
        .sig-role { font-size: 8px; color: #94a3b8; }
        .sig-date { font-size: 8px; color: #94a3b8; margin-top: 8px; }

        /* ── Note ── */
        .note { margin-top: 25px; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .footer {
            position: fixed; bottom: 30px; left: 50px; right: 50px;
            font-size: 7px; color: #cbd5e1; border-top: 1px solid #e2e8f0; padding-top: 6px;
        }
    </style>
</head>
<body>

    {{-- ── HEADER / KOP ── --}}
    <div class="header">
        <table class="header-table">
            <tr>
                @if($company && $company->logo_path)
                <td class="logo-cell">
                    <img src="{{ storage_path('app/public/' . $company->logo_path) }}" class="logo" alt="Logo">
                </td>
                @endif
                <td>
                    <div class="company-name">{{ $company?->name ?? 'PT. TRANSKARGO SOLUSINDO' }}</div>
                    @if($company && $company->address)
                    <div class="company-address">{{ $company->address }}</div>
                    @endif
                    @if($company && $company->phone)
                    <div class="company-address">Telp: {{ $company->phone }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- ── JUDUL ── --}}
    <div class="title-block">
        <div class="title-main">Surat Permohonan Overtime</div>
        <div class="title-sub">Overtime Request Form</div>
    </div>

    {{-- ── INFO UTAMA ── --}}
    <table class="info-table">
        <tr>
            <td class="info-label">No. Request</td>
            <td class="info-value">{{ $request->request_no }}</td>
            <td class="info-label">Tanggal Request</td>
            <td class="info-value">{{ $request->request_date->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Nama Client</td>
            <td class="info-value">{{ $request->client_name }}</td>
            <td class="info-label">Tanggal Overtime</td>
            <td class="info-value">
                {{ $request->overtime_date->translatedFormat('d F Y') }}
                ({{ $request->overtime_date->translatedFormat('l') }})
            </td>
        </tr>
        @if($request->client_address)
        <tr>
            <td class="info-label">Alamat Client</td>
            <td class="info-value" colspan="3">{{ $request->client_address }}</td>
        </tr>
        @endif
        @if($request->client_phone)
        <tr>
            <td class="info-label">Telp. Client</td>
            <td class="info-value" colspan="3">{{ $request->client_phone }}</td>
        </tr>
        @endif
        <tr>
            <td class="info-label">Jenis Kegiatan</td>
            <td class="info-value" colspan="3">
                <strong>{{ $request->activity_type_label }}</strong>
            </td>
        </tr>
        <tr>
            <td class="info-label">Jam Overtime</td>
            <td class="info-value" colspan="3">
                Mulai: <strong>{{ \Carbon\Carbon::parse($request->overtime_start_time)->format('H:i') }} WIB</strong>
                @if($request->overtime_end_time)
                    &nbsp;—&nbsp; Selesai: <strong>{{ \Carbon\Carbon::parse($request->overtime_end_time)->format('H:i') }} WIB</strong>
                    &nbsp;&nbsp;<em>(Durasi: {{ $request->duration_label }})</em>
                @else
                    &nbsp;—&nbsp; <em>Sampai selesai</em>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── DESKRIPSI KEGIATAN ── --}}
    @if($request->description)
    <div class="section-title">📝 Deskripsi Kegiatan Overtime</div>
    <div class="description-box">
        {!! nl2br(e($request->description)) !!}
    </div>
    @endif

    {{-- ── TANDA TANGAN ── --}}
    <div class="signature-section">
        <table class="sig-table">
            <tr>
                {{-- Pihak Kami (PT. Transkargo) --}}
                <td>
                    <div class="sig-label">Diajukan oleh,</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ auth()->user()?->name ?? '(Staff Akunting)' }}</div>
                    <div class="sig-role">Staff — {{ $company?->name ?? 'PT. Transkargo Solusindo' }}</div>
                    <div class="sig-date">Tanggal: {{ now()->translatedFormat('d F Y') }}</div>
                </td>
                {{-- Pihak Client --}}
                <td>
                    <div class="sig-label">Disetujui oleh,</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">(_______________________)</div>
                    <div class="sig-role">{{ $request->client_name }}</div>
                    <div class="sig-date">Tanggal: _______________</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── CATATAN ── --}}
    <div class="note">
        <strong>Catatan:</strong>
        • Overtime berlaku mulai pukul 18:00 WIB sampai dengan kegiatan selesai.
        • Mohon persetujuan ini ditandatangani dan dikembalikan sebelum pelaksanaan overtime.
        @if($request->status === 'signed')
            <br>• <strong>Status: ✓ Sudah ditandatangani client.</strong>
        @endif
    </div>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB &nbsp;|&nbsp;
        No. Request: {{ $request->request_no }} &nbsp;|&nbsp;
        Status: {{ $request->status_label }}
    </div>

</body>
</html>
