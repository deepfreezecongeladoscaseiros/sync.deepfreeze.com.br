{{--
    Componente: Estrelas de Avaliação

    Exibe 1-5 estrelas SVG com contagem de avaliações.
    Reutilizado em: card de produto, detalhes do produto.

    Variáveis:
    - $starCount: int (0-5) — quantidade de estrelas preenchidas
    - $reviewCount: int — total de avaliações
    - $size: string (opcional) — 'sm' (card, 18px) ou 'lg' (detalhes, 24px). Default: 'sm'
--}}
@if($starCount > 0)
    @php
        $starSize = ($size ?? 'sm') === 'lg' ? 26 : 20;
        $sizeClass = ($size ?? 'sm') === 'lg' ? 'df-rating--lg' : 'df-rating--sm';
    @endphp
    <div class="df-rating {{ $sizeClass }}">
        <div class="df-rating__stars">
            @for($i = 1; $i <= 5; $i++)
                <svg class="df-rating__star {{ $i <= $starCount ? 'df-rating__star--filled' : 'df-rating__star--empty' }}"
                     viewBox="0 0 24 24" width="{{ $starSize }}" height="{{ $starSize }}">
                    <path d="M12 1.5l2.9 6.3 6.6.9c.4.1.6.5.4.9l-.1.1-4.8 4.9 1.2 6.9c.1.4-.2.8-.6.8h-.3L12 18.8l-5.3 3.6c-.4.2-.8.1-1-.3v-.3l1.2-6.9L2.1 9.7c-.3-.3-.3-.7 0-1l.1-.1 6.6-.9L12 1.5z"/>
                </svg>
            @endfor
        </div>
        <span class="df-rating__count">({{ $reviewCount }})</span>
    </div>
@endif
