<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    private function companyId(): ?int
    {
        return auth()->user()->empresa_id;
    }

    public function index()
    {
        $query = RestaurantTable::with('company')
            ->withCount([
                'queueCalls as active_calls_count' => fn ($q) => $q->active(),
            ])
            ->orderBy('identificador');

        if ($this->companyId()) {
            $query->where('empresa_id', $this->companyId());
        }

        $tables = $query->paginate(20);

        return view('dashboard.tables.index', compact('tables'));
    }

    public function create()
    {
        $companies = $this->companyId()
            ? null
            : Company::where('ativo', true)->orderBy('nome')->get();

        return view('dashboard.tables.form', ['table' => null, 'companies' => $companies]);
    }

    public function store(Request $request)
    {
        $rules = [
            'identificador' => 'required|string|max:20',
            'nome_exibicao' => 'nullable|string|max:100',
            'identificador_etiqueta_nfc' => 'nullable|string|max:100',
            'ativo' => 'boolean',
        ];

        if (! $this->companyId()) {
            $rules['empresa_id'] = 'required|exists:empresas,id';
        }

        $data = $request->validate($rules);

        $companyId = $this->companyId() ?? (int) $data['empresa_id'];
        $data['empresa_id'] = $companyId;
        $data['token_qr'] = Str::random(40).$companyId;
        $data['ativo'] = $request->boolean('ativo');

        RestaurantTable::create($data);

        return redirect()->route('tables.index')->with('success', '✅ Mesa criada com sucesso!');
    }

    public function edit(RestaurantTable $table)
    {
        $this->authorizeForTable($table);

        $companies = $this->companyId()
            ? null
            : Company::where('ativo', true)->orderBy('nome')->get();

        return view('dashboard.tables.form', compact('table', 'companies'));
    }

    public function update(Request $request, RestaurantTable $table)
    {
        $this->authorizeForTable($table);

        $rules = [
            'identificador' => 'required|string|max:20',
            'nome_exibicao' => 'nullable|string|max:100',
            'identificador_etiqueta_nfc' => 'nullable|string|max:100',
            'ativo' => 'boolean',
        ];

        if (! $this->companyId()) {
            $rules['empresa_id'] = $table->empresa_id ? 'nullable|exists:empresas,id' : 'required|exists:empresas,id';
        }

        $data = $request->validate($rules);

        if (! $this->companyId() && ! empty($data['empresa_id'])) {
            $data['empresa_id'] = (int) $data['empresa_id'];
        } else {
            unset($data['empresa_id']);
        }

        $data['ativo'] = $request->boolean('ativo');

        $table->update($data);

        return redirect()->route('tables.index')->with('success', '✅ Mesa atualizada!');
    }

    public function toggle(RestaurantTable $table)
    {
        $this->authorizeForTable($table);

        $table->update(['ativo' => ! $table->ativo]);
        $status = $table->ativo ? 'ativada' : 'desativada';

        return back()->with('success', "Mesa {$table->identificador} {$status} com sucesso!");
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

        $url = route('client.call.show', $table->token_qr);
        $qrCode = QrCode::format('svg')->size(300)->errorCorrection('H')->generate($url);

        return view('dashboard.tables.qr', compact('table', 'qrCode', 'url'));
    }

    private function authorizeForTable(RestaurantTable $table): void
    {
        if (auth()->user()->isAdmin()) {
            return;
        }

        abort_if($table->empresa_id !== $this->companyId(), 403);
    }
}
