<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuPermission extends Model
{
    protected $fillable = ['menu_key'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
