<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'cnpj',
        'theme',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function queueCalls(): HasMany
    {
        return $this->hasMany(QueueCall::class);
    }

    /** Chamados ativos (esperando ou em atendimento), ordenados por posição */
    public function activeQueue(): HasMany
    {
        return $this->hasMany(QueueCall::class)
                    ->whereIn('status', ['waiting', 'in_progress'])
                    ->orderBy('position');
    }
}
