<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('users')->orderBy('nome')->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.companies.form', ['company' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:150',
            'cnpj' => 'nullable|string|max:18',
            'tema' => ['required', Rule::in(array_keys(config('themes')))],
            'ativo' => 'boolean',
        ]);

        $data['identificador'] = Str::slug($data['nome']);
        $data['ativo'] = $request->boolean('ativo');

        Company::create($data);

        return redirect()->route('admin.companies.index')->with('success', '✅ Empresa criada!');
    }

    public function edit(Company $company)
    {
        return view('admin.companies.form', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:150',
            'cnpj' => 'nullable|string|max:18',
            'tema' => ['required', Rule::in(array_keys(config('themes')))],
            'ativo' => 'boolean',
        ]);

        $data['identificador'] = Str::slug($data['nome']);
        $data['ativo'] = $request->boolean('ativo');

        $company->update($data);

        return redirect()->route('admin.companies.index')->with('success', '✅ Empresa atualizada!');
    }

    public function toggle(Company $company)
    {
        $company->update(['ativo' => ! $company->ativo]);
        $status = $company->ativo ? 'ativada' : 'desativada';

        return back()->with('success', "Empresa {$company->nome} {$status} com sucesso!");
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return back()->with('success', 'Empresa removida.');
    }
}
