<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'empresa_id',
        'nome',
        'email',
        'password',
        'perfil',
        'ativo',
        'tema_preferido',
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
            'ativo' => 'boolean',
        ];
    }

    // ── Relacionamentos ─────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'empresa_id');
    }

    public function attendedCalls(): HasMany
    {
        return $this->hasMany(QueueCall::class, 'atendente_id');
    }

    // ── Helpers de Role ─────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->perfil === 'administrador';
    }

    public function isManager(): bool
    {
        return $this->perfil === 'gerente';
    }

    public function isAttendant(): bool
    {
        return $this->perfil === 'atendente';
    }

    /** Gerentes e Admins podem cadastrar/editar/remover mesas e atendentes */
    public function canManageTables(): bool
    {
        return in_array($this->perfil, ['administrador', 'gerente']);
    }

    /** Tema ativo: prefere a escolha do usuário, cai no tema da empresa e, por fim, no padrão. */
    public function getActiveThemeAttribute(): string
    {
        return $this->tema_preferido
            ?? $this->company?->tema
            ?? 'safira';
    }
}
