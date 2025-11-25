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
                    <ul class="list-unstyled footer-links">
	<li><i class="fa fa-whatsapp"></i><a href="https://api.whatsapp.com/send?phone=5511947446739" target="_blank">(11) 94744-6739</a></li>
	<li><i class="fa fa-envelope"></i><a href="/cdn-cgi/l/email-protection#2a4945445e4b5e456a444b5e5f584b464643594b5904494547044858"><span class="__cf_email__" data-cfemail="5636393822372239163837222324373a3a3f2537257835393b783424">[email&#160;protected]</span></a></li>
</ul>                    <div class="rede-social-topo">
                        {{-- Redes sociais gerenciadas via admin --}}
                        {!! social_networks() !!}
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4 col-md-2 boxHeight">
                <div class="site-seguro">
                    <h5>Site seguro</h5>
                    <a href="https://transparencyreport.google.com/safe-browsing/search?url=https://naturallisas.com.br/">
                        <img class="img-responsive" src="https://naturallisas.com.br/lojas/naturallis/theme/assets/img/google-safe-browsing.svg" alt="Você está em uma navegação segura." title="Google Safe Browsing">
                    </a>
                    <img class="img-responsive" src="https://naturallisas.com.br/lojas/naturallis/theme/assets/img/selo-rapidssl.svg" alt="Você está em um site seguro." title="Site Seguro">
                                        <img class="img-responsive img-pagar-me" src="https://naturallisas.com.br/lojas/naturallis/theme/assets/img/selo-pagar-me.svg" alt="Pagamento seguro" title="Pagar.me">
                                    </div>
            </div>

        </div>
    </div>

    <div class="copyright">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="flex-center">
                        <p>&copy;<span id="anoAtual"></span>&nbsp;Naturallis Alimenta&ccedil;&atilde;o Saud&aacute;vel - Todos os direitos reservados</p>

<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script>
  document.getElementById("anoAtual").textContent = new Date().getFullYear();
</script>

<p>&nbsp;</p>

<p>&nbsp;</p>                        <a class="safe-browsing" href="https://transparencyreport.google.com/safe-browsing/search?url=https://naturallisas.com.br/">
    <img class="img-responsive" src="https://cdn.oceanserver.com.br/cdn/google-safe-browsing.png" alt="Você está em uma navegação segura." title="Google Safe Browsing" width="118" height="38" style="margin-left:15px;">
</a>
<a class="logo-by" href="https://octofood.com.br/" target="_blank" rel="noopener">
    <img src="https://cdn.oceanserver.com.br/cdn/byoctofood4.svg" alt="Octofood - Plataforma de E-commerce Especializada em Alimentos." title="Octofood - Plataforma de E-commerce Especializada em Alimentos." width="35" height="25">
</a>                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cookie Consent LGPD (gerenciado via admin) --}}
    {!! cookie_consent() !!}

</footer>
