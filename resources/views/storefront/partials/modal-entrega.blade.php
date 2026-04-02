{{--
    Modal: Entrega na minha região

    Consulta se o CEP é atendido via AJAX (endpoint já existente).
    Substitui o iframe fancybox do legado que gerava 404.

    NOTA: Usa estilos inline pois o theme-override.css sobrescreve
    .modal-content com background !important. Estilos inline ganham prioridade.
--}}
<div class="modal fade" id="modalEntregaCep" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width: 460px; margin-top: 10%;">
        <div class="modal-content" style="background-color: #fff !important; border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 8px 30px rgba(0,0,0,0.2);">

            {{-- Header --}}
            <div style="background-color: #013E3B; padding: 18px 24px; position: relative;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8; font-size: 22px; position: absolute; right: 16px; top: 14px; text-shadow: none;">
                    <span>&times;</span>
                </button>
                <h4 style="color: #fff; font-weight: 700; margin: 0; font-size: 1.2em;">
                    <i class="fa fa-map-marker" style="margin-right: 8px;"></i>Entrega na minha região?
                </h4>
            </div>

            {{-- Body --}}
            <div style="padding: 24px; background: #fff;">

                {{-- Formulário de consulta --}}
                <div id="entrega-form-section">
                    <p style="color: #555; margin-bottom: 16px; font-size: 14px;">
                        Informe seu CEP para verificar se realizamos entregas na sua região:
                    </p>

                    <div style="display: flex; max-width: 300px;">
                        <input type="text" id="entrega-cep-input"
                               placeholder="00000-000"
                               style="flex: 1; height: 46px; padding: 0 16px; border: 2px solid #ddd; border-right: none; border-radius: 30px 0 0 30px; font-size: 16px; outline: none; color: #333; background: #fff !important;">
                        <button type="button" id="entrega-cep-btn"
                                style="height: 46px; padding: 0 20px; background: #FFA733; border: 2px solid #FFA733; border-radius: 0 30px 30px 0; cursor: pointer; font-size: 14px; -webkit-appearance: none;">
                            <span id="entrega-cep-btn-label" style="color: #fff !important; font-weight: 600; display: inline-block;">Buscar</span>
                        </button>
                    </div>

                    <small style="display: block; margin-top: 10px; color: #999; font-size: 12px;">
                        Não sabe seu CEP?
                        <a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" rel="noopener" style="color: #013E3B; text-decoration: underline;">Consulte aqui</a>
                    </small>
                </div>

                {{-- Resultado: Atendido --}}
                <div id="entrega-result-ok" style="display: none; text-align: center; padding: 16px 0;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #e8f5e9; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-check" style="font-size: 26px; color: #28a745;"></i>
                    </div>
                    <h4 style="color: #28a745; font-weight: 700; margin: 0 0 8px; font-size: 1.15em;">Entregamos na sua região!</h4>
                    <p style="color: #555; margin: 0; font-size: 14px;" id="entrega-result-endereco"></p>
                </div>

                {{-- Resultado: Não atendido --}}
                <div id="entrega-result-nao" style="display: none; text-align: center; padding: 16px 0;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #fff3e0; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-exclamation-triangle" style="font-size: 26px; color: #ff9800;"></i>
                    </div>
                    <h4 style="color: #e65100; font-weight: 700; margin: 0 0 8px; font-size: 1.15em;">Ainda não atendemos sua região</h4>
                    <p style="color: #555; margin: 0; font-size: 14px;">Registramos seu interesse! Estamos sempre expandindo nossas áreas de entrega.</p>
                </div>

                {{-- Resultado: Erro --}}
                <div id="entrega-result-erro" style="display: none; text-align: center; padding: 16px 0;">
                    <p style="color: #e74c3c; margin: 0;"><i class="fa fa-times-circle"></i> Ocorreu um erro ao consultar. Tente novamente.</p>
                </div>

            </div>

            {{-- Footer --}}
            <div style="padding: 12px 24px; background: #fff; border-top: 1px solid #eee; text-align: right;">
                <button type="button" id="entrega-nova-consulta"
                        style="display: none; background: none; border: none; color: #013E3B; cursor: pointer; font-size: 13px; padding: 8px 12px;">
                    <i class="fa fa-refresh"></i> Consultar outro CEP
                </button>
                <button type="button" data-dismiss="modal"
                        style="background: #f0f0f0; border: none; border-radius: 30px; padding: 8px 24px; color: #333; cursor: pointer; font-size: 14px;">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>
