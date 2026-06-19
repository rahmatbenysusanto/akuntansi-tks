<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('name')->paginate(20);
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_no' => 'required|string|max:30',
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        return view('employees.form', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_no' => 'required|string|max:30',
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->update(['is_active' => false]);
        return redirect()->route('employees.index')
            ->with('success', 'Karyawan dinonaktifkan.');
    }
}
