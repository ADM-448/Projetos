<?php

namespace App\Http\Controllers;

use App\Http\Requests\VagaRequest;
use App\Models\Vaga;
use App\Models\PerguntaVaga;
use App\Models\Candidatura;
use App\Models\RespostaCandidato;
use App\Models\Chat;
use App\Models\Notificacao;
use App\Models\Candidato;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VagaController extends Controller
{
    public function __construct(protected GeoService $geoService)
    {
    }

    public function index(Request $request)
    {
        $busca = $request->query('busca');
        $localizacao = $request->query('localizacao');
        $ramo = $request->query('ramo');
        $modalidade = $request->query('modalidade');
        $tipoContrato = $request->query('tipo_contrato');
        $experiencia = $request->query('experiencia');
        $remoteOnly = $request->boolean('remote_only');
        $tab = $request->query('tab', 'jobs'); // 'jobs', 'saved', 'applied'
        $ordem = $request->query('ordem', 'recentes'); // 'recentes' ou 'proximos'
        $raioKm = $request->query('raio_km'); // 5, 10, 25, 50

        $salvas = session()->get('vagas_salvas', []);

        // Mapeamento de candidaturas prévias do candidato logado (Item 303)
        $candidaturasUsuario = [];
        $candidatoCoords = null;
        $candidatoSemEndereco = false;

        if (Auth::check() && Auth::user()->isCandidato() && Auth::user()->candidato) {
            $candidato = Auth::user()->candidato;
            $candidaturasUsuario = Candidatura::where('candidato_id', $candidato->id)
                ->pluck('data_candidatura', 'vaga_id')
                ->toArray();

            // Assegura que o candidato tenha coordenadas para o radar de proximidade
            if (!$candidato->latitude || !$candidato->longitude) {
                if (!empty($candidato->cep) || !empty($candidato->cidade)) {
                    $c = $this->geoService->getCoordenadasAproximadas($candidato->cep, $candidato->cidade);
                    if ($c) {
                        $candidato->update(['latitude' => $c['lat'], 'longitude' => $c['lng']]);
                    }
                }
            }

            if ($candidato->latitude && $candidato->longitude) {
                $candidatoCoords = ['lat' => (float) $candidato->latitude, 'lng' => (float) $candidato->longitude];
            } else {
                $candidatoSemEndereco = true;
            }
        }

        $query = Vaga::with('empresa')
            ->buscar($busca)
            ->porLocalizacao($localizacao)
            ->porRamo($ramo)
            ->porTipoContrato($tipoContrato)
            ->porExperiencia($experiencia);

        // Isolamento de Vagas por Perfil de Acesso:
        if (Auth::check() && Auth::user()->isEmpresa()) {
            // A empresa só vê as vagas DELA (com contagem de inscritos)
            $empresaIds = Auth::user()->empresas()->pluck('empresas.id');
            $query->whereIn('empresa_id', $empresaIds)->withCount('candidaturas');
        } elseif (Auth::check() && Auth::user()->isPrefeitura()) {
            // A prefeitura vê as vagas ativas da CIDADE DELA
            $query->ativas();
            $cidadePrefeitura = Auth::user()->getCidadePrefeitura();
            if ($cidadePrefeitura) {
                $query->where(function ($q) use ($cidadePrefeitura) {
                    $q->where('localizacao', 'LIKE', "%{$cidadePrefeitura}%")
                      ->orWhereHas('empresa', fn($e) => $e->where('cidade', 'LIKE', "%{$cidadePrefeitura}%"));
                });
            }
        } else {
            // Candidato ou Visitante (Guest) vê TODAS as vagas ativas
            $query->ativas();
        }

        if ($remoteOnly) {
            $query->porModalidade('Remoto');
        } elseif ($modalidade) {
            $query->porModalidade($modalidade);
        }

        // Abas: Jobs, Saved, Applied
        if ($tab === 'saved') {
            $query->whereIn('id', !empty($salvas) ? $salvas : [0]);
        } elseif ($tab === 'applied') {
            $appliedIds = array_keys($candidaturasUsuario);
            $query->whereIn('id', !empty($appliedIds) ? $appliedIds : [0]);
        }

        // Ordenação por Proximidade em KM (Vagas Próximas de Mim)
        if ($ordem === 'proximos' && $candidatoCoords) {
            $todasVagas = $query->get();
            foreach ($todasVagas as $v) {
                $vLat = $v->latitude;
                $vLng = $v->longitude;
                if (!$vLat || !$vLng) {
                    $approx = $this->geoService->getCoordenadasAproximadas($v->cep, $v->localizacao ?: ($v->empresa->cidade ?? null));
                    $vLat = $approx['lat'];
                    $vLng = $approx['lng'];
                }
                $v->distancia_km = $this->geoService->calcularDistancia(
                    $candidatoCoords['lat'],
                    $candidatoCoords['lng'],
                    $vLat,
                    $vLng
                );
            }

            if (!empty($raioKm)) {
                $todasVagas = $todasVagas->filter(fn($v) => $v->distancia_km !== null && $v->distancia_km <= (float)$raioKm);
            }

            $todasVagas = $todasVagas->sortBy(fn($v) => $v->distancia_km ?? 999999)->values();

            $page = (int) $request->query('page', 1);
            $perPage = 10;
            $vagas = new \Illuminate\Pagination\LengthAwarePaginator(
                $todasVagas->forPage($page, $perPage)->values(),
                $todasVagas->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $vagas = $query->latest()->paginate(10)->withQueryString();

            if ($candidatoCoords) {
                foreach ($vagas as $v) {
                    $vLat = $v->latitude;
                    $vLng = $v->longitude;
                    if (!$vLat || !$vLng) {
                        $approx = $this->geoService->getCoordenadasAproximadas($v->cep, $v->localizacao ?: ($v->empresa->cidade ?? null));
                        $vLat = $approx['lat'];
                        $vLng = $approx['lng'];
                    }
                    $v->distancia_km = $this->geoService->calcularDistancia(
                        $candidatoCoords['lat'],
                        $candidatoCoords['lng'],
                        $vLat,
                        $vLng
                    );
                }
            }
        }

        // Locais únicos disponíveis para filtro
        $queryLocais = Vaga::ativas()->whereNotNull('localizacao')->where('localizacao', '!=', '');
        if (Auth::check() && Auth::user()->isEmpresa()) {
            $queryLocais->whereIn('empresa_id', Auth::user()->empresas()->pluck('empresas.id'));
        } elseif (Auth::check() && Auth::user()->isPrefeitura()) {
            $cidade = Auth::user()->getCidadePrefeitura();
            if ($cidade) {
                $queryLocais->where(function ($q) use ($cidade) {
                    $q->where('localizacao', 'LIKE', "%{$cidade}%")
                      ->orWhereHas('empresa', fn($e) => $e->where('cidade', 'LIKE', "%{$cidade}%"));
                });
            }
        }
        $locaisDisponiveis = $queryLocais->distinct()->pluck('localizacao');

        // Ramos pré-definidos (com "Outros" - Item 342)
        $ramosDeAtividade = [
            'Tecnologia, TI, Informática',
            'Administração e Finanças',
            'Agricultura e Agronegócio',
            'Educação e Ensino',
            'Engenharia (Civil, Elétrica, Mecânica, etc.)',
            'Indústria e Manufatura',
            'Logística e Transportes',
            'Marketing e Comunicação',
            'Recursos Humanos e Gestão de Pessoas',
            'Saúde e Medicina',
            'Serviços Gerais e Manutenção',
            'Varejo e Comércio',
            'Outros',
        ];

        $tiposContratoDisponiveis = ['CLT', 'PJ', 'Estágio', 'Temporário', 'Cooperado'];
        $modalidadesDisponiveis = ['Presencial', 'Remoto', 'Híbrido'];
        $experienciasDisponiveis = ['Estágio', 'Júnior', 'Pleno', 'Sênior', 'Especialista'];

        // Recomendações e progresso do perfil do candidato (estilo Peerlist)
        $profileWidget = [
            'score' => 0,
            'recommendations' => [],
        ];

        if (Auth::check() && Auth::user()->isCandidato() && Auth::user()->candidato) {
            $candidato = Auth::user()->candidato;
            $candidato->loadMissing(['experiencias', 'formacoes']);
            $score = 0;
            $recs = [];

            if (!empty($candidato->resumo_profissional)) {
                $score += 20;
            } else {
                $recs[] = [
                    'title' => 'Adicione um resumo profissional',
                    'desc' => 'Destaque seus objetivos e competências para os recrutadores.',
                ];
            }

            if ($candidato->experiencias && $candidato->experiencias->count() > 0) {
                $score += 30;
            } else {
                $recs[] = [
                    'title' => 'Cadastre histórico profissional',
                    'desc' => 'Inclua empresas anteriores, cargos e realizações.',
                ];
            }

            if ($candidato->formacoes && $candidato->formacoes->count() > 0) {
                $score += 25;
            } else {
                $recs[] = [
                    'title' => 'Complete sua formação acadêmica',
                    'desc' => 'Graduações, cursos técnicos ou certificações.',
                ];
            }

            if (!empty($candidato->cep) && !empty($candidato->cidade)) {
                $score += 15;
            } else {
                $recs[] = [
                    'title' => 'Atualize sua localização e CEP',
                    'desc' => 'Calculamos vagas próximas ao seu endereço.',
                ];
            }

            if (!empty($candidato->telefone)) {
                $score += 10;
            } else {
                $recs[] = [
                    'title' => 'Cadastre seu WhatsApp / Telefone',
                    'desc' => 'Facilite o contato das empresas com você.',
                ];
            }

            $profileWidget = [
                'score' => min(100, $score),
                'recommendations' => $recs,
            ];
        }

        return view('vagas.index', compact(
            'vagas',
            'locaisDisponiveis',
            'ramosDeAtividade',
            'tiposContratoDisponiveis',
            'modalidadesDisponiveis',
            'experienciasDisponiveis',
            'candidaturasUsuario',
            'salvas',
            'busca',
            'localizacao',
            'ramo',
            'modalidade',
            'tipoContrato',
            'experiencia',
            'remoteOnly',
            'tab',
            'ordem',
            'raioKm',
            'candidatoCoords',
            'candidatoSemEndereco',
            'profileWidget'
        ));
    }

    public function toggleSalvar(Request $request, int $id)
    {
        $vaga = Vaga::findOrFail($id);
        $salvas = session()->get('vagas_salvas', []);

        if (in_array($id, $salvas)) {
            $salvas = array_values(array_diff($salvas, [$id]));
            $salvo = false;
            $mensagem = 'Vaga removida das salvas.';
        } else {
            $salvas[] = $id;
            $salvo = true;
            $mensagem = 'Vaga salva com sucesso!';
        }

        session()->put('vagas_salvas', $salvas);

        if ($request->wantsJson()) {
            return response()->json([
                'saved' => $salvo,
                'message' => $mensagem,
                'total_salvas' => count($salvas),
            ]);
        }

        return back()->with('success', $mensagem);
    }


    public function show($id)
    {
        $vaga = Vaga::with(['empresa', 'perguntas'])->findOrFail($id);

        $jaCandidatado = false;
        $candidatura = null;
        $chat = null;
        $perfilCompleto = true;

        if (Auth::check() && Auth::user()->isCandidato()) {
            $candidato = Auth::user()->candidato;

            if ($candidato) {
                $perfilCompleto = $candidato->isPerfilCompleto();

                $candidatura = Candidatura::where('vaga_id', $vaga->id)
                    ->where('candidato_id', $candidato->id)
                    ->first();

                if ($candidatura) {
                    $jaCandidatado = true;
                    $chat = Chat::where('candidatura_id', $candidatura->id)->first();
                }
            } else {
                $perfilCompleto = false;
            }
        }

        return view('vagas.detalhe', compact('vaga', 'jaCandidatado', 'candidatura', 'chat', 'perfilCompleto'));
    }

    public function aplicar(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || !$user->isCandidato()) {
            return redirect()->route('login')->with('error', 'Apenas candidatos podem se inscrever para vagas.');
        }

        $candidato = $user->candidato;
        if (!$candidato || !$candidato->isPerfilCompleto()) {
            return back()->with('error', 'Você precisa completar os dados mínimos do seu currículo antes de se candidatar.');
        }

        $vaga = Vaga::with('perguntas')->findOrFail($id);

        // Verifica se já está candidatado
        $existente = Candidatura::where('vaga_id', $vaga->id)
            ->where('candidato_id', $candidato->id)
            ->first();

        if ($existente) {
            return back()->with('info', 'Você já está candidatado a esta vaga.');
        }

        // 1. Validação de Perguntas Eliminatórias (Item 347)
        $respostasInput = $request->input('respostas', []);

        foreach ($vaga->perguntas as $pergunta) {
            $resposta = $respostasInput[$pergunta->id] ?? null;

            if ($pergunta->eliminatoria && strtolower(trim((string) $resposta)) === 'não') {
                return back()->with('alerta_eliminatoria', 'Atenção: você respondeu ‘Não’ a um requisito obrigatório para esta vaga. Por esse motivo, não será possível prosseguir com a sua candidatura.')->withInput();
            }
        }

        // 2. Registro da Candidatura e Chat
        DB::transaction(function () use ($vaga, $candidato, $user, $respostasInput) {
            $candidatura = Candidatura::create([
                'vaga_id' => $vaga->id,
                'candidato_id' => $candidato->id,
                'status' => 'pendente',
                'data_candidatura' => now(),
            ]);

            // Salva as respostas
            foreach ($vaga->perguntas as $pergunta) {
                if (isset($respostasInput[$pergunta->id])) {
                    RespostaCandidato::create([
                        'candidatura_id' => $candidatura->id,
                        'pergunta_id' => $pergunta->id,
                        'resposta' => $respostasInput[$pergunta->id],
                    ]);
                }
            }

            // Cria o canal de Chat para a candidatura
            Chat::create([
                'candidatura_id' => $candidatura->id,
                'vaga_id' => $vaga->id,
                'candidato_user_id' => $user->id,
                'recrutador_id' => $vaga->recrutador_id,
            ]);

            // Notifica o Recrutador
            Notificacao::create([
                'user_id' => $vaga->recrutador_id,
                'titulo' => 'Nova Candidatura',
                'mensagem' => "{$candidato->nome_completo} se candidatou à vaga '{$vaga->titulo}'.",
                'link' => route('vagas.candidatos', $vaga->id),
                'lida' => false,
            ]);
        });

        return redirect()->route('vagas.show', $vaga->id)
            ->with('success', 'Candidatura realizada com sucesso! Boa sorte!');
    }

    public function cancelarCandidatura($id)
    {
        $user = Auth::user();
        if (!$user || !$user->isCandidato() || !$user->candidato) {
            abort(403);
        }

        $candidatura = Candidatura::where('id', $id)
            ->where('candidato_id', $user->candidato->id)
            ->firstOrFail();

        $vagaId = $candidatura->vaga_id;
        $candidatura->delete();

        return redirect()->route('vagas.show', $vagaId)->with('success', 'Candidatura cancelada com sucesso.');
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->isEmpresa() && !$user->isAdmin()) {
            abort(403, 'Acesso restrito a recrutadores e empresas.');
        }

        $empresa = $user->empresas()->first();
        if (!$empresa && !$user->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Nenhuma empresa vinculada ao seu usuário.');
        }

        $tiposContrato = ['CLT', 'PJ', 'Estágio', 'Jovem Aprendiz', '1° Emprego', 'Temporário', 'Autônomo'];
        $modalidades = ['Presencial', 'Híbrido', 'Remoto'];
        $ramos = [
            'Administração e Finanças', 'Agricultura e Agronegócio', 'Educação e Ensino',
            'Engenharia (Civil, Elétrica, Mecânica, etc.)', 'Indústria e Manufatura',
            'Logística e Transportes', 'Marketing e Comunicação', 'Recursos Humanos e Gestão de Pessoas',
            'Saúde e Medicina', 'Serviços Gerais e Manutenção', 'Tecnologia, TI, Informática',
            'Varejo e Comércio', 'Outros'
        ];

        return view('vagas.create', compact('tiposContrato', 'modalidades', 'ramos', 'empresa'));
    }

    public function store(VagaRequest $request)
    {
        $user = Auth::user();
        $empresa = $user->empresas()->first();

        $dados = $request->validated();
        $dados['empresa_id'] = $empresa->id ?? 1;
        $dados['recrutador_id'] = $user->id;
        $dados['portal_id'] = config('portal.current_id', 1);
        $dados['status'] = 'ativa';

        // Geolocalização por CEP se fornecido
        if (!empty($dados['cep'])) {
            $geo = $this->geoService->consultarCep($dados['cep']);
            if ($geo) {
                $dados['latitude'] = $geo['latitude'];
                $dados['longitude'] = $geo['longitude'];
            }
        }

        $vaga = DB::transaction(function () use ($dados, $request) {
            $vaga = Vaga::create($dados);

            if ($request->has('perguntas')) {
                foreach ($request->input('perguntas') as $p) {
                    if (!empty($p['texto'])) {
                        PerguntaVaga::create([
                            'vaga_id' => $vaga->id,
                            'texto_pergunta' => $p['texto'],
                            'eliminatoria' => !empty($p['eliminatoria']),
                        ]);
                    }
                }
            }

            return $vaga;
        });

        return redirect()->route('dashboard')->with('success', 'Vaga publicada com sucesso!');
    }

    public function edit($id)
    {
        $vaga = Vaga::with('perguntas')->findOrFail($id);
        $this->authorizeVagaAccess($vaga);

        $tiposContrato = ['CLT', 'PJ', 'Estágio', 'Jovem Aprendiz', '1° Emprego', 'Temporário', 'Autônomo'];
        $modalidades = ['Presencial', 'Híbrido', 'Remoto'];
        $ramos = [
            'Administração e Finanças', 'Agricultura e Agronegócio', 'Educação e Ensino',
            'Engenharia (Civil, Elétrica, Mecânica, etc.)', 'Indústria e Manufatura',
            'Logística e Transportes', 'Marketing e Comunicação', 'Recursos Humanos e Gestão de Pessoas',
            'Saúde e Medicina', 'Serviços Gerais e Manutenção', 'Tecnologia, TI, Informática',
            'Varejo e Comércio', 'Outros'
        ];

        return view('vagas.edit', compact('vaga', 'tiposContrato', 'modalidades', 'ramos'));
    }

    public function update(VagaRequest $request, $id)
    {
        $vaga = Vaga::findOrFail($id);
        $this->authorizeVagaAccess($vaga);

        $dados = $request->validated();

        if (!empty($dados['cep']) && $dados['cep'] !== $vaga->cep) {
            $geo = $this->geoService->consultarCep($dados['cep']);
            if ($geo) {
                $dados['latitude'] = $geo['latitude'];
                $dados['longitude'] = $geo['longitude'];
            }
        }

        DB::transaction(function () use ($vaga, $dados, $request) {
            $vaga->update($dados);

            // Sincroniza perguntas
            if ($request->has('perguntas')) {
                $vaga->perguntas()->delete();
                foreach ($request->input('perguntas') as $p) {
                    if (!empty($p['texto'])) {
                        PerguntaVaga::create([
                            'vaga_id' => $vaga->id,
                            'texto_pergunta' => $p['texto'],
                            'eliminatoria' => !empty($p['eliminatoria']),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('dashboard')->with('success', 'Vaga atualizada com sucesso!');
    }

    public function candidatos($id, Request $request)
    {
        $vaga = Vaga::with('empresa')->findOrFail($id);
        $this->authorizeVagaAccess($vaga);

        // Marca como lidas as notificações do recrutador referentes a esta vaga
        if (Auth::check()) {
            Notificacao::where('user_id', Auth::id())
                ->where('link', route('vagas.candidatos', $vaga->id))
                ->where('lida', false)
                ->update(['lida' => true]);
        }

        $ordem = $request->query('ordem', 'recentes');

        $query = Candidatura::with(['candidato.user', 'chat', 'respostas.pergunta'])
            ->where('vaga_id', $vaga->id);

        if ($ordem === 'antigas') {
            $query->oldest('data_candidatura');
        } elseif ($ordem === 'recentes') {
            $query->latest('data_candidatura');
        }

        $candidaturas = $query->get();

        // Calcula distância de cada candidato em relação à vaga
        foreach ($candidaturas as $c) {
            if ($vaga->latitude && $vaga->longitude && $c->candidato && $c->candidato->latitude && $c->candidato->longitude) {
                $c->distancia_km = $this->geoService->calcularDistancia(
                    $vaga->latitude,
                    $vaga->longitude,
                    $c->candidato->latitude,
                    $c->candidato->longitude
                );
            } else {
                $c->distancia_km = null;
            }
        }

        // Se ordenação for por proximidade em KM
        if ($ordem === 'proximos') {
            $candidaturas = $candidaturas->sortBy(function ($item) {
                return $item->distancia_km !== null ? $item->distancia_km : 999999;
            })->values();
        }

        return view('vagas.candidatos', compact('vaga', 'candidaturas', 'ordem'));
    }

    public function perfilCandidato($userId, Request $request)
    {
        $candidato = Candidato::where('user_id', $userId)
            ->with(['user', 'experiencias', 'formacoes', 'cursos'])
            ->firstOrFail();

        $vagaId = $request->query('vaga_id');
        $vaga = null;
        $candidatura = null;
        $distanciaKm = null;

        if (Auth::check() && Auth::user()->isEmpresa()) {
            $empresaIds = Auth::user()->empresas()->pluck('empresas.id');
            $vagasIdsDaEmpresa = Vaga::whereIn('empresa_id', $empresaIds)->pluck('id');

            if ($vagaId) {
                $vaga = Vaga::with('empresa')->findOrFail($vagaId);
                $this->authorizeVagaAccess($vaga);

                $candidatura = Candidatura::where('vaga_id', $vaga->id)
                    ->where('candidato_id', $candidato->id)
                    ->with(['respostas.pergunta', 'chat'])
                    ->first();
            } else {
                $candidatura = Candidatura::whereIn('vaga_id', $vagasIdsDaEmpresa)
                    ->where('candidato_id', $candidato->id)
                    ->with(['respostas.pergunta', 'chat', 'vaga.empresa'])
                    ->latest('data_candidatura')
                    ->first();

                if (!$candidatura) {
                    abort(403, 'Você só pode visualizar perfis de candidatos inscritos em vagas da sua empresa.');
                }
                $vaga = $candidatura->vaga;
            }
        } elseif ($vagaId) {
            $vaga = Vaga::with('empresa')->findOrFail($vagaId);
            $candidatura = Candidatura::where('vaga_id', $vaga->id)
                ->where('candidato_id', $candidato->id)
                ->with(['respostas.pergunta', 'chat'])
                ->first();
        }

        if ($vaga && $vaga->latitude && $vaga->longitude && $candidato->latitude && $candidato->longitude) {
            $distanciaKm = $this->geoService->calcularDistancia(
                $vaga->latitude,
                $vaga->longitude,
                $candidato->latitude,
                $candidato->longitude
            );
        }

        // Prepara link formatado do WhatsApp
        $whatsappLink = null;
        $telefoneParaZap = $candidato->celular ?: $candidato->telefone;
        if (!empty($telefoneParaZap)) {
            $limpo = preg_replace('/\D/', '', $telefoneParaZap);
            if (strlen($limpo) === 10 || strlen($limpo) === 11) {
                $limpo = '55' . $limpo;
            }
            if (strlen($limpo) >= 12) {
                $whatsappLink = 'https://wa.me/' . $limpo;
            }
        }

        return view('vagas.candidato_perfil', compact('candidato', 'vaga', 'candidatura', 'distanciaKm', 'whatsappLink'));
    }

    public function atualizarStatusCandidatura(Request $request, $id)
    {
        $candidatura = Candidatura::with(['vaga', 'candidato.user'])->findOrFail($id);
        $this->authorizeVagaAccess($candidatura->vaga);

        $request->validate([
            'status' => ['required', 'string', 'in:pendente,em_processo,aprovado,reprovado'],
        ]);

        $novoStatus = $request->input('status');
        $candidatura->update(['status' => $novoStatus]);

        $statusLabels = [
            'pendente' => 'Pendente',
            'em_processo' => 'Em Processo',
            'aprovado' => 'Aprovado',
            'reprovado' => 'Não Selecionado',
        ];
        $label = $statusLabels[$novoStatus] ?? ucfirst($novoStatus);

        // Notifica o candidato
        if ($candidatura->candidato && $candidatura->candidato->user_id) {
            Notificacao::create([
                'user_id' => $candidatura->candidato->user_id,
                'titulo' => 'Status da Candidatura Atualizado',
                'mensagem' => "O status da sua candidatura na vaga '{$candidatura->vaga->titulo}' mudou para: {$label}.",
                'link' => route('minhas.candidaturas'),
                'lida' => false,
            ]);
        }

        return back()->with('success', "Status da candidatura alterado para '{$label}' com sucesso!");
    }

    protected function authorizeVagaAccess(Vaga $vaga)
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEmpresa()) {
            $empresaIds = $user->empresas()->pluck('empresas.id')->toArray();
            if (in_array($vaga->empresa_id, $empresaIds)) {
                return true;
            }
        }

        abort(403, 'Acesso não autorizado para esta vaga.');
    }
}
