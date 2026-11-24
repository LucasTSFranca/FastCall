<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    private function companyId(): ?int
    {
        return auth()->user()->company_id;
    }

    public function index()
    {
        $query = RestaurantTable::with('company')
                                 ->withCount([
                                     'queueCalls as active_calls_count' => fn ($q) => $q->active(),
                                 ])
                                 ->orderBy('identifier');

        if ($this->companyId()) {
            $query->where('company_id', $this->companyId());
        }

        $tables = $query->paginate(20);

        return view('dashboard.tables.index', compact('tables'));
    }

    public function create()
    {
        $companies = $this->companyId()
            ? null
            : \App\Models\Company::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.tables.form', ['table' => null, 'companies' => $companies]);
    }

    public function store(Request $request)
    {
        $rules = [
            'identifier' => 'required|string|max:20',
            'label'      => 'nullable|string|max:100',
            'nfc_tag_id' => 'nullable|string|max:100',
            'is_active'  => 'boolean',
        ];

        if (! $this->companyId()) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $data = $request->validate($rules);

        $companyId          = $this->companyId() ?? (int) $data['company_id'];
        $data['company_id'] = $companyId;
        $data['qr_token']   = Str::random(40) . $companyId;
        $data['is_active']  = $request->boolean('is_active');

        RestaurantTable::create($data);

        return redirect()->route('tables.index')->with('success', '✅ Mesa criada com sucesso!');
    }

    public function edit(RestaurantTable $table)
    {
        $this->authorizeForTable($table);

        $companies = $this->companyId()
            ? null
            : \App\Models\Company::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.tables.form', compact('table', 'companies'));
    }

    public function update(Request $request, RestaurantTable $table)
    {
        $this->authorizeForTable($table);

        $rules = [
            'identifier' => 'required|string|max:20',
            'label'      => 'nullable|string|max:100',
            'nfc_tag_id' => 'nullable|string|max:100',
            'is_active'  => 'boolean',
        ];

        if (! $this->companyId()) {
            $rules['company_id'] = $table->company_id ? 'nullable|exists:companies,id' : 'required|exists:companies,id';
        }

        $data = $request->validate($rules);

        if (! $this->companyId() && ! empty($data['company_id'])) {
            $data['company_id'] = (int) $data['company_id'];
        } else {
            unset($data['company_id']);
        }

        $data['is_active'] = $request->boolean('is_active');

        $table->update($data);

        return redirect()->route('tables.index')->with('success', '✅ Mesa atualizada!');
    }

    public function toggle(RestaurantTable $table)
    {
        $this->authorizeForTable($table);

        $table->update(['is_active' => ! $table->is_active]);
        $status = $table->is_active ? 'ativada' : 'desativada';

        return back()->with('success', "Mesa {$table->identifier} {$status} com sucesso!");
    }

    public function destroy(RestaurantTable $table)
    {
        $this->authorizeForTable($table);

        $table->delete();

        return back()->with('success', 'Mesa removida.');
    }

    /** Exibe a página com o QR Code da mesa para impressão/download */
    public function qr(RestaurantTable $table)
    {
        $this->authorizeForTable($table);

        $url    = route('client.call.show', $table->qr_token);
        $qrCode = QrCode::format('svg')->size(300)->errorCorrection('H')->generate($url);

        return view('dashboard.tables.qr', compact('table', 'qrCode', 'url'));
    }

    private function authorizeForTable(RestaurantTable $table): void
    {
        if (auth()->user()->isAdmin()) {
            return;
        }

        abort_if($table->company_id !== $this->companyId(), 403);
    }
}
