<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Nomeado RestaurantTable para evitar conflito com a classe Table do Laravel/PHP.
 * A tabela no banco continua sendo "tables".
 */
class RestaurantTable extends Model
{
    use SoftDeletes;

    protected $table = 'tables';

    protected $fillable = [
        'company_id',
        'identifier',
        'label',
        'qr_token',
        'nfc_tag_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $table) {
            if (empty($table->qr_token)) {
                $table->qr_token = Str::random(40) . $table->company_id;
            }
        });
    }

    // ── Relacionamentos ─────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function queueCalls(): HasMany
    {
        return $this->hasMany(QueueCall::class, 'table_id');
    }

    /** Chamado ativo atual desta mesa (mais recente) */
    public function activeCall(): HasOne
    {
        return $this->hasOne(QueueCall::class, 'table_id')
                    ->whereIn('status', ['waiting', 'in_progress'])
                    ->latestOfMany('called_at');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    public function isCurrentlyWaiting(): bool
    {
        return $this->activeCall()->exists();
    }

    /** Nome de exibição: label se tiver, senão "Mesa {identifier}" */
    public function getDisplayNameAttribute(): string
    {
        return $this->label ?? "Mesa {$this->identifier}";
    }

    /** URL completa do QR Code para esta mesa */
    public function getQrUrlAttribute(): string
    {
        return route('client.call.show', $this->qr_token);
    }
}
