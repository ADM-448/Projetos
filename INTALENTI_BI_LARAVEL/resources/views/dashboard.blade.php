@extends('layouts.app')

@section('content')
    <div x-data="dashboardData()" class="min-h-screen flex flex-col font-sans relative">

        <!-- Novo Header Ventriloc -->
        @include('partials.dashboard.header')

        <!-- Main Content -->
        <main class="flex-1 w-full max-w-[1200px] mx-auto px-4 sm:px-8 pb-20 pt-8">

            @if (session('success'))
                <div class="bg-neon text-black px-4 py-2 text-center text-sm font-medium mb-8">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 text-red-600 px-4 py-2 text-center text-sm font-medium border border-red-200 mb-8">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Espaçamento generoso entre seções (80px no Ventriloc) -->
            <div class="space-y-[80px]">
                @include('partials.dashboard.kpis')
                @include('partials.dashboard.tabs.cenarios')
                @include('partials.dashboard.tabs.enquadramento')
                @include('partials.dashboard.tabs.dissidio')
                @include('partials.dashboard.tabs.mercado')
                @include('partials.dashboard.tabs.convencoes')
                @include('partials.dashboard.tabs.financeiro')
            </div>
            
        </main>

        @include('partials.dashboard.modals.raio_x')
        @include('partials.dashboard.modals.nova_vaga_cep')

    </div>
@endsection

@push('scripts')
    @include('partials.dashboard.scripts')
@endpush