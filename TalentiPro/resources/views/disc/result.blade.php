@extends('layouts.app')

@section('title', 'Resultado DISC')

@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
    <style>
        .disc-result-card {
            background: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f2f5;
            box-sizing: border-box;
            width: 100%;
        }

        .disc-result-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: center;
            width: 100%;
        }

        .disc-chart-box {
            box-sizing: border-box;
            width: 100%;
            overflow: hidden;
        }

        @media (max-width: 900px) {
            .disc-result-card {
                padding: 16px !important;
            }

            .disc-result-grid {
                grid-template-columns: 1fr !important;
                gap: 20px !important;
            }

            .disc-chart-box {
                padding: 8px !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .disc-chart-box>div {
                max-width: 100% !important;
                width: 100% !important;
            }

            .disc-actions-wrap {
                justify-content: center !important;
                flex-direction: column !important;
            }

            .disc-actions-wrap button,
            .disc-actions-wrap a,
            .disc-actions-wrap form {
                width: 100%;
            }

            .disc-actions-wrap button,
            .disc-actions-wrap a {
                justify-content: center;
                text-align: center;
            }
        }
    </style>

    <div class="header-actions"
        style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="min-width: 0; width: 100%;">
            <h1 class="page-title"
                style="margin-bottom: 8px; font-size: clamp(1.2rem, 4vw, 1.5rem); word-break: break-word;"><i
                    class="fa-solid fa-chart-radar" style="color: #6a4c7c;"></i> Relatório Executivo DISC:
                {{ $assessment->user->name ?? $assessment->respondent_name }}
            </h1>
            <p style="color: #64748b; font-size: 0.95rem; word-break: break-word;">Análise Comportamental Profunda (Essência
                vs. Adaptação)</p>
        </div>

        @if(isset($historico) && $historico->count() > 1)
            <div>
                <label style="font-size: 0.85rem; font-weight: bold; color: #4a5568;">Histórico de Avaliações:</label>
                <select onchange="window.location.href=this.value"
                    style="padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e0; margin-left: 5px; outline:none;">
                    @foreach($historico as $hist)
                        <option value="{{ route('disc.result', $hist->id) }}" {{ $assessment->id == $hist->id ? 'selected' : '' }}>
                            {{ $hist->created_at->format('d/m/Y H:i') }} -
                            {{ $hist->origin === 'external_form' ? 'Candidato (Externo)' : 'Interno' }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @php
        $meta = $assessment->metadata ?? [];
        $natural = $meta['natural'] ?? [];
        $adaptado = $meta['adaptado'] ?? [];
        $highStress = $meta['high_stress_adaptation'] ?? false;
        $compressed = $meta['compressed_profile'] ?? false;
        $eixos = $meta['eixos'] ?? ['foco_tarefas' => 50, 'ritmo_ativo' => 50];
        $activeSubtraits = $meta['subtraits_active'] ?? [];

        $pKey = substr($assessment->primary_profile ?? 'D', 0, 1);
        $insights = config('disc_insights.profiles.' . $pKey);
        $adjectives = config('disc_insights.intensity_adjectives');
        $subtraitsDict = config('disc_insights.subtraits');

        function getAdjective($score, $dict)
        {
            if (!$dict)
                return '';
            $closest = 10;
            foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 99] as $k) {
                if ($score >= $k)
                    $closest = $k;
            }
            return $dict[$closest] ?? '';
        }
    @endphp

    <!-- DIAGNÓSTICO E ALERTAS DE RH -->
    @if($highStress || $compressed)
        <div
            style="background: #fff5f5; border-left: 5px solid #e53e3e; padding: 20px; border-radius: 12px; margin-bottom: 25px; display: flex; gap: 15px; align-items: flex-start; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <i class="fa-solid fa-triangle-exclamation" style="color: #e53e3e; font-size: 1.5rem; margin-top: 3px;"></i>
            <div>
                <h3 style="color: #c53030; font-size: 1.1rem; font-weight: 800; margin-bottom: 8px;">Alerta de Diagnóstico (RH)
                </h3>
                @if($highStress)
                    <p style="color: #9b2c2c; font-size: 0.95rem; margin-bottom: 5px;"><strong>Alto Esforço de Adaptação:</strong> O
                        colaborador está a fazer um esforço emocional tremendo (Gap >= 20) para adaptar o seu comportamento natural
                        às exigências que perceciona do cargo atual. Risco de burnout ou desmotivação a médio prazo.</p>
                @endif
                @if($compressed)
                    <p style="color: #9b2c2c; font-size: 0.95rem; margin-bottom: 0;"><strong>Perfil Comprimido (Síndrome de
                            Indecisão):</strong> Todos os fatores estão concentrados perto da média. Isto indica pressão, transição
                        de carreira, ou indefinição clara sobre qual é o papel esperado desta pessoa.</p>
                @endif
            </div>
        </div>
    @endif

    <div class="disc-result-card">
        <div class="disc-result-grid">

            <!-- Lado Esquerdo: Gráficos -->
            <div style="display: flex; flex-direction: column; gap: 20px; min-width: 0;">
                <!-- Radar Gráfico -->
                <div class="disc-chart-box"
                    style="display: flex; justify-content: center; align-items: center; background: #fafbfc; border-radius: 16px; padding: 20px; border: 1px dashed #e2e8f0; width: 100%; box-sizing: border-box;">
                    <div style="width: 100%; max-width: 450px;">
                        <canvas id="discChart"></canvas>
                    </div>
                </div>

                <!-- Matriz Dimensional -->
                <div
                    style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                    <h4
                        style="font-size: 1.05rem; font-weight: 700; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                        <i class="fa-solid fa-compass" style="color: #a0aec0; margin-right: 8px;"></i> Eixos Dimensionais
                    </h4>

                    <div style="margin-bottom: 15px;">
                        <div
                            style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 5px; font-size: 0.85rem; font-weight: bold; color: #4a5568; margin-bottom: 5px;">
                            <span style="min-width: 120px; flex: 1;">Pessoas / Relacionamentos</span>
                            <span style="min-width: 120px; flex: 1; text-align: right;">Tarefas / Resultados</span>
                        </div>
                        <div
                            style="width: 100%; background: #edf2f7; height: 10px; border-radius: 5px; position: relative;">
                            <div
                                style="position: absolute; left: 50%; top: -5px; bottom: -5px; width: 2px; background: #cbd5e0;">
                            </div>
                            <div
                                style="position: absolute; left: {{ $eixos['foco_tarefas'] }}%; top: -3px; width: 16px; height: 16px; background: #6a4c7c; border-radius: 50%; transform: translateX(-50%); box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div
                            style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 5px; font-size: 0.85rem; font-weight: bold; color: #4a5568; margin-bottom: 5px;">
                            <span style="min-width: 120px; flex: 1;">Introversão / Passivo</span>
                            <span style="min-width: 120px; flex: 1; text-align: right;">Extroversão / Ativo</span>
                        </div>
                        <div
                            style="width: 100%; background: #edf2f7; height: 10px; border-radius: 5px; position: relative;">
                            <div
                                style="position: absolute; left: 50%; top: -5px; bottom: -5px; width: 2px; background: #cbd5e0;">
                            </div>
                            <div
                                style="position: absolute; left: {{ $eixos['ritmo_ativo'] }}%; top: -3px; width: 16px; height: 16px; background: #6a4c7c; border-radius: 50%; transform: translateX(-50%); box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lado Direito: Resultados Textuais e Insights -->
            <div style="display: flex; flex-direction: column; gap: 20px; min-width: 0;">

                <div
                    style="background: linear-gradient(135deg, rgba(106, 76, 124, 0.1) 0%, rgba(106, 76, 124, 0.05) 100%); border-left: 5px solid #6a4c7c; padding: 20px 25px; border-radius: 0 12px 12px 0;">
                    <h2
                        style="font-size: 1.6rem; font-weight: 800; color: #2d3748; margin-bottom: 5px; word-break: break-word;">
                        Essência Primária: <span
                            style="color: #6a4c7c;">{{ mb_strtoupper($assessment->primary_profile_br) }}</span>
                    </h2>
                    @if($assessment->secondary_profile_br)
                        <p style="font-size: 1rem; color: #4a5568; font-weight: 600; text-transform: uppercase;">
                            FATOR SECUNDÁRIO: {{ mb_strtoupper($assessment->secondary_profile_br) }}
                        </p>
                    @endif
                </div>

                <!-- Termómetro de Intensidade -->
                <div
                    style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                    <h4
                        style="font-size: 1.05rem; font-weight: 700; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                        <i class="fa-solid fa-temperature-half" style="color: #a0aec0; margin-right: 8px;"></i> Termómetro
                        de Intensidade (Essência)
                    </h4>

                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
                        @foreach(['DOMINANCE' => ['D', '#e53e3e', 'Dominância'], 'INFLUENCE' => ['I', '#d69e2e', 'Influência'], 'STEADINESS' => ['S', '#38a169', 'Estabilidade'], 'COMPLIANCE' => ['C', '#3182ce', 'Conformidade']] as $key => $c)
                            <li
                                style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border-radius: 8px; border-left: 4px solid {{$c[1]}}; gap: 5px;">
                                <span style="font-weight: 700; color: #2d3748; font-size: 0.9rem; min-width: 100px;">{{$c[2]}}
                                    ({{$c[0]}})</span>
                                <div style="text-align: right; min-width: 100px;">
                                    <span
                                        style="font-size: 0.9rem; font-weight: 800; color: {{$c[1]}}; text-transform: uppercase;">{{ getAdjective($natural[$key] ?? 0, $adjectives[$c[0]] ?? []) }}</span>
                                    <span
                                        style="font-size: 0.8rem; color: #a0aec0; margin-left: 5px;">[{{ $natural[$key] ?? 0 }}%]</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Subtraços -->
                @if(count($activeSubtraits) > 0)
                    <div
                        style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                        <h4
                            style="font-size: 1.05rem; font-weight: 700; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                            <i class="fa-solid fa-fingerprint" style="color: #a0aec0; margin-right: 8px;"></i> Sub-traços Ativos
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($activeSubtraits as $st)
                                @if(isset($subtraitsDict[$st]))
                                    <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #edf2f7;">
                                        <strong
                                            style="color: #4a5568; font-size: 0.95rem; display: block; margin-bottom: 4px;">{{ $subtraitsDict[$st]['name'] }}</strong>
                                        <span
                                            style="color: #718096; font-size: 0.85rem; line-height: 1.4; display: block;">{{ $subtraitsDict[$st]['description'] }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Perfil Natural vs Adaptado -->
                @if(isset($natural) && isset($adaptado))
                    <div
                        style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                        <h4
                            style="font-size: 1.05rem; font-weight: 700; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                            <i class="fa-solid fa-scale-balanced" style="color: #a0aec0; margin-right: 8px;"></i> Natural vs
                            Adaptado (O que a função lhe exige)
                        </h4>

                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach(['DOMINANCE' => ['D', '#e53e3e'], 'INFLUENCE' => ['I', '#d69e2e'], 'STEADINESS' => ['S', '#38a169'], 'COMPLIANCE' => ['C', '#3182ce']] as $fullKey => $cfg)
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span
                                        style="font-weight:900; font-size:0.9rem; width:15px; color:{{$cfg[1]}}">{{$cfg[0]}}</span>
                                    <div style="flex:1; display:flex; flex-direction:column; gap:3px;">
                                        <!-- Barra Natural (Colorida) -->
                                        <div style="background:#edf2f7; height:8px; border-radius:4px; overflow:hidden;">
                                            <div style="height:100%; width:{{ $natural[$fullKey] ?? 0 }}%; background:{{$cfg[1]}};">
                                            </div>
                                        </div>
                                        <!-- Barra Adaptado (Cinza) -->
                                        <div style="background:#edf2f7; height:8px; border-radius:4px; overflow:hidden;">
                                            <div style="height:100%; width:{{ $adaptado[$fullKey] ?? 0 }}%; background:#a0aec0;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div
                                style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-top: 5px; font-size: 0.8rem; font-weight: 600; color: #718096;">
                                <span style="display:flex; align-items:center; gap:4px; word-break: break-word;"><span
                                        style="width:10px; height:10px; border-radius:50%; background:#6a4c7c; flex-shrink: 0;"></span>
                                    Cor = Natural (Essência)</span>
                                <span style="display:flex; align-items:center; gap:4px; word-break: break-word;"><span
                                        style="width:10px; height:10px; border-radius:50%; background:#a0aec0; flex-shrink: 0;"></span>
                                    Cinza = Adaptado (Cargo)</span>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <!-- GUIA DO GESTOR -->
        @if($insights)
            <div
                style="margin-top: 30px; background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <h3
                    style="font-size: 1.2rem; font-weight: 800; color: #2d3748; margin-bottom: 20px; border-bottom: 2px solid #edf2f7; padding-bottom: 10px;">
                    <i class="fa-solid fa-book-open-reader" style="color: #6a4c7c; margin-right: 8px;"></i> Guia Prático de
                    Gestão (Perfil {{$pKey}})
                </h3>

                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div style="background: #f0fff4; border: 1px solid #c6f6d5; padding: 15px; border-radius: 10px;">
                        <h5 style="color: #276749; font-weight: 800; margin-bottom: 10px; font-size: 0.95rem;"><i
                                class="fa-solid fa-circle-check"></i> O que FAZER (Comunicação)</h5>
                        <ul style="padding-left: 20px; color: #2f855a; font-size: 0.9rem; margin: 0;">
                            @foreach($insights['do'] as $do)
                            <li>{{$do}}</li> @endforeach
                        </ul>
                    </div>
                    <div style="background: #fff5f5; border: 1px solid #fed7d7; padding: 15px; border-radius: 10px;">
                        <h5 style="color: #c53030; font-weight: 800; margin-bottom: 10px; font-size: 0.95rem;"><i
                                class="fa-solid fa-circle-xmark"></i> O que EVITAR (Desmotiva)</h5>
                        <ul style="padding-left: 20px; color: #9b2c2c; font-size: 0.9rem; margin: 0;">
                            @foreach($insights['dont'] as $dont)
                            <li>{{$dont}}</li> @endforeach
                        </ul>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div>
                        <strong style="color: #4a5568; font-size: 0.95rem; display: block; margin-bottom: 5px;">Pontos Fortes
                            Principais:</strong>
                        <ul style="padding-left: 20px; color: #718096; font-size: 0.9rem; margin: 0;">
                            @foreach($insights['strengths'] as $st)
                            <li>{{$st}}</li> @endforeach
                        </ul>
                    </div>
                    <div>
                        <strong style="color: #4a5568; font-size: 0.95rem; display: block; margin-bottom: 5px;">Ambiente Ideal
                            de Trabalho:</strong>
                        <ul style="padding-left: 20px; color: #718096; font-size: 0.9rem; margin: 0;">
                            @foreach($insights['environment'] as $env)
                            <li>{{$env}}</li> @endforeach
                        </ul>
                    </div>
                    <div>
                        <strong style="color: #4a5568; font-size: 0.95rem; display: block; margin-bottom: 5px;">Estilo de
                            Aprendizagem:</strong>
                        <p style="color: #718096; font-size: 0.9rem; margin: 0;">{{ $insights['learning'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="disc-actions-wrap"
            style="text-align: right; margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;">

            @php
                $user = auth()->user();
                $isMasterOrAdmin = in_array($user->perfil_acesso->value, ['master', 'admin']);

                // Verifica solicitação pendente existente para não-masters
                $solicitacaoPendente = null;
                if (!$isMasterOrAdmin) {
                    $solicitacaoPendente = \App\Models\Solicitacao::where('user_id', $user->id)
                        ->where('disc_assessment_id', $assessment->id)
                        ->where('tipo', 'reabertura_disc')
                        ->whereIn('status', ['pendente', 'aprovada'])
                        ->first();
                }
            @endphp

            @if($isMasterOrAdmin && $assessment->user_id == auth()->id())
                {{-- Master/Admin VENDO O SEU PRÓPRIO TESTE: refaz imediatamente --}}
                <form action="{{ route('disc.retake') }}" method="POST" style="margin: 0;"
                    onsubmit="event.preventDefault(); Swal.fire({title:'Atenção!', text:'Responder novamente irá apagar este resultado definitivamente. Deseja continuar?', icon:'warning', showCancelButton:true, confirmButtonColor:'#6a4c7c', cancelButtonColor:'#a0aec0', confirmButtonText:'Sim, refazer!', cancelButtonText:'Cancelar'}).then((res) => { if(res.isConfirmed) this.submit(); });">
                    @csrf
                    <button type="submit" class="btn-secondary"
                        style="padding: 12px 25px; border-radius: 8px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; background: transparent; border: 1px solid #6a4c7c; color: #6a4c7c; cursor: pointer;">
                        <i class="fa-solid fa-rotate-right"></i> Responder Novamente
                    </button>
                </form>
            @elseif(in_array($user->perfil_acesso->value ?? $user->perfil_acesso, ['master', 'admin', 'gestor']) && $assessment->user_id != auth()->id())
                {{-- Master/Gestor/Admin VENDO TESTE DE TERCEIRO: libera o teste pro colaborador refazer --}}
                <form action="{{ route('disc.liberar', $assessment->id) }}" method="POST" style="margin: 0;"
                    onsubmit="event.preventDefault(); Swal.fire({title:'Liberar Novo Teste', text:'Tem certeza? Isso irá apagar o teste atual e permitir que o colaborador faça de novo.', icon:'warning', showCancelButton:true, confirmButtonColor:'#e53e3e', cancelButtonColor:'#a0aec0', confirmButtonText:'Sim, liberar!', cancelButtonText:'Cancelar'}).then((res) => { if(res.isConfirmed) this.submit(); });">
                    @csrf
                    <button type="submit" class="btn-secondary"
                        style="padding: 12px 25px; border-radius: 8px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; background: transparent; border: 1px solid #e53e3e; color: #e53e3e; cursor: pointer;">
                        <i class="fa-solid fa-unlock"></i> Liberar Novo Teste
                    </button>
                </form>
            @elseif($solicitacaoPendente)
                {{-- Já tem solicitação em aberto --}}
                <span
                    style="padding: 12px 20px; border-radius: 8px; background: #fffbeb; border: 1px solid #f6e05e; color: #92400e; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-clock"></i>
                    @if($solicitacaoPendente->status === 'pendente')
                        Solicitação de refazer aguardando aprovação do RH
                    @else
                        Solicitação aprovada — entre em contato com o RH
                    @endif
                </span>
            @elseif($assessment->user_id == auth()->id())
                {{-- Colaborador/Gestor VENDO O SEU PRÓPRIO TESTE: abre modal de justificativa --}}
                <button type="button" onclick="document.getElementById('modal-disc-solicitar').style.display='flex'"
                    style="padding: 12px 25px; border-radius: 8px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; background: transparent; border: 1px solid #6a4c7c; color: #6a4c7c; cursor: pointer;">
                    <i class="fa-solid fa-paper-plane"></i> Solicitar Refazer
                </button>
            @endif

            @php
                $rotaVoltar = route('dashboard');
                $textoVoltar = 'Voltar ao Painel';

                if ($assessment->origin === 'external_form') {
                    $rotaVoltar = route('disc.equipe');
                    $textoVoltar = 'Voltar';
                } elseif (in_array(auth()->user()->perfil_acesso->value ?? '', ['master', 'admin', 'gestor'])) {
                    $rotaVoltar = route('disc.equipe');
                    $textoVoltar = 'Voltar aos Testes da Equipe';
                }
            @endphp
            <a href="{{ $rotaVoltar }}" class="btn-primary"
                style="padding: 12px 25px; border-radius: 8px; font-weight: bold; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrow-left"></i> {{ $textoVoltar }}
            </a>
        </div>

    </div>
    </div>
    </div>


    {{-- Modal: Solicitar Refazer DISC (apenas para não-masters) --}}
    @if(!in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
        <div id="modal-disc-solicitar"
            style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
            <div
                style="background:white; border-radius:16px; padding:40px; max-width:520px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
                <h3 style="font-size:1.3rem; font-weight:800; color:#2d3748; margin-bottom:8px;">
                    <i class="fa-solid fa-paper-plane" style="color:#6a4c7c;"></i> Solicitar Refazer o Teste DISC
                </h3>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:20px; line-height:1.6;">
                    Sua solicitação será enviada para o RH para aprovação. Justifique abaixo o motivo pelo qual deseja refazer o
                    teste.
                </p>

                @if(session('error'))
                    <div
                        style="background:#fff5f5; border:1px solid #feb2b2; color:#c53030; padding:12px; border-radius:8px; margin-bottom:16px; font-size:0.9rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('disc.solicitar-refazer') }}" method="POST">
                    @csrf
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; color:#4a5568; margin-bottom:8px; font-size:0.9rem;">
                            Justificativa <span style="color:#e53e3e;">*</span>
                        </label>
                        <textarea name="justificativa" rows="4"
                            placeholder="Ex: Errei ao preencher algumas respostas e gostaria de refazer o teste para ter um resultado mais preciso..."
                            style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.9rem; resize:vertical; font-family:inherit; box-sizing:border-box; outline:none;"
                            required minlength="20">{{ old('justificativa') }}</textarea>
                        <p style="font-size:0.78rem; color:#a0aec0; margin-top:4px;">Mínimo de 20 caracteres.</p>
                    </div>

                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" onclick="document.getElementById('modal-disc-solicitar').style.display='none'"
                            style="padding:10px 20px; border-radius:8px; border:1px solid #e2e8f0; background:white; color:#4a5568; font-weight:600; cursor:pointer;">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary"
                            style="padding:10px 24px; border-radius:8px; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-paper-plane"></i> Enviar Solicitação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @push('scripts')

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Tenta carregar o Chart.js do Vite (window.Chart) ou do CDN (Chart)
                const ChartClass = window.Chart || Chart;

                if (typeof ChartClass !== 'undefined') {
                    const ctx = document.getElementById('discChart').getContext('2d');

                    new ChartClass(ctx, {
                        type: 'radar',
                        data: {
                            labels: [
                                'Dominância',
                                'Influência',
                                'Estabilidade',
                                'Conformidade'
                            ],
                            datasets: [
                                {
                                    label: 'Natural (Essência)',
                                    data: [
                                            {{ $natural['DOMINANCE'] ?? 0 }},
                                            {{ $natural['INFLUENCE'] ?? 0 }},
                                            {{ $natural['STEADINESS'] ?? 0 }},
                                        {{ $natural['COMPLIANCE'] ?? 0 }}
                                    ],
                                    backgroundColor: 'rgba(106, 76, 124, 0.2)',
                                    borderColor: 'rgba(106, 76, 124, 1)',
                                    pointBackgroundColor: [
                                        '#e53e3e', // Red
                                        '#d69e2e', // Yellow
                                        '#38a169', // Green
                                        '#3182ce'  // Blue
                                    ],
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: 'rgba(106, 76, 124, 1)',
                                    borderWidth: 3,
                                    pointRadius: 6,
                                    pointHoverRadius: 8
                                },
                                {
                                    label: 'Adaptado (Cargo)',
                                    data: [
                                            {{ $adaptado['DOMINANCE'] ?? 0 }},
                                            {{ $adaptado['INFLUENCE'] ?? 0 }},
                                            {{ $adaptado['STEADINESS'] ?? 0 }},
                                        {{ $adaptado['COMPLIANCE'] ?? 0 }}
                                    ],
                                    backgroundColor: 'rgba(160, 174, 192, 0.15)',
                                    borderColor: 'rgba(160, 174, 192, 1)',
                                    pointBackgroundColor: '#a0aec0',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: 'rgba(160, 174, 192, 1)',
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            layout: {
                                padding: window.innerWidth < 900
                                    ? { left: 20, right: 20, top: 10, bottom: 10 }
                                    : { left: 30, right: 30, top: 20, bottom: 20 }
                            },
                            scales: {
                                r: {
                                    angleLines: {
                                        display: true,
                                        color: 'rgba(0, 0, 0, 0.15)'
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.15)'
                                    },
                                    pointLabels: {
                                        font: function (context) {
                                            var width = context.chart.width;
                                            return {
                                                size: width < 400 ? 10 : 13, // Menor no mobile, maior no desktop
                                                weight: 'bold',
                                                family: "'Inter', sans-serif"
                                            };
                                        },
                                        color: '#2d3748'
                                    },
                                    ticks: {
                                        display: false, // Esconde os numerozinhos (25, 50, 75...) para não encavalar
                                        min: 0,
                                        max: 100, // Maximum points possible is 100
                                        stepSize: 25,
                                        color: '#718096',
                                        backdropColor: 'transparent',
                                        font: {
                                            size: 11,
                                            weight: 'bold',
                                            family: "'Inter', sans-serif"
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        font: { size: 12, weight: 'bold', family: "'Inter', sans-serif" },
                                        color: '#4a5568',
                                        usePointStyle: true,
                                        padding: 20
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(45, 55, 72, 0.9)',
                                    titleFont: { size: 14, family: "'Inter', sans-serif" },
                                    bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: true
                                }
                            }
                        }
                    });
                } else {
                    console.error('Chart.js não foi carregado corretamente.');
                }
            });
        </script>
    @endpush
@endsection