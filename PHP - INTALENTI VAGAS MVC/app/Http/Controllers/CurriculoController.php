<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\ExperienciaProfissional;
use App\Models\FormacaoAcademica;
use App\Models\CursoComplementar;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CurriculoController extends Controller
{
    public function __construct(protected GeoService $geoService)
    {
    }

    public function edit()
    {
        $user = Auth::user();

        if (!$user->isCandidato()) {
            abort(403, 'Apenas candidatos podem gerenciar currículo.');
        }

        $candidato = $user->candidato ?: Candidato::create([
            'user_id' => $user->id,
            'nome_completo' => $user->name,
            'nacionalidade' => 'Brasileiro(a)',
        ]);

        $candidato->load(['experiencias', 'formacoes', 'cursos']);

        $idiomasDisponiveis = [
            'Português', 'Inglês', 'Espanhol', 'Francês', 'Alemão', 'Italiano', 'Japonês', 'Mandarim', 'Libras', 'Outro'
        ];

        $niveisIdioma = ['Básico', 'Intermediário', 'Avançado', 'Fluente / Nativo'];

        $niveisEscolaridade = [
            'Ensino Fundamental Incompleto',
            'Ensino Fundamental Completo',
            'Ensino Médio Incompleto',
            'Ensino Médio Completo',
            'Técnico / Profissionalizante',
            'Ensino Superior Incompleto',
            'Ensino Superior Completo',
            'Pós-graduação / Especialização',
            'Mestrado',
            'Doutorado',
        ];

        return view('curriculo.edit', compact(
            'candidato',
            'idiomasDisponiveis',
            'niveisIdioma',
            'niveisEscolaridade'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $candidato = $user->candidato;

        $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date', 'before:today'],
            'celular' => ['required', 'string', 'max:20'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'cep' => ['nullable', 'string', 'max:10'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:2'],
            'sobre_mim' => ['nullable', 'string'],
            'resumo_profissional' => ['nullable', 'string', 'max:1000'],
            'situacao_profissional' => ['nullable', 'string'],
            'cargo_pretendido' => ['nullable', 'string'],
            'faixa_salarial' => ['nullable', 'string'],
            'escolaridade' => ['nullable', 'string'],
            'sexo' => ['nullable', 'string'],
            'estado_civil' => ['nullable', 'string'],
            'nacionalidade' => ['nullable', 'string'],
            'carteira_habilitacao' => ['nullable', 'string'],
            'curriculo_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // Máx 5MB
            'experiencias.*.empresa' => ['nullable', 'string', 'max:255'],
            'experiencias.*.cargo' => ['nullable', 'string', 'max:255'],
            'experiencias.*.data_inicio' => ['nullable', 'date', 'before_or_equal:today'],
            'formacoes.*.instituicao' => ['nullable', 'string', 'max:255'],
            'formacoes.*.curso' => ['nullable', 'string', 'max:255'],
            'formacoes.*.data_inicio' => ['nullable', 'date', 'before_or_equal:today'],
        ], [
            'nome_completo.required' => 'O nome completo é obrigatório.',
            'celular.required' => 'Informe o número de celular ou WhatsApp para contato.',
            'data_nascimento.before' => 'A data de nascimento não pode estar no futuro.',
            'curriculo_file.mimes' => 'O arquivo de currículo deve ser em formato PDF, DOC ou DOCX.',
            'curriculo_file.max' => 'O arquivo de currículo não pode exceder 5 MB.',
            'experiencias.*.data_inicio.before_or_equal' => 'A data de início da experiência não pode estar no futuro.',
            'formacoes.*.data_inicio.before_or_equal' => 'A data de início da formação não pode estar no futuro.',
        ]);

        $dados = $request->except(['curriculo_file', 'experiencias', 'formacoes', 'cursos', 'idiomas_itens']);

        // Geocodificação automática via GeoService
        if (!empty($dados['cep'])) {
            $geo = $this->geoService->consultarCep($dados['cep']);
            if ($geo) {
                $dados['latitude'] = $geo['latitude'];
                $dados['longitude'] = $geo['longitude'];
                if (empty($dados['cidade'])) $dados['cidade'] = $geo['cidade'];
                if (empty($dados['estado'])) $dados['estado'] = $geo['estado'];
            }
        }

        // Upload de currículo em PDF/Word
        if ($request->hasFile('curriculo_file')) {
            if ($candidato->curriculo_path) {
                Storage::disk('public')->delete($candidato->curriculo_path);
            }
            $dados['curriculo_path'] = $request->file('curriculo_file')->store('curriculos', 'public');
        }

        // Estruturação dos idiomas com nível (Item 309, 310)
        $idiomasInput = $request->input('idiomas_itens', []);
        $idiomasFormatados = [];
        foreach ($idiomasInput as $item) {
            if (!empty($item['idioma']) && !empty($item['nivel'])) {
                $idiomasFormatados[] = [
                    'idioma' => trim($item['idioma']),
                    'nivel' => trim($item['nivel']),
                ];
            }
        }
        $dados['idiomas'] = $idiomasFormatados;

        DB::transaction(function () use ($candidato, $dados, $request) {
            $candidato->update($dados);

            // Sincroniza Experiências
            $candidato->experiencias()->delete();
            if ($request->has('experiencias')) {
                foreach ($request->input('experiencias') as $exp) {
                    if (!empty($exp['empresa']) && !empty($exp['cargo'])) {
                        ExperienciaProfissional::create([
                            'candidato_id' => $candidato->id,
                            'empresa' => $exp['empresa'],
                            'cargo' => $exp['cargo'],
                            'data_inicio' => $exp['data_inicio'] ?: null,
                            'data_fim' => !empty($exp['atual']) ? null : ($exp['data_fim'] ?: null),
                            'atual' => !empty($exp['atual']),
                            'descricao' => $exp['descricao'] ?? null,
                        ]);
                    }
                }
            }

            // Sincroniza Formações
            $candidato->formacoes()->delete();
            if ($request->has('formacoes')) {
                foreach ($request->input('formacoes') as $form) {
                    if (!empty($form['instituicao']) && !empty($form['curso'])) {
                        FormacaoAcademica::create([
                            'candidato_id' => $candidato->id,
                            'instituicao' => $form['instituicao'],
                            'curso' => $form['curso'],
                            'nivel' => $form['nivel'] ?? null,
                            'data_inicio' => $form['data_inicio'] ?: null,
                            'data_fim' => $form['data_fim'] ?: null,
                            'concluido' => !empty($form['concluido']),
                        ]);
                    }
                }
            }

            // Sincroniza Cursos
            $candidato->cursos()->delete();
            if ($request->has('cursos')) {
                foreach ($request->input('cursos') as $c) {
                    if (!empty($c['nome_curso'])) {
                        CursoComplementar::create([
                            'candidato_id' => $candidato->id,
                            'nome_curso' => $c['nome_curso'],
                            'instituicao' => $c['instituicao'] ?? null,
                            'carga_horaria' => $c['carga_horaria'] ?? null,
                            'ano_conclusao' => $c['ano_conclusao'] ?? null,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('curriculo.edit')->with('success', 'Currículo atualizado com sucesso!');
    }

    /**
     * Endpoint API para busca assíncrona de CEP no formulário do currículo
     */
    public function buscaCep($cep)
    {
        $dados = $this->geoService->consultarCep($cep);

        if (!$dados) {
            return response()->json(['error' => 'CEP não localizado.'], 404);
        }

        return response()->json($dados);
    }
}
