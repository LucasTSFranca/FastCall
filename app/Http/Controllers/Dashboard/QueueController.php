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
        return auth()->user()->empresa_id;
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
            ->with('table:id,identificador,nome_exibicao')
            ->orderBy('posicao');

        if ($this->companyId()) {
            $query->forCompany($this->companyId());
        }

        $calls = $query->get([
            'id',
            'mesa_id',
            'empresa_id',
            'posicao',
            'tipo_servico',
            'situacao',
            'chamado_em',
        ]);

        return response()->json($calls);
    }

    /** Atendente assume o chamado e altera sua situação para em atendimento. */
    public function attend(QueueCall $call)
    {
        $this->authorizeForCompany($call);

        $call->update([
            'situacao' => 'em_atendimento',
            'atendente_id' => auth()->id(),
            'atendimento_iniciado_em' => now(),
        ]);

        $mesa = $call->table->display_name;

        return back()->with('success', "✅ {$mesa} em atendimento.");
    }

    /** Fecha um único chamado */
    public function close(QueueCall $call)
    {
        $this->authorizeForCompany($call);

        $call->update([
            'situacao' => 'concluido',
            'encerrado_em' => now(),
        ]);

        return back()->with('success', 'Chamado encerrado com sucesso.');
    }

    /** Fecha múltiplos chamados selecionados de uma só vez */
    public function closeBatch(Request $request)
    {
        $validated = $request->validate([
            'chamado_ids' => 'required|array|min:1',
            'chamado_ids.*' => 'integer|exists:chamados,id',
        ]);

        $query = QueueCall::whereIn('id', $validated['chamado_ids'])->active();

        if ($this->companyId()) {
            $query->where('empresa_id', $this->companyId());
        }

        $count = $query->update([
            'situacao' => 'concluido',
            'encerrado_em' => now(),
            'atendente_id' => auth()->id(),
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
            $query->where('empresa_id', $this->companyId());
        }

        $count = $query->update([
            'situacao' => 'concluido',
            'encerrado_em' => now(),
            'atendente_id' => auth()->id(),
        ]);

        return back()->with('success', "🗑️ Fila limpa! {$count} chamado(s) encerrado(s).");
    }

    /** Garante que o chamado pertence à empresa do usuário logado */
    private function authorizeForCompany(QueueCall $call): void
    {
        if (auth()->user()->isAdmin()) {
            return;
        }

        abort_if($call->empresa_id !== $this->companyId(), 403, 'Acesso não autorizado a este chamado.');
    }
}
