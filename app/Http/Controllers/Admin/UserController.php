<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('company')->orderBy('nome')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $isAdmin = auth()->user()->isAdmin();
        abort_if(! $isAdmin && (! auth()->user()->isManager() || ! auth()->user()->empresa_id), 403);

        $companies = $isAdmin
            ? Company::where('ativo', true)->orderBy('nome')->get()
            : collect();

        return view('admin.users.form', [
            'user' => null,
            'companies' => $companies,
            'isAdmin' => $isAdmin,
            'company' => $isAdmin ? null : auth()->user()->company,
        ]);
    }

    public function store(Request $request)
    {
        $isAdmin = auth()->user()->isAdmin();
        abort_if(! $isAdmin && (! auth()->user()->isManager() || ! auth()->user()->empresa_id), 403);

        $rules = [
            'nome' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'ativo' => 'boolean',
        ];
        $rules['perfil'] = $isAdmin
            ? 'required|in:administrador,gerente,atendente'
            : 'required|in:atendente';

        if ($isAdmin) {
            $rules['empresa_id'] = 'nullable|exists:empresas,id';
        }

        $data = $request->validate($rules);
        $data['empresa_id'] = $isAdmin ? ($data['empresa_id'] ?? null) : auth()->user()->empresa_id;

        $data['ativo'] = $request->boolean('ativo');

        User::create($data);

        $destination = $isAdmin ? 'admin.users.index' : 'queue.index';

        return redirect()->route($destination)->with('success', '✅ Funcionário criado!');
    }

    public function edit(User $user)
    {
        $companies = Company::where('ativo', true)->orderBy('nome')->get();

        return view('admin.users.form', [
            'user' => $user,
            'companies' => $companies,
            'isAdmin' => true,
            'company' => null,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'empresa_id' => 'nullable|exists:empresas,id',
            'nome' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'perfil' => 'required|in:administrador,gerente,atendente',
            'ativo' => 'boolean',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['ativo'] = $request->boolean('ativo');

        if ($user->id === auth()->id() && ! $data['ativo']) {
            return back()->withInput()->with('error', 'Você não pode desativar sua própria conta.');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', '✅ Usuário atualizado!');
    }

    public function toggle(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Você não pode desativar seu próprio usuário.');

        $user->update(['ativo' => ! $user->ativo]);
        $status = $user->ativo ? 'ativado' : 'desativado';

        return back()->with('success', "Usuário {$user->nome} {$status} com sucesso!");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Você não pode remover a si mesmo.');
        $user->delete();

        return back()->with('success', 'Usuário removido.');
    }
}
