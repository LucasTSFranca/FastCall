<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'theme_preference',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relacionamentos ─────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function attendedCalls(): HasMany
    {
        return $this->hasMany(QueueCall::class, 'attended_by');
    }

    // ── Helpers de Role ─────────────────────────────────────────────────────

    public function isAdmin(): bool     { return $this->role === 'admin'; }
    public function isManager(): bool   { return $this->role === 'manager'; }
    public function isAttendant(): bool { return $this->role === 'attendant'; }

    /** Gerentes e Admins podem cadastrar/editar/remover mesas e atendentes */
    public function canManageTables(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /** Tema ativo: prefere a escolha do usuário, cai no tema da empresa, depois 'dark' */
    public function getActiveThemeAttribute(): string
    {
        return $this->theme_preference
            ?? $this->company?->theme
            ?? 'dark';
    }
}
