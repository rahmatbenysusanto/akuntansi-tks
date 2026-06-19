<?php

namespace App\Traits;

use App\Scopes\CompanyScope;

trait TenantScoped
{
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (empty($model->company_id)) {
                if (auth()->check() && auth()->user()->current_company_id) {
                    $model->company_id = auth()->user()->current_company_id;
                } elseif (defined('static::SEEDING_COMPANY_ID') && static::SEEDING_COMPANY_ID) {
                    $model->company_id = static::SEEDING_COMPANY_ID;
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
