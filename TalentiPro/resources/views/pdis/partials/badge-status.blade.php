@php
    $bg = '#edf2f7';
    $color = '#4a5568';
    $label = 'Não Iniciado';

    switch($status) {
        case 'nao_iniciado':
            $bg = '#edf2f7';
            $color = '#4a5568';
            $label = 'Não Iniciado';
            break;
        case 'em_andamento':
            $bg = '#fefcbf';
            $color = '#b7791f';
            $label = 'Em Andamento';
            break;
        case 'concluido':
            $bg = '#c6f6d5';
            $color = '#22543d';
            $label = 'Concluído';
            break;
        case 'atrasado':
            $bg = '#fed7d7';
            $color = '#9b2c2c';
            $label = 'Atrasado';
            break;
        case 'cancelado':
            $bg = '#e2e8f0';
            $color = '#4a5568';
            $label = 'Cancelado';
            break;
    }
@endphp

<span class="badge" style="background-color: {{ $bg }}; color: {{ $color }}; padding: 6px 12px; font-size: 0.85rem; font-weight: bold; border-radius: 6px; display: inline-block;">
    {{ $label }}
</span>
