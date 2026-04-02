{{--
    Modal: Entrega na minha região

    Consulta se o CEP é atendido via AJAX (endpoint já existente).
    Substitui o iframe fancybox do legado que gerava 404.
--}}
<div class="modal fade" id="modalEntregaCep" tabindex="-1" role="dialog" aria-labelledby="modalEntregaCepLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">

            {{-- Header --}}
            <div class="modal-header" style="background: var(--color-primary, #013E3B); color: #fff; border: none; padding: 18px 24px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color: #fff; opacity: 0.8; font-size: 24px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalEntregaCepLabel" style="font-weight: 700;">
                    <i class="fa fa-map-marker"></i> Entrega na minha região?
                </h4>
            </div>

            {{-- Body --}}
            <div class="modal-body" style="padding: 24px;">

                {{-- Formulário de consulta --}}
                <div id="entrega-form-section">
                    <p style="color: #555; margin-bottom: 16px;">Informe seu CEP para verificar se realizamos entregas na sua região:</p>
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" id="entrega-cep-input" class="form-control input-lg"
                               placeholder="00000-000" style="border-radius: 30px 0 0 30px; height: 48px;">
                        <span class="input-group-btn">
                            <button type="button" id="entrega-cep-btn" class="btn btn-lg"
                                    style="background: var(--color-primary, #013E3B); color: #fff; border-radius: 0 30px 30px 0; height: 48px; padding: 0 20px;">
                                <i class="fa fa-search" id="entrega-cep-icon"></i>
                            </button>
                        </span>
                    </div>
                    <small class="text-muted" style="display: block; margin-top: 8px;">
                        Não sabe seu CEP? <a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" rel="noopener">Consulte aqui</a>
                    </small>
                </div>

                {{-- Resultado: Atendido --}}
                <div id="entrega-result-ok" style="display: none; text-align: center; padding: 20px 0;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #e8f5e9; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-check" style="font-size: 28px; color: #28a745;"></i>
                    </div>
                    <h4 style="color: #28a745; font-weight: 700; margin-bottom: 8px;">Entregamos na sua região!</h4>
                    <p style="color: #555;" id="entrega-result-endereco"></p>
                </div>

                {{-- Resultado: Não atendido --}}
                <div id="entrega-result-nao" style="display: none; text-align: center; padding: 20px 0;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #fff3e0; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-exclamation-triangle" style="font-size: 28px; color: #ff9800;"></i>
                    </div>
                    <h4 style="color: #e65100; font-weight: 700; margin-bottom: 8px;">Ainda não atendemos sua região</h4>
                    <p style="color: #555;">Registramos seu interesse! Estamos sempre expandindo nossas áreas de entrega.</p>
                </div>

                {{-- Resultado: Erro --}}
                <div id="entrega-result-erro" style="display: none; text-align: center; padding: 20px 0;">
                    <p style="color: #e74c3c;"><i class="fa fa-times-circle"></i> Ocorreu um erro ao consultar. Tente novamente.</p>
                </div>

            </div>

            {{-- Footer --}}
            <div class="modal-footer" style="border: none; padding: 12px 24px;">
                <button type="button" id="entrega-nova-consulta" class="btn" style="display: none; color: var(--color-primary, #013E3B);">
                    <i class="fa fa-refresh"></i> Consultar outro CEP
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>

        </div>
    </div>
</div>
