<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use TenantScoped;
    protected $fillable = ['company_id','account_id','bank_name','account_number','account_holder_name'];
}

class BankStatementImport extends Model
{
    protected $fillable = ['bank_account_id','statement_date','opening_balance','closing_balance'];
    public $timestamps = false;
}

class BankStatementLine extends Model
{
    protected $fillable = ['bank_statement_import_id','transaction_date','description','debit','credit','is_reconciled','matched_journal_entry_line_id'];
    public $timestamps = false;
}
