<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        $requests = OvertimeRequest::with(['creator'])
            ->latest()
            ->paginate(20);

        return view('overtime-requests.index', compact('requests'));
    }

    public function create()
    {
        // Generate nomor request: OT-YYYYMMDD-XXX
        $today = now()->format('Ymd');
        $count = OvertimeRequest::whereDate('created_at', today())->count() + 1;
        $requestNo = "OT-{$today}-" . str_pad($count, 3, '0', STR_PAD_LEFT);

        return view('overtime-requests.form', compact('requestNo'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $validated = $request->validate([
            'request_no'          => [
                'required', 'string', 'max:50',
                Rule::unique('overtime_requests', 'request_no')->where('company_id', $companyId),
            ],
            'request_date'        => 'required|date',
            'client_name'         => 'required|string|max:255',
            'client_address'      => 'nullable|string|max:500',
            'client_phone'        => 'nullable|string|max:30',
            'pic_name'            => 'nullable|string|max:255',
            'pic_phone'           => 'nullable|string|max:30',
            'activity_type'       => 'required|in:staging_perangkat,lab_testing,software_upgrade,other',
            'activity_description' => 'nullable|string|max:255',
            'overtime_date'       => 'required|date',
            'overtime_start_time' => 'required|date_format:H:i',
            'overtime_end_time'   => 'nullable|date_format:H:i',
            'hourly_rate'         => 'nullable|numeric|min:0',
            'total_cost'          => 'nullable|numeric|min:0',
            'description'         => 'nullable|string|max:2000',
        ], [
            'request_no.unique' => 'Nomor request sudah digunakan.',
        ]);

        $validated['company_id'] = $companyId;
        $validated['created_by'] = auth()->id();
        $validated['status']     = 'draft';

        OvertimeRequest::create($validated);

        return redirect()->route('overtime-requests.index')
            ->with('success', 'Request overtime berhasil disimpan.');
    }

    public function edit(OvertimeRequest $overtimeRequest)
    {
        $this->authorizeOvertimeRequest($overtimeRequest);

        if ($overtimeRequest->status !== 'draft') {
            return back()->with('error', 'Request yang sudah dikirim/ditandatangani tidak bisa diedit.');
        }

        return view('overtime-requests.form', [
            'request'   => $overtimeRequest,
            'requestNo' => $overtimeRequest->request_no,
        ]);
    }

    public function update(Request $request, OvertimeRequest $overtimeRequest)
    {
        $this->authorizeOvertimeRequest($overtimeRequest);

        if ($overtimeRequest->status !== 'draft') {
            return back()->with('error', 'Request yang sudah dikirim/ditandatangani tidak bisa diedit.');
        }

        $companyId = auth()->user()->current_company_id;

        $validated = $request->validate([
            'request_no'          => [
                'required', 'string', 'max:50',
                Rule::unique('overtime_requests', 'request_no')
                    ->where('company_id', $companyId)
                    ->ignore($overtimeRequest->id),
            ],
            'request_date'        => 'required|date',
            'client_name'         => 'required|string|max:255',
            'client_address'      => 'nullable|string|max:500',
            'client_phone'        => 'nullable|string|max:30',
            'pic_name'            => 'nullable|string|max:255',
            'pic_phone'           => 'nullable|string|max:30',
            'activity_type'       => 'required|in:staging_perangkat,lab_testing,software_upgrade,other',
            'activity_description' => 'nullable|string|max:255',
            'overtime_date'       => 'required|date',
            'overtime_start_time' => 'required|date_format:H:i',
            'overtime_end_time'   => 'nullable|date_format:H:i',
            'hourly_rate'         => 'nullable|numeric|min:0',
            'total_cost'          => 'nullable|numeric|min:0',
            'description'         => 'nullable|string|max:2000',
        ]);

        $validated['updated_by'] = auth()->id();

        $overtimeRequest->update($validated);

        return redirect()->route('overtime-requests.index')
            ->with('success', 'Request overtime berhasil diperbarui.');
    }

    public function destroy(OvertimeRequest $overtimeRequest)
    {
        $this->authorizeOvertimeRequest($overtimeRequest);

        if ($overtimeRequest->status !== 'draft') {
            return back()->with('error', 'Hanya request berstatus Draft yang bisa dihapus.');
        }

        $overtimeRequest->delete();

        return redirect()->route('overtime-requests.index')
            ->with('success', 'Request overtime berhasil dihapus.');
    }

    /**
     * Mark as sent to client.
     */
    public function send(OvertimeRequest $overtimeRequest)
    {
        $this->authorizeOvertimeRequest($overtimeRequest);

        if ($overtimeRequest->status !== 'draft') {
            return back()->with('error', 'Hanya request berstatus Draft yang bisa dikirim.');
        }

        $overtimeRequest->update([
            'status'     => 'sent',
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Request overtime ditandai sudah dikirim ke client.');
    }

    /**
     * Mark as signed by client.
     */
    public function sign(OvertimeRequest $overtimeRequest)
    {
        $this->authorizeOvertimeRequest($overtimeRequest);

        if ($overtimeRequest->status !== 'sent') {
            return back()->with('error', 'Hanya request berstatus Terkirim yang bisa ditandatangani.');
        }

        $overtimeRequest->update([
            'status'     => 'signed',
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Request overtime ditandai sudah ditandatangani client.');
    }

    /**
     * Generate PDF untuk dikirim ke client.
     */
    public function pdf(OvertimeRequest $overtimeRequest)
    {
        $this->authorizeOvertimeRequest($overtimeRequest);

        $company = auth()->user()->currentCompany;

        $pdf = Pdf::loadView('overtime-requests.pdf', [
            'request' => $overtimeRequest,
            'company' => $company,
        ]);

        $pdf->setPaper('A4');

        return $pdf->download("Overtime-Request-{$overtimeRequest->request_no}.pdf");
    }

    /**
     * Cek bahwa overtime request dimiliki oleh company user yang sedang login.
     */
    private function authorizeOvertimeRequest(OvertimeRequest $overtimeRequest): void
    {
        if ($overtimeRequest->company_id !== auth()->user()->current_company_id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }
    }
}
