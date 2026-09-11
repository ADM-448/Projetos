<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Exibe o formulário de edição do perfil (Meus Dados)
     */
    public function edit(): View
    {
        $user = auth()->user()->load(['departamento', 'cargo', 'gestor']);
        return view('profile.edit', compact('user'));
    }

    /**
     * Atualiza as informações do perfil do usuário autenticado
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $isMaster = $user->perfil_acesso === UserRole::MASTER;

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'telefone' => 'nullable|string|max:25',
        ];

        // Apenas Master pode alterar a senha no perfil
        if ($isMaster && $request->filled('password')) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $request->validate($rules);

        // Atualiza campos permitidos para todos
        $user->name = $request->name;
        $user->email = $request->email;
        
        // Normaliza telefone (apenas números)
        $user->telefone = $request->telefone ? preg_replace('/\D/', '', $request->telefone) : null;

        // Se for Master e preencheu senha, atualiza
        if ($isMaster && $request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Registra atividade se AtividadeService existir
        if (class_exists(\App\Services\AtividadeService::class)) {
            app(\App\Services\AtividadeService::class)->registrar(
                $user->empresa_id,
                $user->id,
                'Perfil Atualizado',
                'Você atualizou os seus dados cadastrais.',
                'fa-solid fa-user-pen',
                '#3b82f6'
            );
        }

        return redirect()->route('profile.edit')->with('success', 'Perfil atualizado com sucesso!');
    }
}
