<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;

class EmployeeSalaryController extends Controller
{
    public function index()
    {
        $employees = Employee::with('salary')
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(25);

        return view('employee-salaries.index', compact('employees'));
    }

    public function edit(Employee $employee)
    {
        $salary = $employee->salary ?? new EmployeeSalary();
        return view('employee-salaries.form', compact('employee', 'salary'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'base_salary'         => 'required|numeric|min:0',
            'allowance_transport' => 'nullable|numeric|min:0',
            'allowance_meal'      => 'nullable|numeric|min:0',
            'allowance_other'     => 'nullable|numeric|min:0',
            'bpjs_kesehatan_pct'  => 'nullable|numeric|min:0|max:100',
            'bpjs_tk_pct'         => 'nullable|numeric|min:0|max:100',
        ]);

        $validated = array_map(fn($v) => $v ?? 0, $validated);

        EmployeeSalary::updateOrCreate(
            ['employee_id' => $employee->id],
            $validated
        );

        return redirect()->route('employee-salaries.index')
            ->with('success', 'Setup gaji ' . $employee->name . ' berhasil disimpan.');
    }
}
