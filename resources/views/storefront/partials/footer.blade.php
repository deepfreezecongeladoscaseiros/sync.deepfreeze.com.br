{{--
    Partial: Rodapé Completo da Loja

    Inclui:
    - Logo
    - Formas de Pagamento
    - Menu Institucional (dinâmico)
    - Contato com Redes Sociais
    - Site Seguro
    - Copyright
    - Cookie Consent LGPD
--}}

<footer class="footer">
    <div class="container">
        <div class="row">

            <div class="col-xs-12 col-sm-3 col-md-2 col-lg-3 boxHeight hidden-xs">
                <div class="logo">
                    <a href="{{ url("/") }}">
                        <img class="img-responsive" src="{{ theme_logo() }}" alt="{{ theme_logo_alt() }}" title="{{ theme_logo_alt() }}">
                    </a>
                </div>
            </div>

            <div class="col-xs-12 col-sm-9 col-md-2 boxHeight">
                <div class="forma-pagamento">
                    <h5>Formas de pagamento</h5>
                    <div class="group-icon">
                        <i class="oct icon-debito"></i><i class="oct icon-alelo"></i><i class="oct icon-pix"></i><i class="oct icon-visa"></i><i class="oct icon-ticket"></i><i class="oct icon-american"></i><i class="oct icon-sodexo"></i><i class="oct icon-dinners"></i><i class="oct icon-vr"></i><i class="oct icon-master"></i><i class="oct icon-ben"></i><i class="oct icon-greencard"></i><i class="oct icon-verocard"></i><i class="oct icon-dinheiro"></i>                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2 boxHeight">
                <div class="menu-footer">
                    <h5>Institucional</h5>
                    <ul class="list-unstyled footer-links">
                        {{-- Links dinâmicos de páginas internas ativas --}}
                        {!! institutional_pages_menu() !!}
                    </ul>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4 col-md-3 boxHeight">
                <div class="menu-contato">
                    <h5>Contato</h5>
                    @php $contact = contact_settings(); @endphp
                    <ul class="list-unstyled footer-links">
                        <li><i class="fa fa-whatsapp"></i><a href="{{ $contact->getWhatsAppUrl() }}" target="_blank">{{ $contact->whatsapp_display }}</a></li>
                        <li><i class="fa fa-envelope"></i><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></li>
                    </ul>                    <div class="rede-social-topo">
                        {{-- Redes sociais gerenciadas via admin --}}
                        {!! social_networks() !!}
                    </div>
                </div>
            </div>

            {{-- Seção de selos - usando assets locais --}}
            <div class="col-xs-12 col-sm-4 col-md-2 boxHeight">
                <div class="site-seguro">
                    <h5>Site seguro</h5>
                    {{-- Selos de segurança serão adicionados quando os assets locais estiverem disponíveis --}}
                    {{-- TODO: Adicionar selos locais em public/storefront/img/selos/ --}}
                </div>
            </div>

        </div>
    </div>

    <div class="copyright">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="flex-center">
                        <p>&copy;<span id="anoAtual"></span>&nbsp;Deep Freeze Congelados Artesanais ltda - Todos os direitos reservados - Rua Ant&ocirc;nio Bas&iacute;lio 562, Rio de Janeiro, RJ - CNPJ 20.025.886/0001-35 - IE 86.665.603</p>
                        <script>
                            document.getElementById("anoAtual").textContent = new Date().getFullYear();
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cookie Consent LGPD (gerenciado via admin) --}}
    {!! cookie_consent() !!}

</footer>
