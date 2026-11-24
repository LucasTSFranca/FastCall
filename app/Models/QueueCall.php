<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueCall extends Model
{
    protected $fillable = [
        'company_id',
        'table_id',
        'attended_by',
        'position',
        'status',
        'notes',
        'called_at',
        'attended_at',
        'closed_at',
    ];

    protected $casts = [
        'called_at'   => 'datetime',
        'attended_at' => 'datetime',
        'closed_at'   => 'datetime',
    ];

    // ── Relacionamentos ─────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attended_by');
    }

    // ── Query Scopes ────────────────────────────────────────────────────────

    /** Chamados ativos (aguardando ou em atendimento) */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'in_progress']);
    }

    /** Apenas aguardando */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /** Filtra pela empresa */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // ── Helpers de status ───────────────────────────────────────────────────

    public function isWaiting(): bool    { return $this->status === 'waiting'; }
    public function isInProgress(): bool { return $this->status === 'in_progress'; }
    public function isDone(): bool       { return $this->status === 'done'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
    public function isClosed(): bool     { return in_array($this->status, ['done', 'cancelled']); }

    /** Tempo de espera formatado */
    public function getWaitingTimeAttribute(): string
    {
        return $this->called_at->diffForHumans();
    }
}
