<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankStatementLine extends Model
{
    protected $fillable = ['bank_statement_import_id','transaction_date','description','debit','credit','is_reconciled','matched_journal_entry_line_id'];
    public $timestamps = false;
    protected function casts(): array { return ['is_reconciled' => 'boolean']; }
}

class BankStatementImport extends Model
{
    protected $fillable = ['bank_account_id','statement_date','opening_balance','closing_balance'];
    public $timestamps = false;
    public function lines() { return $this->hasMany(BankStatementLine::class); }
}
