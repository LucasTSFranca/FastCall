<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nome',
        'identificador',
        'caminho_logo',
        'cnpj',
        'tema',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class, 'empresa_id');
    }

    public function queueCalls(): HasMany
    {
        return $this->hasMany(QueueCall::class, 'empresa_id');
    }

    /** Chamados ativos (esperando ou em atendimento), ordenados por posição */
    public function activeQueue(): HasMany
    {
        return $this->hasMany(QueueCall::class)
            ->whereIn('situacao', ['aguardando', 'em_atendimento'])
            ->orderBy('posicao');
    }
}
