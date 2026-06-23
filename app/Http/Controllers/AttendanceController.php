<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        $query = Attendance::with('employee')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        // Filter bulan & tahun
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);
        $query->whereMonth('date', $month)->whereYear('date', $year);

        // Filter karyawan
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->paginate(25)->withQueryString();

        // Cek status clock-in hari ini (untuk semua karyawan, atau bisa dikembangkan per user)
        $todayAttendances = Attendance::with('employee')
            ->whereDate('date', today())
            ->get()
            ->keyBy('employee_id');

        return view('attendances.index', compact(
            'attendances', 'employees', 'todayAttendances', 'month', 'year'
        ));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        return view('attendances.form', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id'),
            ],
            'date'       => 'required|date',
            'clock_in'   => 'nullable|date_format:H:i',
            'clock_out'  => 'nullable|date_format:H:i|after_or_equal:clock_in',
            'status'     => 'required|in:hadir,sakit,izin,cuti,dinas_luar,alpha',
            'notes'      => 'nullable|string|max:500',
        ], [
            'clock_out.after_or_equal' => 'Jam keluar harus setelah atau sama dengan jam masuk.',
        ]);

        // Cek duplikasi manual (unique constraint juga akan menangkap, tapi beri pesan yang ramah)
        $exists = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $validated['date'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'Karyawan ini sudah memiliki catatan absensi untuk tanggal tersebut.'])->withInput();
        }

        Attendance::create($validated);

        return redirect()->route('attendances.index')
            ->with('success', 'Data absensi berhasil disimpan.');
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        return view('attendances.form', compact('attendance', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id'),
            ],
            'date'       => 'required|date',
            'clock_in'   => 'nullable|date_format:H:i',
            'clock_out'  => 'nullable|date_format:H:i|after_or_equal:clock_in',
            'status'     => 'required|in:hadir,sakit,izin,cuti,dinas_luar,alpha',
            'notes'      => 'nullable|string|max:500',
        ], [
            'clock_out.after_or_equal' => 'Jam keluar harus setelah atau sama dengan jam masuk.',
        ]);

        // Cek duplikasi, kecuali record ini sendiri
        $exists = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $validated['date'])
            ->where('id', '!=', $attendance->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'Karyawan ini sudah memiliki catatan absensi untuk tanggal tersebut.'])->withInput();
        }

        $attendance->update($validated);

        return redirect()->route('attendances.index')
            ->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendances.index')
            ->with('success', 'Data absensi berhasil dihapus.');
    }

    /**
     * Clock In — absen masuk untuk karyawan yang dipilih (hari ini)
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
        ]);

        $today = today();

        $existing = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return back()->with('error', 'Karyawan ini sudah absen hari ini (jam masuk: ' . ($existing->clock_in ?? '-') . ').');
        }

        Attendance::create([
            'employee_id' => $request->employee_id,
            'date'        => $today->toDateString(),
            'clock_in'    => now()->format('H:i'),
            'status'      => 'hadir',
        ]);

        $employee = Employee::find($request->employee_id);
        return back()->with('success', 'Clock In berhasil untuk ' . ($employee?->name ?? 'karyawan') . ' pukul ' . now()->format('H:i') . '.');
    }

    /**
     * Clock Out — absen pulang untuk karyawan yang dipilih (hari ini)
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
        ]);

        $attendance = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('date', today())
            ->first();

        if (! $attendance) {
            return back()->with('error', 'Karyawan ini belum Clock In hari ini.');
        }

        if ($attendance->clock_out) {
            return back()->with('error', 'Karyawan ini sudah Clock Out hari ini (jam pulang: ' . $attendance->clock_out . ').');
        }

        $attendance->update(['clock_out' => now()->format('H:i')]);

        $employee = Employee::find($request->employee_id);
        return back()->with('success', 'Clock Out berhasil untuk ' . ($employee?->name ?? 'karyawan') . ' pukul ' . now()->format('H:i') . '.');
    }
}
