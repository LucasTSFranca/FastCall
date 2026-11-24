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

        if ($companyId) {
            $waitingCount = QueueCall::forCompany($companyId)->waiting()->count();
            $inProgressCount = QueueCall::forCompany($companyId)->where('situacao', 'em_atendimento')->count();
            $totalToday = QueueCall::forCompany($companyId)->whereDate('chamado_em', today())->count();
            $tablesCount = RestaurantTable::where('empresa_id', $companyId)->where('ativo', true)->count();

            $recentCalls = QueueCall::forCompany($companyId)
                ->active()
                ->with('table')
                ->orderBy('posicao')
                ->take(10)
                ->get();
        } else {
            $waitingCount = QueueCall::waiting()->count();
            $inProgressCount = QueueCall::where('situacao', 'em_atendimento')->count();
            $totalToday = QueueCall::whereDate('chamado_em', today())->count();
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
