<?php

namespace App\Http\Controllers;

use App\Models\Convencao;
use Illuminate\Http\Request;

class ConvencaoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sindicato_nome' => 'required|string|max:255',
            'ano_vigencia' => 'required|string|max:255',
            'data_base' => 'required|string|max:50',
            'reajuste_geral_percentual' => 'required|numeric|min:0',
            'regras' => 'array',
            'regras.*.tipo_regra' => 'required|in:piso_cargo,beneficio_fixo,adicional_percentual',
            'regras.*.descricao' => 'required|string|max:255',
            'regras.*.valor' => 'required|numeric|min:0',
        ]);

        $convencao = Convencao::create([
            'sindicato_nome' => $validated['sindicato_nome'],
            'ano_vigencia' => $validated['ano_vigencia'],
            'data_base' => $validated['data_base'],
            'reajuste_geral_percentual' => $validated['reajuste_geral_percentual'],
        ]);

        if (! empty($validated['regras'])) {
            $convencao->regras()->createMany($validated['regras']);
        }

        return redirect()->back()->with('success', 'Convenção cadastrada com sucesso!');
    }

    public function update(Request $request, Convencao $convencao)
    {
        $validated = $request->validate([
            'sindicato_nome' => 'required|string|max:255',
            'ano_vigencia' => 'required|string|max:255',
            'data_base' => 'required|string|max:50',
            'reajuste_geral_percentual' => 'required|numeric|min:0',
            'regras' => 'array',
            'regras.*.tipo_regra' => 'required|in:piso_cargo,beneficio_fixo,adicional_percentual',
            'regras.*.descricao' => 'required|string|max:255',
            'regras.*.valor' => 'required|numeric|min:0',
        ]);

        // 1. Atualiza os dados principais da convenção
        $convencao->update([
            'sindicato_nome' => $validated['sindicato_nome'],
            'ano_vigencia' => $validated['ano_vigencia'],
            'data_base' => $validated['data_base'],
            'reajuste_geral_percentual' => $validated['reajuste_geral_percentual'],
        ]);

        // 2. Remove as regras antigas e insere as novas (evita duplicidade e órfãos)
        $convencao->regras()->delete();

        if (! empty($validated['regras'])) {
            $convencao->regras()->createMany($validated['regras']);
        }

        return redirect()->back()->with('success', 'Convenção atualizada com sucesso!');
    }

    public function destroy(Convencao $convencao)
    {
        $convencao->delete();

        return redirect()->back()->with('success', 'Convenção removida com sucesso!');
    }
}
