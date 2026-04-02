{{--
    Partial: Avaliações dos Clientes (Carrossel)

    Exibe avaliações em carrossel horizontal (Owl Carousel 2) com:
    - Título centralizado com accent bar
    - Cards com avatar de iniciais + nome + estrelas + texto
    - Formulário para nova avaliação (se logado via guard customer)

    Variáveis:
    - $reviews: Collection de Depoimento (com reviewer_name via join)
    - $productStars: ['estrelas' => int, 'total' => int]
    - $product: Product model
--}}

<section class="product-reviews">

    {{-- Título da seção centralizado com accent bar --}}
    <div class="reviews-section-header">
        <h3 class="reviews-section-title">Avaliações dos Clientes</h3>
        <div class="reviews-accent-bar"></div>
        @if($productStars['total'] > 0)
            <div class="reviews-summary-line">
                <div class="reviews-stars-summary">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="star-icon" viewBox="0 0 24 24" width="20" height="20">
                            <path d="M12 2.5c.4 0 .7.2.9.5l2.5 5 5.5.8c.5.1.8.5.8 1 0 .2-.1.5-.3.7l-4 3.9.9 5.5c.1.5-.1.9-.5 1.1-.2.1-.4.2-.6.2-.2 0-.3 0-.5-.1L12 18.3l-4.9 2.6c-.4.2-.9.2-1.3-.1-.3-.2-.5-.6-.4-1.1l.9-5.5-4-3.9c-.3-.3-.4-.8-.2-1.2.2-.4.5-.6.9-.7l5.5-.8 2.5-5c.2-.3.6-.5 1-.5z"
                                  fill="{{ $i <= $productStars['estrelas'] ? '#f5a623' : '#ddd' }}"/>
                        </svg>
                    @endfor
                </div>
                <span class="reviews-summary-text">
                    {{ $productStars['estrelas'] }}/5
                    ({{ $productStars['total'] }} {{ $productStars['total'] === 1 ? 'avaliação' : 'avaliações' }})
                </span>
            </div>
        @endif
    </div>

    {{-- Carrossel de avaliações (só exibe se houver reviews) --}}
    @if($reviews->count() > 0)
        <div class="reviews-carousel-wrapper">
            <div class="js-reviews-carousel owl-carousel">
                @foreach($reviews as $review)
                    @php
                        // Gera iniciais: primeira letra do nome + primeira letra do último sobrenome
                        $nameParts = explode(' ', trim($review->reviewer_name));
                        $initials = mb_strtoupper(mb_substr($nameParts[0], 0, 1));
                        if (count($nameParts) > 1) {
                            $initials .= mb_strtoupper(mb_substr(end($nameParts), 0, 1));
                        }
                        // Cor do avatar baseada no nome (consistente para o mesmo nome)
                        $avatarColors = ['#013E3B', '#2E7D32', '#5D4037', '#F57C00', '#00695C', '#4E342E'];
                        $colorIndex = ord(mb_strtoupper(mb_substr($review->reviewer_name, 0, 1))) % count($avatarColors);
                        $avatarColor = $avatarColors[$colorIndex];
                    @endphp
                    <div class="review-card">
                        <div class="review-card-header">
                            {{-- Avatar com iniciais --}}
                            <div class="review-avatar" style="background-color: {{ $avatarColor }};">
                                <span>{{ $initials }}</span>
                            </div>
                            <div class="review-card-meta">
                                {{-- Nome do autor --}}
                                <span class="review-card-author">{{ ucfirst(mb_strtolower($nameParts[0])) }}</span>
                                {{-- Estrelas inline --}}
                                <div class="review-card-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="star-icon" viewBox="0 0 24 24" width="14" height="14">
                                            <path d="M12 2.5c.4 0 .7.2.9.5l2.5 5 5.5.8c.5.1.8.5.8 1 0 .2-.1.5-.3.7l-4 3.9.9 5.5c.1.5-.1.9-.5 1.1-.2.1-.4.2-.6.2-.2 0-.3 0-.5-.1L12 18.3l-4.9 2.6c-.4.2-.9.2-1.3-.1-.3-.2-.5-.6-.4-1.1l.9-5.5-4-3.9c-.3-.3-.4-.8-.2-1.2.2-.4.5-.6.9-.7l5.5-.8 2.5-5c.2-.3.6-.5 1-.5z"
                                                  fill="{{ $i <= $review->avaliacao ? '#f5a623' : '#ddd' }}"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        {{-- Texto do depoimento --}}
                        <p class="review-card-text">{{ $review->depoimento }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="reviews-empty-text">Nenhuma avaliação ainda. Seja o primeiro!</p>
    @endif

    {{-- Formulário de avaliação ou mensagem de login --}}
    <div class="review-form-wrapper">
        @auth('customer')
            <div id="reviewFormContainer">
                <h4 class="review-form-title">Deixe sua avaliação</h4>
                <form id="reviewForm">
                    @csrf
                    <input type="hidden" name="produto_id" value="{{ $product->id }}">
                    <input type="hidden" name="avaliacao" id="reviewRating" value="0">

                    {{-- Seletor de estrelas interativo --}}
                    <div class="review-star-picker">
                        <label>Sua nota:</label>
                        <div class="star-picker" id="starPicker">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="star-icon star-pickable" data-value="{{ $i }}" viewBox="0 0 24 24" width="28" height="28">
                                    <path d="M12 2.5c.4 0 .7.2.9.5l2.5 5 5.5.8c.5.1.8.5.8 1 0 .2-.1.5-.3.7l-4 3.9.9 5.5c.1.5-.1.9-.5 1.1-.2.1-.4.2-.6.2-.2 0-.3 0-.5-.1L12 18.3l-4.9 2.6c-.4.2-.9.2-1.3-.1-.3-.2-.5-.6-.4-1.1l.9-5.5-4-3.9c-.3-.3-.4-.8-.2-1.2.2-.4.5-.6.9-.7l5.5-.8 2.5-5c.2-.3.6-.5 1-.5z"
                                          fill="#ddd"/>
                                </svg>
                            @endfor
                        </div>
                    </div>

                    {{-- Textarea com contador de caracteres --}}
                    <div class="review-textarea-wrapper">
                        <textarea name="depoimento" id="reviewText" maxlength="200" rows="3"
                                  placeholder="Conte o que achou deste produto..."></textarea>
                        <span class="review-char-counter"><span id="charCount">0</span>/200</span>
                    </div>

                    {{-- Botão de envio --}}
                    <button type="submit" class="btn-review-submit" id="btnSubmitReview" disabled>
                        Enviar Avaliação
                    </button>
                </form>
            </div>

            {{-- Mensagem de sucesso (oculta inicialmente) --}}
            <div id="reviewSuccessMessage" style="display: none;" class="review-success">
                <i class="fa fa-check-circle"></i>
                <p>Obrigado! Sua avaliação será publicada após aprovação.</p>
            </div>
        @else
            <div class="review-login-prompt">
                @if($reviews->count() === 0)
                    <p>Seja o primeiro a avaliar! <a href="{{ route('login') }}">Faça login</a> para deixar sua avaliação.</p>
                @else
                    <p><a href="{{ route('login') }}">Faça login</a> para deixar sua avaliação.</p>
                @endif
            </div>
        @endauth
    </div>

</section>

{{-- Inicializa Owl Carousel para reviews --}}
@push('scripts')
<script>
$(document).ready(function() {
    // === Carrossel de Reviews (Owl Carousel 2) ===
    // Configuração segue o padrão do projeto (home/index.blade.php)
    $('.js-reviews-carousel').owlCarousel({
        loop: false,
        margin: 20,
        nav: true,
        navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
        dots: false,
        responsive: {
            0:    { items: 1 },
            768:  { items: 2 },
            992:  { items: 3 }
        }
    });
});
</script>
@endpush

{{-- Scripts do formulário de avaliação (só para clientes logados) --}}
@auth('customer')
@push('scripts')
<script>
$(document).ready(function() {
    // === Star Picker: Hover e Click ===
    var selectedRating = 0;

    // Hover: destaca estrelas temporariamente
    $('#starPicker .star-pickable').on('mouseenter', function() {
        var hoverVal = $(this).data('value');
        $('#starPicker .star-pickable').each(function() {
            var starVal = $(this).data('value');
            $(this).find('path').attr('fill', starVal <= hoverVal ? '#f5a623' : '#ddd');
        });
    });

    // Mouse leave: volta para o rating selecionado
    $('#starPicker').on('mouseleave', function() {
        $('#starPicker .star-pickable').each(function() {
            var starVal = $(this).data('value');
            $(this).find('path').attr('fill', starVal <= selectedRating ? '#f5a623' : '#ddd');
        });
    });

    // Click: seleciona o rating
    $('#starPicker .star-pickable').on('click', function() {
        selectedRating = $(this).data('value');
        $('#reviewRating').val(selectedRating);
        validateReviewForm();
    });

    // === Contador de caracteres ===
    $('#reviewText').on('input', function() {
        var len = $(this).val().length;
        $('#charCount').text(len);
        validateReviewForm();
    });

    // === Validação: habilita botão apenas com nota + texto preenchidos ===
    function validateReviewForm() {
        var hasRating = selectedRating > 0;
        var hasText = $('#reviewText').val().trim().length > 0;
        $('#btnSubmitReview').prop('disabled', !(hasRating && hasText));
    }

    // === Submit via AJAX ===
    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();

        var $btn = $('#btnSubmitReview');
        $btn.prop('disabled', true).text('Enviando...');

        $.ajax({
            url: '{{ route("review.store") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                produto_id: $('input[name="produto_id"]').val(),
                avaliacao: $('#reviewRating').val(),
                depoimento: $('#reviewText').val()
            },
            success: function(response) {
                if (response.success) {
                    // Esconde formulário e mostra mensagem de sucesso
                    $('#reviewFormContainer').fadeOut(300, function() {
                        $('#reviewSuccessMessage').fadeIn(300);
                    });
                }
            },
            error: function(xhr) {
                var msg = 'Erro ao enviar avaliação. Tente novamente.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                $btn.prop('disabled', false).text('Enviar Avaliação');
            }
        });
    });
});
</script>
@endpush
@endauth
