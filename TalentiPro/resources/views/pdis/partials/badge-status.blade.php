@php
    $bg    = 'rgba(100, 116, 139, 0.12)';
    $color = '#475569';
    $icon  = 'fa-hourglass-start';
    $label = 'Não Iniciado';

    switch($status) {
        case 'nao_iniciado':
            $bg    = 'rgba(100, 116, 139, 0.12)';
            $color = '#475569';
            $icon  = 'fa-hourglass-start';
            $label = 'Não Iniciado';
            break;
        case 'em_andamento':
            $bg    = 'rgba(245, 158, 11, 0.15)';
            $color = '#b45309';
            $icon  = 'fa-spinner';
            $label = 'Em Andamento';
            break;
        case 'concluido':
            $bg    = 'rgba(16, 185, 129, 0.15)';
            $color = '#065f46';
            $icon  = 'fa-circle-check';
            $label = 'Concluído';
            break;
        case 'atrasado':
            $bg    = 'rgba(239, 68, 68, 0.15)';
            $color = '#991b1b';
            $icon  = 'fa-clock-rotate-left';
            $label = 'Atrasado';
            break;
        case 'cancelado':
            $bg    = 'rgba(100, 116, 139, 0.1)';
            $color = '#64748b';
            $icon  = 'fa-ban';
            $label = 'Cancelado';
            break;
    }
@endphp

<span style="background: {{ $bg }}; color: {{ $color }}; padding: 5px 13px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; display: inline-flex; align-items: center; gap: 6px; letter-spacing: 0.02em; white-space: nowrap;">
    <i class="fa-solid {{ $icon }}" style="font-size: 0.72rem;"></i>
    {{ $label }}
</span>
