<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'current_company_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function currentCompany()
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'created_by');
    }

    public function closedPeriods()
    {
        return $this->hasMany(AccountingPeriod::class, 'closed_by');
    }

    public function switchCompany(int $companyId): void
    {
        $this->update(['current_company_id' => $companyId]);
        session(['current_company_id' => $companyId]);
    }

    /**
     * Menu permissions — staff hanya bisa akses menu yang diberikan admin.
     */
    public function menuPermissions()
    {
        return $this->hasMany(MenuPermission::class);
    }

    /**
     * Cek apakah user bisa mengakses menu tertentu.
     * Admin selalu return true.
     */
    public function canAccessMenu(string $key): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->menuPermissions()->where('menu_key', $key)->exists();
    }

    /**
     * Sync menu permissions: hapus semua lalu insert ulang dari array keys.
     */
    public function syncMenuPermissions(array $keys): void
    {
        $this->menuPermissions()->delete();
        $this->menuPermissions()->createMany(
            array_map(fn($k) => ['menu_key' => $k], $keys)
        );
    }
}
