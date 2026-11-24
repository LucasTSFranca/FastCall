<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Nomeado RestaurantTable para evitar conflito com a classe Table do Laravel/PHP.
 */
class RestaurantTable extends Model
{
    use SoftDeletes;

    protected $table = 'mesas';

    protected $fillable = [
        'empresa_id',
        'identificador',
        'nome_exibicao',
        'token_qr',
        'identificador_etiqueta_nfc',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $table) {
            if (empty($table->token_qr)) {
                $table->token_qr = Str::random(40).$table->empresa_id;
            }
        });
    }

    // ── Relacionamentos ─────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'empresa_id');
    }

    public function queueCalls(): HasMany
    {
        return $this->hasMany(QueueCall::class, 'mesa_id');
    }

    /** Chamado ativo atual desta mesa (mais recente) */
    public function activeCall(): HasOne
    {
        return $this->hasOne(QueueCall::class, 'mesa_id')
            ->whereIn('situacao', ['aguardando', 'em_atendimento'])
            ->latestOfMany('chamado_em');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    public function isCurrentlyWaiting(): bool
    {
        return $this->activeCall()->exists();
    }

    /** Retorna o nome configurado ou usa o identificador da mesa. */
    public function getDisplayNameAttribute(): string
    {
        return $this->nome_exibicao ?? "Mesa {$this->identificador}";
    }

    /** URL completa do QR Code para esta mesa */
    public function getQrUrlAttribute(): string
    {
        return route('client.call.show', $this->token_qr);
    }
}
