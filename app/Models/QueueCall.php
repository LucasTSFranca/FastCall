<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueCall extends Model
{
    protected $table = 'chamados';

    public const SERVICE_TYPES = [
        'atendimento' => 'Chamar atendente',
        'pedir_conta' => 'Pedir a conta',
    ];

    protected $fillable = [
        'empresa_id',
        'mesa_id',
        'atendente_id',
        'posicao',
        'tipo_servico',
        'situacao',
        'observacoes',
        'chamado_em',
        'atendimento_iniciado_em',
        'encerrado_em',
    ];

    protected $casts = [
        'chamado_em' => 'datetime',
        'atendimento_iniciado_em' => 'datetime',
        'encerrado_em' => 'datetime',
    ];

    // ── Relacionamentos ─────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'empresa_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'mesa_id');
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendente_id');
    }

    // ── Query Scopes ────────────────────────────────────────────────────────

    /** Chamados ativos (aguardando ou em atendimento) */
    public function scopeActive($query)
    {
        return $query->whereIn('situacao', ['aguardando', 'em_atendimento']);
    }

    /** Apenas aguardando */
    public function scopeWaiting($query)
    {
        return $query->where('situacao', 'aguardando');
    }

    /** Filtra pela empresa */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('empresa_id', $companyId);
    }

    // ── Helpers de status ───────────────────────────────────────────────────

    public function isWaiting(): bool
    {
        return $this->situacao === 'aguardando';
    }

    public function isInProgress(): bool
    {
        return $this->situacao === 'em_atendimento';
    }

    public function isDone(): bool
    {
        return $this->situacao === 'concluido';
    }

    public function isCancelled(): bool
    {
        return $this->situacao === 'cancelado';
    }

    public function isClosed(): bool
    {
        return in_array($this->situacao, ['concluido', 'cancelado']);
    }

    /** Tempo de espera formatado */
    public function getWaitingTimeAttribute(): string
    {
        return $this->chamado_em->diffForHumans();
    }
}
