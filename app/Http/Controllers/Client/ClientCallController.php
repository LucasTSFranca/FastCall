<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\QueueCall;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ClientCallController extends Controller
{
    /** Exibe a página mobile do cliente ao escanear o QR Code */
    public function show(string $qrToken)
    {
        $table = RestaurantTable::where('qr_token', $qrToken)
                                ->where('is_active', true)
                                ->with('company')
                                ->firstOrFail();

        $activeCall = $table->activeCall;

        return view('client.call', compact('table', 'activeCall'));
    }

    /** Registra um novo chamado na fila */
    public function store(string $qrToken): JsonResponse
    {
        $table = RestaurantTable::where('qr_token', $qrToken)
                                ->where('is_active', true)
                                ->firstOrFail();

        // Evita duplicata: mesa já está na fila ativa
        if ($table->isCurrentlyWaiting()) {
            $call = $table->activeCall;
            return response()->json([
                'success'  => true,
                'call_id'  => $call->id,
                'position' => $call->position,
                'message'  => 'Você já está na fila!',
            ]);
        }

        $call = DB::transaction(function () use ($table) {
            $nextPosition = QueueCall::forCompany($table->company_id)
                                     ->active()
                                     ->max('position') + 1;

            return QueueCall::create([
                'company_id' => $table->company_id,
                'table_id'   => $table->id,
                'position'   => $nextPosition,
                'status'     => 'waiting',
                'called_at'  => now(),
            ]);
        });

        return response()->json([
            'success'  => true,
            'call_id'  => $call->id,
            'position' => $call->position,
        ]);
    }

    /** Retorna a posição atual na fila (endpoint de polling AJAX) */
    public function position(string $qrToken, int $callId): JsonResponse
    {
        $call = QueueCall::with('table')->find($callId);

        if (! $call || $call->isClosed()) {
            return response()->json(['status' => 'closed']);
        }

        // Quantos chamados com status 'waiting' estão à frente na fila
        $ahead = QueueCall::forCompany($call->company_id)
                          ->waiting()
                          ->where('position', '<', $call->position)
                          ->count();

        return response()->json([
            'status'   => $call->status,
            'position' => $call->position,
            'ahead'    => $ahead,
        ]);
    }

    /** Cancela o chamado (botão de desistência do cliente) */
    public function cancel(string $qrToken): JsonResponse
    {
        $table = RestaurantTable::where('qr_token', $qrToken)
                                ->where('is_active', true)
                                ->firstOrFail();

        $call = QueueCall::where('table_id', $table->id)
                         ->active()
                         ->latest('called_at')
                         ->first();

        if ($call) {
            $call->update([
                'status'    => 'cancelled',
                'closed_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
