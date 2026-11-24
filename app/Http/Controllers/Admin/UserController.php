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
        $companies = Company::where('ativo', true)->orderBy('nome')->get();

        return view('admin.users.form', ['user' => null, 'companies' => $companies]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'empresa_id' => 'nullable|exists:empresas,id',
            'nome' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'perfil' => 'required|in:administrador,gerente,atendente',
            'ativo' => 'boolean',
        ]);

        $data['ativo'] = $request->boolean('ativo');

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', '✅ Usuário criado!');
    }

    public function edit(User $user)
    {
        $companies = Company::where('ativo', true)->orderBy('nome')->get();

        return view('admin.users.form', compact('user', 'companies'));
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
