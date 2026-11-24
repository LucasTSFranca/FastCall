<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\QueueCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    private function companyId(): ?int
    {
        return auth()->user()->company_id;
    }

    /** Painel da fila — a lista é atualizada pelo componente Livewire. */
    public function index()
    {
        return view('dashboard.queue.index');
    }

    /** Endpoint de polling — retorna JSON com estado atual da fila */
    public function live(): JsonResponse
    {
        $query = QueueCall::active()
            ->with('table:id,identifier,label')
            ->orderBy('position');

        if ($this->companyId()) {
            $query->forCompany($this->companyId());
        }

        $calls = $query->get(['id', 'table_id', 'company_id', 'position', 'status', 'called_at']);

        return response()->json($calls);
    }

    /** Atendente assume o chamado (muda status para in_progress) */
    public function attend(QueueCall $call)
    {
        $this->authorizeForCompany($call);

        $call->update([
            'status' => 'in_progress',
            'attended_by' => auth()->id(),
            'attended_at' => now(),
        ]);

        $mesa = $call->table->display_name;

        return back()->with('success', "✅ {$mesa} em atendimento.");
    }

    /** Fecha um único chamado */
    public function close(QueueCall $call)
    {
        $this->authorizeForCompany($call);

        $call->update([
            'status' => 'done',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Chamado encerrado com sucesso.');
    }

    /** Fecha múltiplos chamados selecionados de uma só vez */
    public function closeBatch(Request $request)
    {
        $validated = $request->validate([
            'call_ids' => 'required|array|min:1',
            'call_ids.*' => 'integer|exists:queue_calls,id',
        ]);

        $query = QueueCall::whereIn('id', $validated['call_ids'])->active();

        if ($this->companyId()) {
            $query->where('company_id', $this->companyId());
        }

        $count = $query->update([
            'status' => 'done',
            'closed_at' => now(),
            'attended_by' => auth()->id(),
        ]);

        return back()->with('success', "✅ {$count} chamado(s) encerrado(s) com sucesso.");
    }

    /**
     * Limpa TODA a fila da empresa.
     * Ação de emergência / fim de expediente.
     * Requer que o usuário digite "CONFIRMAR" para evitar acidentes.
     */
    public function clearAll(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:CONFIRMAR',
        ], [
            'confirm.in' => 'Você deve digitar exatamente "CONFIRMAR" para prosseguir.',
        ]);

        $query = QueueCall::active();

        if ($this->companyId()) {
            $query->where('company_id', $this->companyId());
        }

        $count = $query->update([
            'status' => 'done',
            'closed_at' => now(),
            'attended_by' => auth()->id(),
        ]);

        return back()->with('success', "🗑️ Fila limpa! {$count} chamado(s) encerrado(s).");
    }

    /** Garante que o chamado pertence à empresa do usuário logado */
    private function authorizeForCompany(QueueCall $call): void
    {
        if (auth()->user()->isAdmin()) {
            return;
        }

        abort_if($call->company_id !== $this->companyId(), 403, 'Acesso não autorizado a este chamado.');
    }
}
