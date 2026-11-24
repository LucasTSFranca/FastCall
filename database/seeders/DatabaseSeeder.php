<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Empresa de demonstração ──────────────────────────────────────
        $company = Company::create([
            'name'      => 'Restaurante FastCall Demo',
            'slug'      => 'fastcall-demo',
            'cnpj'      => '00.000.000/0001-00',
            'theme'     => 'dark',
            'is_active' => true,
        ]);

        // ── Administrador global (sem empresa) ──────────────────────────
        User::create([
            'company_id'       => null,
            'name'             => 'Administrador',
            'email'            => 'admin@fastcall.com',
            'password'         => Hash::make('admin123'),
            'role'             => 'admin',
            'is_active'        => true,
            'theme_preference' => 'dark',
        ]);

        // ── Gerente da empresa de demonstração ───────────────────────────
        User::create([
            'company_id'       => $company->id,
            'name'             => 'Gerente Demo',
            'email'            => 'gerente@fastcall.com',
            'password'         => Hash::make('password'),
            'role'             => 'manager',
            'is_active'        => true,
            'theme_preference' => 'dark',
        ]);

        // ── Atendentes de demonstração ───────────────────────────────────
        User::create([
            'company_id'       => $company->id,
            'name'             => 'Ana Atendente',
            'email'            => 'atendente@fastcall.com',
            'password'         => Hash::make('password'),
            'role'             => 'attendant',
            'is_active'        => true,
            'theme_preference' => 'dark',
        ]);

        User::create([
            'company_id'       => $company->id,
            'name'             => 'Carlos Atendente',
            'email'            => 'carlos@fastcall.com',
            'password'         => Hash::make('password'),
            'role'             => 'attendant',
            'is_active'        => true,
            'theme_preference' => 'blue',
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
                'company_id' => $company->id,
                'identifier' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'label'      => "Mesa {$i} — {$labels[$i - 1]}",
                'qr_token'   => Str::random(40) . $company->id,
                'is_active'  => true,
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
