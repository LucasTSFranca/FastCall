<?php

namespace App\Livewire\Queue;

use App\Models\QueueCall;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LiveQueue extends Component
{
    public function render(): View
    {
        $user = auth()->user();
        $query = QueueCall::active()
            ->with(['table', 'attendant', 'company'])
            ->orderBy('posicao');

        if ($user->empresa_id) {
            $query->forCompany($user->empresa_id);
        }

        $calls = $query->get();
        $waitingPositions = [];

        foreach ($calls as $call) {
            $companyId = $call->empresa_id;
            $waitingPositions[$companyId] ??= 0;

            $call->setAttribute(
                'posicao_atual',
                $call->isWaiting() ? ++$waitingPositions[$companyId] : null
            );
        }

        $overdueCalls = $calls->filter(
            fn (QueueCall $call) => $call->isWaiting()
                && $call->chamado_em->lt(now()->subMinutes(5))
        );

        return view('livewire.queue.live-queue', [
            'calls' => $calls,
            'overdueCalls' => $overdueCalls,
        ]);
    }
}
