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
        $companyId = $user->company_id;

        if ($companyId) {
            $waitingCount    = QueueCall::forCompany($companyId)->waiting()->count();
            $inProgressCount = QueueCall::forCompany($companyId)->where('status', 'in_progress')->count();
            $totalToday      = QueueCall::forCompany($companyId)->whereDate('called_at', today())->count();
            $tablesCount     = RestaurantTable::where('company_id', $companyId)->where('is_active', true)->count();

            $recentCalls = QueueCall::forCompany($companyId)
                                    ->active()
                                    ->with('table')
                                    ->orderBy('position')
                                    ->take(10)
                                    ->get();
        } else {
            $waitingCount    = QueueCall::waiting()->count();
            $inProgressCount = QueueCall::where('status', 'in_progress')->count();
            $totalToday      = QueueCall::whereDate('called_at', today())->count();
            $tablesCount     = RestaurantTable::where('is_active', true)->count();

            $recentCalls = QueueCall::active()
                                    ->with(['table', 'company'])
                                    ->orderBy('position')
                                    ->take(10)
                                    ->get();
        }

        return view('dashboard.index', compact(
            'waitingCount', 'inProgressCount', 'totalToday', 'tablesCount', 'recentCalls'
        ));
    }
}
