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
            ->orderBy('position');

        if ($user->company_id) {
            $query->forCompany($user->company_id);
        }

        return view('livewire.queue.live-queue', [
            'calls' => $query->get(),
        ]);
    }
}
