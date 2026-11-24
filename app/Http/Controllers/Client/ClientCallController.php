<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\QueueCall;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientCallController extends Controller
{
    /** Exibe a página mobile do cliente ao escanear o QR Code */
    public function show(string $qrToken)
    {
        $table = RestaurantTable::where('token_qr', $qrToken)
            ->where('ativo', true)
            ->with('company')
            ->firstOrFail();

        $activeCall = $table->activeCall;
        $activePosition = $activeCall ? $this->currentPosition($activeCall) : null;

        return view('client.call', compact('table', 'activeCall', 'activePosition'));
    }

    /** Registra um novo chamado na fila */
    public function store(Request $request, string $qrToken): JsonResponse
    {
        $validated = $request->validate([
            'tipo_servico' => ['required', Rule::in(array_keys(QueueCall::SERVICE_TYPES))],
        ]);

        $table = RestaurantTable::where('token_qr', $qrToken)
            ->where('ativo', true)
            ->firstOrFail();

        // Evita duplicata: mesa já está na fila ativa
        if ($table->isCurrentlyWaiting()) {
            $call = $table->activeCall;

            return response()->json([
                'success' => true,
                'call_id' => $call->id,
                'position' => $this->currentPosition($call),
                'tipo_servico' => $call->tipo_servico,
                'message' => 'Você já está na fila!',
            ]);
        }

        $call = DB::transaction(function () use ($table, $validated) {
            $nextPosition = QueueCall::forCompany($table->empresa_id)
                ->active()
                ->max('posicao') + 1;

            return QueueCall::create([
                'empresa_id' => $table->empresa_id,
                'mesa_id' => $table->id,
                'posicao' => $nextPosition,
                'tipo_servico' => $validated['tipo_servico'],
                'situacao' => 'aguardando',
                'chamado_em' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'call_id' => $call->id,
            'position' => $this->currentPosition($call),
            'tipo_servico' => $call->tipo_servico,
        ]);
    }

    /** Retorna a posição atual na fila (endpoint de polling AJAX) */
    public function position(string $qrToken, int $callId): JsonResponse
    {
        $call = QueueCall::with('table')->find($callId);

        if (! $call || $call->isClosed()) {
            return response()->json(['status' => 'closed']);
        }

        $ahead = $call->isWaiting()
            ? $this->waitingCallsAhead($call)
            : 0;

        return response()->json([
            'status' => $call->situacao,
            'position' => $call->isWaiting() ? $ahead + 1 : null,
            'ahead' => $ahead,
        ]);
    }

    /** Cancela o chamado (botão de desistência do cliente) */
    public function cancel(string $qrToken): JsonResponse
    {
        $table = RestaurantTable::where('token_qr', $qrToken)
            ->where('ativo', true)
            ->firstOrFail();

        $call = QueueCall::where('mesa_id', $table->id)
            ->active()
            ->latest('chamado_em')
            ->first();

        if ($call) {
            $call->update([
                'situacao' => 'cancelado',
                'encerrado_em' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    private function currentPosition(QueueCall $call): ?int
    {
        return $call->isWaiting()
            ? $this->waitingCallsAhead($call) + 1
            : null;
    }

    private function waitingCallsAhead(QueueCall $call): int
    {
        return QueueCall::forCompany($call->empresa_id)
            ->waiting()
            ->where('posicao', '<', $call->posicao)
            ->count();
    }
}
