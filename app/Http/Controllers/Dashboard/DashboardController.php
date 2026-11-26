<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\QueueCall;
use App\Models\RestaurantTable;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->empresa_id;

        $callStats = QueueCall::query()
            ->when($companyId, fn ($query) => $query->forCompany($companyId))
            ->selectRaw(
                'SUM(CASE WHEN situacao = ? THEN 1 ELSE 0 END) AS waiting_count,
                SUM(CASE WHEN situacao = ? THEN 1 ELSE 0 END) AS in_progress_count,
                SUM(CASE WHEN DATE(chamado_em) = ? THEN 1 ELSE 0 END) AS total_today',
                ['aguardando', 'em_atendimento', today()->toDateString()]
            )
            ->first();

        $waitingCount = (int) $callStats->waiting_count;
        $inProgressCount = (int) $callStats->in_progress_count;
        $totalToday = (int) $callStats->total_today;

        if ($companyId) {
            $tablesCount = RestaurantTable::where('empresa_id', $companyId)->where('ativo', true)->count();

            $recentCalls = QueueCall::forCompany($companyId)
                ->active()
                ->with('table')
                ->orderBy('posicao')
                ->take(10)
                ->get();
        } else {
            $tablesCount = RestaurantTable::where('ativo', true)->count();

            $recentCalls = QueueCall::active()
                ->with(['table', 'company'])
                ->orderBy('posicao')
                ->take(10)
                ->get();
        }

        return view('dashboard.index', compact(
            'waitingCount', 'inProgressCount', 'totalToday', 'tablesCount', 'recentCalls'
        ));
    }
}
