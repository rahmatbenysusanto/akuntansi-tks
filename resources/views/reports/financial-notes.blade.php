<x-app-layout>
    <x-slot name="header">Catatan Atas Laporan Keuangan</x-slot>

    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <form method="GET" class="flex gap-3 items-end">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Periode</label>
                    <select name="period_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ ($selectedPeriod?->id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if($balanceData)
        <div class="space-y-6">
            <!-- 1. General Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3">1. UMUM</h3>
                <p class="text-sm text-gray-700 leading-relaxed">
                    PT. Transkargo Solusindo adalah perusahaan yang bergerak di bidang jasa cargo dan logistik.
                    Laporan keuangan disusun berdasarkan sistem akuntansi double-entry dengan periode tahunan.
                </p>
            </div>

            <!-- 2. Accounting Policies -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3">2. KEBIJAKAN AKUNTANSI</h3>
                <div class="text-sm text-gray-700 space-y-2">
                    <p><strong>a. Dasar Penyusunan:</strong> Laporan keuangan disusun berdasarkan basis akrual, kecuali laporan arus kas.</p>
                    <p><strong>b. Aset Tetap:</strong> Disusutkan menggunakan metode garis lurus (straight-line method) selama masa manfaat ekonomis aset.</p>
                    <p><strong>c. Pengakuan Pendapatan:</strong> Pendapatan diakui pada saat jasa cargo/logistik telah diberikan kepada pelanggan.</p>
                    <p><strong>d. Mata Uang:</strong> Seluruh transaksi dicatat dalam Rupiah Indonesia (IDR).</p>
                </div>
            </div>

            <!-- 3. Rincian Aktiva -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3">3. RINCIAN AKTIVA</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-200">
                            <th class="py-2">Akun</th>
                            <th class="py-2 text-right w-40">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceData['aktiva']['details'] as $a)
                        <tr class="border-b border-gray-100 {{ $a['account']->is_header ? 'font-semibold bg-gray-50' : '' }}">
                            <td class="py-1.5" style="padding-left: {{ $a['account']->level * 12 }}px">{{ $a['account']->name }}</td>
                            <td class="py-1.5 text-right font-mono">{{ $a['balance'] > 0 ? number_format($a['balance'], 0, ',', '.') : '-' }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-bold border-t-2 border-gray-300 bg-gray-50">
                            <td class="py-2">TOTAL AKTIVA</td>
                            <td class="py-2 text-right font-mono">{{ number_format($balanceData['total_aktiva'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 4. Rincian Kewajiban -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3">4. RINCIAN KEWAJIBAN</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-200">
                            <th class="py-2">Akun</th>
                            <th class="py-2 text-right w-40">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceData['kewajiban']['details'] as $k)
                        <tr class="border-b border-gray-100 {{ $k['account']->is_header ? 'font-semibold bg-gray-50' : '' }}">
                            <td class="py-1.5" style="padding-left: {{ $k['account']->level * 12 }}px">{{ $k['account']->name }}</td>
                            <td class="py-1.5 text-right font-mono">{{ $k['balance'] > 0 ? number_format($k['balance'], 0, ',', '.') : '-' }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-bold border-t-2 border-gray-300 bg-gray-50">
                            <td class="py-2">TOTAL KEWAJIBAN</td>
                            <td class="py-2 text-right font-mono">{{ number_format($balanceData['total_kewajiban'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 5. Rincian Modal -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3">5. RINCIAN MODAL</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-200">
                            <th class="py-2">Akun</th>
                            <th class="py-2 text-right w-40">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceData['modal']['details'] as $m)
                        <tr class="border-b border-gray-100 {{ $m['account']->is_header ? 'font-semibold bg-gray-50' : '' }}">
                            <td class="py-1.5" style="padding-left: {{ $m['account']->level * 12 }}px">{{ $m['account']->name }}</td>
                            <td class="py-1.5 text-right font-mono">{{ $m['balance'] > 0 ? number_format($m['balance'], 0, ',', '.') : '-' }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-bold border-t-2 border-gray-300 bg-gray-50">
                            <td class="py-2">TOTAL MODAL</td>
                            <td class="py-2 text-right font-mono">{{ number_format($balanceData['total_modal'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 6. Income Statement Notes -->
            @if($incomeData)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3">6. RINCIAN LABA RUGI</h3>
                <div class="text-sm text-gray-700 space-y-1">
                    <p>Pendapatan Usaha: <strong class="font-mono">Rp {{ number_format($incomeData['total_revenue'], 0, ',', '.') }}</strong></p>
                    <p>Beban Pokok Pendapatan: <strong class="font-mono">Rp {{ number_format($incomeData['total_hpp'], 0, ',', '.') }}</strong></p>
                    <p>Biaya Operasional: <strong class="font-mono">Rp {{ number_format($incomeData['total_operating_expenses'], 0, ',', '.') }}</strong></p>
                    <p class="font-bold border-t border-gray-200 pt-2 mt-2">Laba Bersih Tahun Berjalan: <strong class="font-mono text-green-700">Rp {{ number_format($incomeData['net_income'], 0, ',', '.') }}</strong></p>
                </div>
            </div>
            @endif
        </div>
        @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center text-gray-400">
            Pilih periode untuk menampilkan Catatan Atas Laporan Keuangan.
        </div>
        @endif
    </div>
</x-app-layout>
