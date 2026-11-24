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
        $users = User::with('company')->orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        return view('admin.users.form', ['user' => null, 'companies' => $companies]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name'       => 'required|string|max:150',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'required|in:admin,manager,attendant',
            'is_active'  => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', '✅ Usuário criado!');
    }

    public function edit(User $user)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        return view('admin.users.form', compact('user', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name'       => 'required|string|max:150',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'password'   => 'nullable|string|min:8|confirmed',
            'role'       => 'required|in:admin,manager,attendant',
            'is_active'  => 'boolean',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        if ($user->id === auth()->id() && ! $data['is_active']) {
            return back()->withInput()->with('error', 'Você não pode desativar sua própria conta.');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', '✅ Usuário atualizado!');
    }

    public function toggle(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Você não pode desativar seu próprio usuário.');

        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'ativado' : 'desativado';

        return back()->with('success', "Usuário {$user->name} {$status} com sucesso!");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Você não pode remover a si mesmo.');
        $user->delete();
        return back()->with('success', 'Usuário removido.');
    }
}
