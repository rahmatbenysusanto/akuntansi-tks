<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiJournalService
{
    /**
     * Generate journal entry suggestion from natural language prompt.
     *
     * @param string $userPrompt  Deskripsi transaksi dalam bahasa Indonesia
     * @param int    $companyId   ID perusahaan yang sedang aktif
     * @return array{success: bool, data?: array, error?: string}
     */
    public function suggest(string $userPrompt, int $companyId): array
    {
        // 1. Ambil semua akun aktif (leaf only) untuk company ini
        $accounts = Account::where('company_id', $companyId)
            ->where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'category', 'normal_balance', 'report_type']);

        if ($accounts->isEmpty()) {
            return [
                'success' => false,
                'error'   => 'Tidak ada Chart of Account tersedia untuk perusahaan ini.',
            ];
        }

        // 2. Bangun konteks COA untuk system prompt
        $coaContext = $this->buildCoaContext($accounts);

        // 3. Panggil DeepSeek API
        $response = $this->callDeepSeekApi($userPrompt, $coaContext);

        if (!$response['success']) {
            return $response;
        }

        // 4. Validasi & mapping account_id dari response AI
        $result = $this->parseAndValidate($response['data'], $accounts);

        return $result;
    }

    /**
     * Build a compact COA context string for the AI system prompt.
     */
    private function buildCoaContext($accounts): string
    {
        $lines = [];
        foreach ($accounts as $acc) {
            $lines[] = sprintf(
                '[%s] %s | %s | normal: %s | laporan: %s',
                $acc->code,
                $acc->name,
                $acc->category,
                $acc->normal_balance,
                $acc->report_type === 'balance_sheet' ? 'Neraca' : 'Laba Rugi'
            );
        }
        return implode("\n", $lines);
    }

    /**
     * Call DeepSeek API (OpenAI-compatible chat completions).
     */
    private function callDeepSeekApi(string $userPrompt, string $coaContext): array
    {
        $apiKey  = config('services.deepseek.key');
        $model   = config('services.deepseek.model', 'deepseek-chat');
        $baseUrl = config('services.deepseek.base_url', 'https://api.deepseek.com');

        if (empty($apiKey)) {
            Log::error('DEEPSEEK_KEY tidak ditemukan di .env');
            return [
                'success' => false,
                'error'   => 'Konfigurasi AI belum lengkap. Tambahkan DEEPSEEK_KEY di .env.',
            ];
        }

        $systemPrompt = $this->buildSystemPrompt($coaContext);

        try {
            $httpResponse = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type'  => 'application/json',
                ])
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model'       => $model,
                    'temperature' => 0.1, // rendah supaya deterministik
                    'max_tokens'  => 2000,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userPrompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (!$httpResponse->successful()) {
                Log::error('DeepSeek API error', [
                    'status' => $httpResponse->status(),
                    'body'   => $httpResponse->body(),
                ]);
                return [
                    'success' => false,
                    'error'   => "DeepSeek API error (HTTP {$httpResponse->status()}): " . substr($httpResponse->body(), 0, 300),
                ];
            }

            $json = $httpResponse->json();
            $content = $json['choices'][0]['message']['content'] ?? null;

            if (empty($content)) {
                Log::error('DeepSeek API returned empty content', ['json' => $json]);
                return [
                    'success' => false,
                    'error'   => 'AI tidak menghasilkan respon. Coba lagi dengan deskripsi yang lebih jelas.',
                ];
            }

            $parsed = json_decode($content, true);
            if (!is_array($parsed)) {
                Log::error('DeepSeek response is not valid JSON', ['content' => $content]);
                return [
                    'success' => false,
                    'error'   => 'Respon AI tidak valid (bukan JSON). Coba lagi.',
                ];
            }

            return ['success' => true, 'data' => $parsed];

        } catch (\Throwable $e) {
            Log::error('DeepSeek API exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error'   => 'Gagal menghubungi AI: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build the system prompt that instructs the AI how to behave.
     */
    private function buildSystemPrompt(string $coaContext): string
    {
        return <<<PROMPT
Kamu adalah asisten akuntansi double-entry untuk PT. Transkargo Solusindo (perusahaan jasa cargo/logistik).
Tugasmu: menerima deskripsi transaksi dalam bahasa Indonesia, lalu menghasilkan jurnal umum (journal entry)
yang sesuai dengan Chart of Account (COA) perusahaan.

ATURAN PENTING:
1. Setiap jurnal HARUS balance: total DEBIT = total KREDIT.
2. Hanya gunakan akun dari daftar COA di bawah. JANGAN mengarang akun baru.
3. Pilih akun yang PALING SPESIFIK dan SESUAI dengan deskripsi transaksi.
4. Perhatikan kategori akun dan pos saldo normalnya:
   - Aktiva: normal DEBIT (bertambah di debit, berkurang di kredit)
   - Kewajiban: normal KREDIT (bertambah di kredit, berkurang di debit)
   - Modal: normal KREDIT
   - Pendapatan: normal KREDIT
   - HPP & Biaya: normal DEBIT
5. Jika user menyebut "bank", "kas", "tunai", cari akun kas/bank yang sesuai.
6. Jika user menyebut "listrik", "air", "telepon", "internet", "gaji", "sewa",
   "transport", "bensin", "solar", "perbaikan", "pemeliharaan", dll —
   cari akun BIAYA yang sesuai di kategori biaya_operasional.
7. Jika user menyebut "penjualan", "pendapatan", "jasa", cari akun PENDAPATAN.
8. Jika user menyebut "beli", "pembelian" aset/kendaraan/peralatan, cari akun AKTIVA yang sesuai.
9. Jika user menyebut "hutang", "pinjaman", "leasing", "cicilan", cari akun KEWAJIBAN.
10. Untuk setiap baris jurnal, berikan penjelasan singkat mengapa akun itu dipilih (field "explanation").

FORMAT OUTPUT (JSON):
{
  "description": "Keterangan jurnal (ringkas, profesional, bahasa Indonesia)",
  "reference_no": "No. bukti jika disebutkan, null jika tidak ada",
  "entry_date": "YYYY-MM-DD (gunakan tanggal yang disebutkan user, atau null jika tidak disebutkan)",
  "lines": [
    {
      "account_code": "kode akun dari COA",
      "account_name": "nama akun dari COA",
      "debit": 0,
      "credit": 1500000,
      "explanation": "Penjelasan kenapa akun ini dipilih"
    }
  ]
}

DAFTAR CHART OF ACCOUNT (COA) PERUSAHAAN:
Format: [KODE] NAMA | KATEGORI | NORMAL_BALANCE | POS_LAPORAN

{$coaContext}
PROMPT;
    }

    /**
     * Parse AI response, validate against actual COA, and map account_id.
     */
    private function parseAndValidate(array $aiData, $accounts): array
    {
        // Build lookup maps
        $accountByCode = [];
        $accountById   = [];
        foreach ($accounts as $acc) {
            $accountByCode[$acc->code] = $acc;
            $accountById[$acc->id]     = $acc;
        }

        $lines = $aiData['lines'] ?? [];

        if (empty($lines) || !is_array($lines)) {
            return [
                'success' => false,
                'error'   => 'AI tidak menghasilkan baris jurnal. Coba deskripsi yang lebih spesifik.',
            ];
        }

        if (count($lines) < 2) {
            return [
                'success' => false,
                'error'   => 'AI hanya menghasilkan ' . count($lines) . ' baris. Jurnal minimal butuh 2 baris (debit & kredit).',
            ];
        }

        $totalDebit  = 0;
        $totalCredit = 0;
        $mappedLines = [];
        $errors      = [];

        foreach ($lines as $i => $line) {
            $code   = $line['account_code'] ?? null;
            $name   = $line['account_name'] ?? null;
            $debit  = floatval($line['debit'] ?? 0);
            $credit = floatval($line['credit'] ?? 0);

            // Cari akun by code dulu, lalu fallback by name
            $account = null;
            if ($code && isset($accountByCode[$code])) {
                $account = $accountByCode[$code];
            } elseif ($name) {
                // Cari akun yang namanya paling mirip
                $account = $this->findAccountByName($name, $accounts);
            }

            if (!$account) {
                $errors[] = "Baris " . ($i + 1) . ": Akun \"{$code} - {$name}\" tidak ditemukan di COA.";
                continue;
            }

            if ($debit == 0 && $credit == 0) {
                $errors[] = "Baris " . ($i + 1) . ": Debit dan Kredit tidak boleh 0 semua.";
                continue;
            }

            if ($debit > 0 && $credit > 0) {
                $errors[] = "Baris " . ($i + 1) . ": Tidak boleh isi Debit dan Kredit sekaligus.";
                continue;
            }

            $totalDebit  += $debit;
            $totalCredit += $credit;

            $mappedLines[] = [
                'account_id'   => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'debit'        => $debit,
                'credit'       => $credit,
                'explanation'  => $line['explanation'] ?? null,
            ];
        }

        // Cek apakah semua baris berhasil di-mapping
        if (count($mappedLines) < 2) {
            return [
                'success' => false,
                'error'   => 'Gagal memetakan akun: ' . implode(' ', $errors),
            ];
        }

        // Cek balance
        if (abs($totalDebit - $totalCredit) > 0.01) {
            return [
                'success' => false,
                'error'   => "AI menghasilkan jurnal tidak balance: Debit Rp " . number_format($totalDebit, 0, ',', '.') .
                            " vs Kredit Rp " . number_format($totalCredit, 0, ',', '.') . ". Coba ulangi.",
            ];
        }

        // Attach any non-fatal warnings
        $warnings = $errors; // errors yang tidak fatal (akun tidak ditemukan sudah di-skip)

        return [
            'success'    => true,
            'data'       => [
                'description'  => $aiData['description'] ?? '',
                'reference_no' => $aiData['reference_no'] ?? null,
                'entry_date'   => $aiData['entry_date'] ?? null,
                'lines'        => $mappedLines,
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
            ],
            'warnings'   => $warnings,
        ];
    }

    /**
     * Find the best matching account by name (simple fuzzy match).
     */
    private function findAccountByName(string $name, $accounts): ?Account
    {
        $name = strtolower(trim($name));

        // 1. Exact match
        foreach ($accounts as $acc) {
            if (strtolower($acc->name) === $name) {
                return $acc;
            }
        }

        // 2. Contains match
        $best     = null;
        $bestScore = 0;
        foreach ($accounts as $acc) {
            $accName = strtolower($acc->name);
            similar_text($name, $accName, $percent);
            if ($percent > $bestScore && $percent > 50) {
                $bestScore = $percent;
                $best      = $acc;
            }
            // Juga cek apakah name terkandung dalam accName atau sebaliknya
            if ($bestScore < 80 && (str_contains($accName, $name) || str_contains($name, $accName))) {
                $bestScore = 80;
                $best      = $acc;
            }
        }

        return $best;
    }
}
