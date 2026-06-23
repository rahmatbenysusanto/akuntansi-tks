<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'employee_no', 'name', 'department', 'position', 'bank_account_no', 'is_active',
    ];

    public function salary()
    {
        return $this->hasOne(EmployeeSalary::class);
    }

    public function cashAdvances()
    {
        return $this->hasMany(CashAdvance::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
