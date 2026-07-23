@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master', 'rh']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))

@section('title', 'Home')

@section('content_main')
<section class="work-area">
    <div class="big-logo">
        <i class="fa-solid fa-brain"></i>
        <h1 class="watermark">TALENTIPRO</h1>
    </div>
</section>
@endsection