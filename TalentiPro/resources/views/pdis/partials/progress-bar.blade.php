<div style="display: flex; align-items: center; gap: 10px; width: 100%;">
    <div style="background-color: #edf2f7; border-radius: 9999px; height: 10px; flex-grow: 1; overflow: hidden; position: relative;">
        <div class="progress-bar-fill" style="background-color: #6a4c7c; height: 100%; border-radius: 9999px; width: {{ $progresso }}%; transition: width 0.4s ease-in-out;"></div>
    </div>
    <span class="progress-bar-text" style="font-size: 0.85rem; font-weight: bold; color: #4a5568; min-width: 35px; text-align: right;">{{ $progresso }}%</span>
</div>
