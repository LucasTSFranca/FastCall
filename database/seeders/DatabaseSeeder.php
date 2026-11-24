<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Empresa de demonstração ──────────────────────────────────────
        $company = Company::create([
            'nome' => 'Restaurante FastCall Demo',
            'identificador' => 'fastcall-demo',
            'cnpj' => '00.000.000/0001-00',
            'tema' => 'dark',
            'ativo' => true,
        ]);

        // ── Administrador global (sem empresa) ──────────────────────────
        User::create([
            'empresa_id' => null,
            'nome' => 'Administrador',
            'email' => 'admin@fastcall.com',
            'password' => Hash::make('admin123'),
            'perfil' => 'administrador',
            'ativo' => true,
            'tema_preferido' => 'dark',
        ]);

        // ── Gerente da empresa de demonstração ───────────────────────────
        User::create([
            'empresa_id' => $company->id,
            'nome' => 'Gerente Demo',
            'email' => 'gerente@fastcall.com',
            'password' => Hash::make('password'),
            'perfil' => 'gerente',
            'ativo' => true,
            'tema_preferido' => 'dark',
        ]);

        // ── Atendentes de demonstração ───────────────────────────────────
        User::create([
            'empresa_id' => $company->id,
            'nome' => 'Ana Atendente',
            'email' => 'atendente@fastcall.com',
            'password' => Hash::make('password'),
            'perfil' => 'atendente',
            'ativo' => true,
            'tema_preferido' => 'dark',
        ]);

        User::create([
            'empresa_id' => $company->id,
            'nome' => 'Carlos Atendente',
            'email' => 'carlos@fastcall.com',
            'password' => Hash::make('password'),
            'perfil' => 'atendente',
            'ativo' => true,
            'tema_preferido' => 'personalizado',
        ]);

        // ── Mesas de demonstração ────────────────────────────────────────
        $labels = [
            'Área Interna',
            'Área Interna',
            'Área Interna',
            'Área Interna',
            'Varanda',
            'Varanda',
            'Área VIP',
            'Área VIP',
            'Salão Principal',
            'Salão Principal',
        ];

        foreach (range(1, 10) as $i) {
            RestaurantTable::create([
                'empresa_id' => $company->id,
                'identificador' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'nome_exibicao' => "Mesa {$i} — {$labels[$i - 1]}",
                'ativo' => true,
            ]);
        }

        $this->command->info('✅ Dados de demonstração cadastrados!');
        $this->command->line('');
        $this->command->line('  Administrador: admin@fastcall.com / admin123');
        $this->command->line('  Gerente:       gerente@fastcall.com / password');
        $this->command->line('  Atendente:     atendente@fastcall.com / password');
        $this->command->line('  Atendente:     carlos@fastcall.com / password');
    }
}
