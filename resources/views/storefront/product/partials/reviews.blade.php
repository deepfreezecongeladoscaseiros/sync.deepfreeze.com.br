{{--
    Partial: Avaliações dos Clientes

    Exibe avaliações/depoimentos aprovados do produto, com:
    - Resumo (estrelas + média + total)
    - Lista de avaliações individuais (máx 10)
    - Formulário para nova avaliação (se logado via guard customer)

    Variáveis:
    - $reviews: Collection de Depoimento (com reviewer_name via join)
    - $productStars: ['estrelas' => int, 'total' => int]
    - $product: Product model
--}}

<section class="product-reviews">
    <div class="group-box box-avaliacoes">

        {{-- Título da seção --}}
        <h3 class="titulo-desc">
            <i class="fa fa-star"></i> Avaliações dos Clientes
        </h3>

        {{-- Resumo: estrelas + média + total --}}
        <div class="reviews-summary">
            <div class="reviews-stars-summary">
                @for($i = 1; $i <= 5; $i++)
                    <svg class="star-icon star-summary" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M12 2.5c.4 0 .7.2.9.5l2.5 5 5.5.8c.5.1.8.5.8 1 0 .2-.1.5-.3.7l-4 3.9.9 5.5c.1.5-.1.9-.5 1.1-.2.1-.4.2-.6.2-.2 0-.3 0-.5-.1L12 18.3l-4.9 2.6c-.4.2-.9.2-1.3-.1-.3-.2-.5-.6-.4-1.1l.9-5.5-4-3.9c-.3-.3-.4-.8-.2-1.2.2-.4.5-.6.9-.7l5.5-.8 2.5-5c.2-.3.6-.5 1-.5z"
                              fill="{{ $i <= $productStars['estrelas'] ? '#f5a623' : '#ddd' }}"/>
                    </svg>
                @endfor
            </div>
            <span class="reviews-average">
                @if($productStars['total'] > 0)
                    {{ $productStars['estrelas'] }}/5
                    <span class="reviews-count">({{ $productStars['total'] }} {{ $productStars['total'] === 1 ? 'avaliação' : 'avaliações' }})</span>
                @else
                    <span class="reviews-count">Nenhuma avaliação ainda</span>
                @endif
            </span>
        </div>

        {{-- Lista de avaliações individuais --}}
        @if($reviews->count() > 0)
            <div class="reviews-list">
                @foreach($reviews as $review)
                    <div class="review-item">
                        <div class="review-header">
                            {{-- Estrelas do review individual --}}
                            <div class="review-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="star-icon star-small" viewBox="0 0 24 24" width="16" height="16">
                                        <path d="M12 2.5c.4 0 .7.2.9.5l2.5 5 5.5.8c.5.1.8.5.8 1 0 .2-.1.5-.3.7l-4 3.9.9 5.5c.1.5-.1.9-.5 1.1-.2.1-.4.2-.6.2-.2 0-.3 0-.5-.1L12 18.3l-4.9 2.6c-.4.2-.9.2-1.3-.1-.3-.2-.5-.6-.4-1.1l.9-5.5-4-3.9c-.3-.3-.4-.8-.2-1.2.2-.4.5-.6.9-.7l5.5-.8 2.5-5c.2-.3.6-.5 1-.5z"
                                              fill="{{ $i <= $review->avaliacao ? '#f5a623' : '#ddd' }}"/>
                                    </svg>
                                @endfor
                            </div>
                            {{-- Nome e data --}}
                            <span class="review-author">{{ ucfirst(mb_strtolower($review->reviewer_name)) }}</span>
                            <span class="review-date">{{ \Carbon\Carbon::parse($review->created)->format('d/m/Y') }}</span>
                        </div>
                        <p class="review-text">{{ $review->depoimento }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Formulário de avaliação ou mensagem de login --}}
        <div class="review-form-wrapper">
            @auth('customer')
                {{-- Formulário para clientes logados --}}
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
                                    <svg class="star-icon star-pickable" data-value="{{ $i }}" viewBox="0 0 24 24" width="28" height="28" style="cursor: pointer;">
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
                {{-- Mensagem para visitantes não logados --}}
                <div class="review-login-prompt">
                    @if($reviews->count() === 0)
                        <p>Seja o primeiro a avaliar! <a href="{{ route('login') }}">Faça login</a> para deixar sua avaliação.</p>
                    @else
                        <p><a href="{{ route('login') }}">Faça login</a> para deixar sua avaliação.</p>
                    @endif
                </div>
            @endauth
        </div>

    </div>
</section>

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
