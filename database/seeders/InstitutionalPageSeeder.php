<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seeder para páginas institucionais migradas do sistema legado.
 *
 * Conteúdo extraído do legado (CakePHP) em 2026-04-22.
 * Páginas: Política de Privacidade, Política de Troca, Fretes e Entregas, Como Descongelar.
 */
class InstitutionalPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Política de Privacidade',
                'slug' => 'politica-de-privacidade',
                'meta_title' => 'Política de Privacidade - Deep Freeze',
                'meta_description' => 'Política de Privacidade da Deep Freeze Congelados Artesanais. Saiba como coletamos, utilizamos e protegemos seus dados pessoais conforme a LGPD.',
                'meta_keywords' => 'política de privacidade, LGPD, dados pessoais, deep freeze',
                'content' => $this->politicaPrivacidade(),
            ],
            [
                'title' => 'Política de Troca e Devolução',
                'slug' => 'politica-de-troca-devolucao',
                'meta_title' => 'Política de Troca e Devolução - Deep Freeze',
                'meta_description' => 'Conheça a política de troca, devolução e cancelamento da Deep Freeze Congelados Artesanais.',
                'meta_keywords' => 'política de troca, devolução, cancelamento, deep freeze',
                'content' => $this->politicaTroca(),
            ],
            [
                'title' => 'Fretes e Entregas',
                'slug' => 'fretes-e-entregas',
                'meta_title' => 'Fretes e Entregas - Deep Freeze',
                'meta_description' => 'Informações sobre fretes, entregas e grade de horários da Deep Freeze Congelados Artesanais no Rio de Janeiro e Niterói.',
                'meta_keywords' => 'frete, entrega, delivery, rio de janeiro, niterói, deep freeze',
                'content' => $this->fretesEntregas(),
            ],
            [
                'title' => 'Como Descongelar',
                'slug' => 'como-descongelar',
                'meta_title' => 'Como Descongelar - Deep Freeze',
                'meta_description' => 'Aprenda a descongelar corretamente os pratos Deep Freeze: sopas, massas, tortas, risottos, carnes, sobremesas e mais.',
                'meta_keywords' => 'como descongelar, instruções, microondas, forno, deep freeze',
                'content' => $this->comoDescongelar(),
            ],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['active' => true])
            );
        }

        $this->command->info('Páginas institucionais criadas/atualizadas com sucesso.');
    }

    private function politicaPrivacidade(): string
    {
        return <<<'HTML'
<p>A Deep Freeze tem o compromisso com a transparência, a privacidade e a segurança dos dados de seus clientes durante todo o processo de interação com nosso site, aplicativo e lojas físicas. Para que entendam melhor quais informações coletamos e como as utilizamos, armazenamos ou excluímos, detalhamos a seguir nossa Política de Privacidade.</p>

<p>Os dados cadastrais dos clientes não são divulgados para terceiros, exceto quando necessários para o processo de entrega, para cobrança ou participação em promoções solicitadas pelos clientes. Seus dados pessoais são peça fundamental para que o pedido chegue em segurança na sua casa, de acordo com o prazo de entrega estipulado.</p>

<p>Para que a Política de Privacidade seja bem compreendida, é fundamental esclarecer alguns conceitos importantes:</p>

<p>→ <strong>Cliente</strong> – toda pessoa física que adquire produtos ou serviços nas lojas físicas, site e aplicativo;</p>

<p>→ <strong>Dados pessoais</strong> – qualquer informação relacionada a uma pessoa que a identifique ou que, usada em combinação com outras informações tratadas, identifique um indivíduo. Ainda, qualquer informação por meio da qual seja possível identificar uma pessoa ou entrar em contato com ela.</p>

<p>→ <strong>Tratamento de dados pessoais</strong> – considera-se tratamento de dado pessoal a coleta, produção, recepção, classificação, utilização, acesso, reprodução, transmissão, distribuição, processamento, arquivamento, armazenamento, eliminação, avaliação ou controle da informação, comunicação, transferência, difusão ou extração de dados de pessoas físicas.</p>

<p>→ <strong>Titular de dados</strong> – qualquer pessoa física que tenha seus dados pessoais tratados pela Deep Freeze;</p>

<p>→ <strong>Finalidade</strong> – o que queremos alcançar com o tratamento de dados pessoais.</p>

<p>→ <strong>Necessidade</strong> – o tratamento de dados pessoais deve se limitar ao mínimo necessário para o propósito almejado. Ou seja, deve ser pertinente, proporcional e não excessivo.</p>

<p>→ <strong>Consentimento</strong> – autorização clara e objetiva que o titular dá para tratamento de seus dados pessoais com finalidade previamente estipulada. Após dar o consentimento, você pode revogá-lo a qualquer momento. A revogação não cancela os processamentos realizados previamente.</p>

<h3>1. A quem essa política de privacidade se aplica?</h3>

<p>Aplica-se a todos os clientes da Deep Freeze, incluindo site, aplicativo e lojas físicas, que de alguma forma tenham os dados pessoais tratados por nós.</p>

<h3>2. Que tipo de informações pessoais coletamos e utilizamos?</h3>

<p>A Deep Freeze coleta e armazena os seguintes tipos de informações:</p>

<p><strong>Informações que você nos fornece:</strong> a Deep Freeze coleta informações do Cliente de diversas formas: quando você fornece seu CPF para emissão da nota fiscal; troca algum produto; adquire um dos serviços oferecidos pelas lojas físicas, site ou aplicativo; cria uma conta e fornece dados no seu cadastro; faz um pedido em nosso site ou aplicativo; retira um produto que comprou pelo site ou aplicativo nas lojas físicas; participa do sorteio mensal; quando interage com o Serviço de Atendimento ao Consumidor; quando participa de pesquisas ou promoções de marketing etc. Dentre as informações que podem ser solicitadas estão: nome completo, e-mail, CPF, data de nascimento, gênero, endereço, números de telefone e número do cartão de crédito.</p>

<p><strong>Informações coletadas automaticamente pela Deep Freeze:</strong> coletamos e armazenamos determinadas informações sempre que o Cliente interage conosco. Por exemplo, utilizamos cookies e obtemos informações quando seu navegador acessa o nosso site; quando você clica em anúncios, e-mails de parceiros e outros conteúdos fornecidos por nós em outros sites.</p>

<p><strong>Informações coletadas pelo dispositivo móvel:</strong> quando o Cliente baixa ou utiliza o nosso aplicativo, podemos receber dados sobre sua localização e seu dispositivo móvel. Podemos utilizar essas informações para oferecer conteúdos personalizados como, por exemplo, anúncios, resultados de buscas e outros. A maioria dos dispositivos móveis permite que o Cliente desligue os serviços de localização.</p>

<p><strong>Informações coletadas de outras fontes:</strong></p>
<ul>
<li>fontes públicas (por exemplo, dados demográficos);</li>
<li>agências ou órgãos de crédito (por exemplo, relatórios de crédito/cheques);</li>
<li>provedores de dados (por exemplo, dados demográficos relacionados a anúncios publicitários on-line e com base nos interesses do Cliente).</li>
</ul>

<h3>3. Por que solicitamos seus dados pessoais?</h3>

<p>Os dados são necessários para:</p>
<ul>
<li>entregar os produtos adquiridos ou o serviço contratado;</li>
<li>otimizar sua interação conosco;</li>
<li>garantir a segurança do site, de seu aplicativo e dos dados que processa;</li>
<li>informar o Cliente sobre as ofertas e divulgar os nossos serviços;</li>
<li>cumprir obrigações legais;</li>
<li>fins administrativos e de gestão.</li>
</ul>

<p>O fornecimento dos dados permite:</p>
<ul>
<li>entregar os produtos ou serviços adquiridos;</li>
<li>realizar o processo de troca ou devolução de produtos, dentro do prazo estabelecido pelo CDC (Código de Defesa do Consumidor);</li>
<li>enviar atualizações sobre o status do pedido e comunicações de serviços;</li>
<li>enviar brindes ou oferecer benefícios;</li>
<li>coordenar com parceiros a entrega ou disponibilização de serviços;</li>
<li>prestar serviços adequados às necessidades do Cliente;</li>
<li>melhorar a experiência de compra dos clientes na Deep Freeze;</li>
<li>fazer análises e pesquisas de mercado;</li>
<li>manter o Cliente informado sobre os produtos e serviços que oferecemos;</li>
<li>executar publicidade online direcionada;</li>
<li>prevenir, detectar e investigar atividades que estejam em desacordo com o nosso Código de Conduta ou que sejam proibidas ou ilegais; melhorar nosso website, produtos e serviços.</li>
</ul>

<p>A Deep Freeze utiliza bases legais, que podem variar de acordo com a finalidade da coleta, para tratar os dados pessoais dos clientes. O prazo de armazenamento pode mudar conforme a base legal aplicável a cada situação e a finalidade.</p>

<p><strong>3.1 Serviços de marketing</strong></p>

<p>Os serviços de marketing são oferecidos por meio de comunicações gratuitas sobre ofertas, serviços e produtos dirigidas ao Cliente, relacionadas ao seu perfil no site e a compras que fez. Esse serviço abrange e-mails, SMS e WhatsApp. Vale ressaltar que o Cliente pode cancelar o serviço, a qualquer momento acessando o seu cadastro no site.</p>

<h3>4. Com quem compartilhamos seus dados?</h3>

<p>As informações coletadas são tratadas dentro da Deep Freeze e somente serão compartilhadas quando forem necessárias: (i) para prestação adequada dos serviços objeto de suas atividades com empresas parceiras; (ii) para proteção em caso de conflito; (iii) mediante decisão judicial ou requisição de autoridade competente; (iv) com empresas provedoras de infraestrutura tecnológica e operacional, como empresas intermediadoras de pagamento e provedoras de serviço de armazenamento de informações.</p>

<h3>5. Transferência internacional de dados</h3>

<p>Como a internet é um ambiente global, determinados serviços oferecidos pela Deep Freeze podem demandar a transferência dos seus dados para outros países.</p>

<p>Nesses casos, os dados são tratados de acordo com a LGPD (Lei Geral de Proteção de Dados) e demais legislações de proteção. Tomamos medidas de segurança de acordo com nossas políticas e adotamos cláusulas padrão nos contratos com fornecedores e prestadores de serviço.</p>

<p>Ao navegar em nosso site, aplicativo, comprar em nossas lojas físicas e/ou se comunicar conosco, você concorda com o tratamento de suas informações, inclusive a transferência internacional de dados, quando necessário. Adotamos medidas para garantir que quaisquer informações coletadas sejam tratadas com segurança, conforme os padrões de proteção de dados e de acordo com esta Política de Privacidade.</p>

<h3>6. Por quanto tempo a Deep Freeze armazena informações pessoais?</h3>

<p>Armazenamos as informações dos Clientes de acordo com as normas de prescrição do Direito brasileiro.</p>

<h3>7. Quais são os direitos do titular de dados?</h3>

<p>O titular dos dados pessoais possui o direito de obter da Deep Freeze, a qualquer momento, mediante requisição formal, informações referentes aos seus dados.</p>

<p>A Deep Freeze terá 15 dias para responder às solicitações dos titulares. Os pedidos serão analisados conforme previsto em legislação vigente e, por questões legais, algumas solicitações podem não ser atendidas.</p>

<p>Os titulares dos dados, segundo o texto da LGPD, podem exercer os seus direitos por meio de:</p>
<ol type="I">
<li>confirmação da existência de tratamento;</li>
<li>acesso aos dados;</li>
<li>correção de dados incompletos, inexatos ou desatualizados;</li>
<li>anonimização, bloqueio ou eliminação de dados desnecessários, excessivos ou tratados em desconformidade com o disposto nesta Lei;</li>
<li>portabilidade dos dados a outro fornecedor de serviço ou produto, mediante requisição expressa, de acordo com a regulamentação da autoridade nacional, observados os segredos comercial e industrial;</li>
<li>eliminação dos dados pessoais tratados com o consentimento do titular;</li>
<li>informação das entidades públicas e privadas com as quais o controlador realizou uso compartilhado de dados;</li>
<li>informação sobre a possibilidade de não fornecer consentimento e sobre as consequências da negativa;</li>
<li>revogação do consentimento.</li>
</ol>

<h3>8. Como exercer os seus direitos?</h3>

<p>Você pode exercer seus direitos entrando em contato pelo e-mail <a href="mailto:sac@deepfreeze.com.br">sac@deepfreeze.com.br</a> ou ligando para (21) 3478-3000. Para mudar suas preferências em relação às comunicações de marketing a qualquer momento, você pode acessar o seu cadastro em nosso site.</p>

<h3>9. Cookies e tecnologias semelhantes</h3>

<p>Cookies são pequenos arquivos de dados que são colocados no seu computador ou em outros dispositivos (como 'smartphones' ou 'tablets') enquanto você navega no site.</p>

<p>Utilizamos cookies, pixels e outras tecnologias (coletivamente, "cookies") para reconhecer seu navegador ou dispositivo, aprender mais sobre seus interesses, fornecer recursos e serviços essenciais e também para:</p>
<ul>
<li>acompanhar suas preferências para enviar somente anúncios de seu interesse;</li>
<li>acompanhar os itens armazenados no seu carrinho de compras;</li>
<li>realização de pesquisas e diagnósticos para melhorar o conteúdo, produtos e serviços;</li>
<li>impedir atividades fraudulentas;</li>
<li>melhorar a segurança.</li>
</ul>

<p>Se você bloquear ou rejeitar nossos cookies, não poderá adicionar itens ao seu carrinho de compras, prosseguir para o checkout ou usar nossos produtos e serviços que exijam login.</p>

<h3>10. Como você pode manter suas informações pessoais seguras?</h3>

<p>A Deep Freeze utiliza os melhores protocolos de segurança para preservar a privacidade dos dados dos Clientes, mas também recomenda medidas de proteção individual.</p>

<p>Para manter a segurança e proteção dos seus dados pessoais fornecidos no cadastro ou nas compras, em nenhuma hipótese o seu login e senha devem ser compartilhados com terceiros. Além disso, ao acessar o seu cadastro, principalmente em computadores públicos, certifique-se de que você realizou o logout da sua conta para evitar que pessoas não autorizadas acessem e utilizem as informações sem o seu conhecimento.</p>

<p>Não entramos em contato por telefone, WhatsApp, SMS ou e-mail solicitando dados pessoais. Em nenhuma hipótese eles devem ser fornecidos, pois pode ser uma tentativa de uso indevido.</p>

<h3>11. Outras informações importantes sobre proteção de dados</h3>

<p>Para garantir que as regras estão claras e precisas, podemos alterar essa política a qualquer momento, publicando a Política de Privacidade revisada neste site.</p>

<h3>12. Como entrar em contato com o encarregado da proteção de dados da Deep Freeze?</h3>

<p>O encarregado da proteção de dados é o responsável escolhido pela Deep Freeze para atuar como canal de comunicação entre o controlador, os titulares dos dados e a Autoridade Nacional de Proteção de Dados (ANPD). Qualquer dúvida sobre tratamento de dados pessoais poderá ser encaminhada para o e-mail <a href="mailto:sac@deepfreeze.com.br">sac@deepfreeze.com.br</a>.</p>

<h3>13. Legislação e foro</h3>

<p>Esta política será regida, interpretada e executada de acordo com as Leis da República Federativa do Brasil, especialmente a Lei nº 13.709/2018, independentemente das Leis de outros estados ou países, sendo competente o foro de domicílio do Cliente para dirimir qualquer dúvida decorrente deste documento.</p>
HTML;
    }

    private function politicaTroca(): string
    {
        return <<<'HTML'
<p><strong>Política de Desistência, Troca, Devolução e Cancelamento Deep Freeze Congelados Artesanais</strong></p>

<h3>1) Cancelamento por desistência</h3>

<p>É facultado ao cliente desistir da compra, desde que, por se tratar de produtos congelados, de alta perecibilidade, que tal interesse seja manifestado em até 24 (vinte e quatro) horas, contados da data de recebimento do produto.</p>

<p>A desistência da compra deverá ser manifestada, expressamente, a ser comunicada/informada nos canais de relacionamento com o cliente, preenchendo as seguintes informações:</p>

<ul>
<li>Nome completo</li>
<li>Endereço completo (incluindo CEP, cidade e estado)</li>
<li>E-mail</li>
<li>Telefone para contato</li>
<li>Número do pedido</li>
<li>Número da nota fiscal</li>
<li>Listagem de produtos a serem devolvidos</li>
<li>Motivo da desistência</li>
</ul>

<p>O cliente será ressarcido do(s) valor pago (através da mesma forma de pagamento utilizado para a compra) do(s) produto(s), por ocasião da devolução(ões) deste(s) em sua(s) embalagem(s) original(ais) e congelado(s), que não poderá(rão) ter sido violada(s) e sem qualquer tipo de dano, acompanhada(s) de sua respectiva nota fiscal.</p>

<h3>2) Troca por erro operacional</h3>

<p>No caso de equívoco na quantidade ou tipo(s) do(s) produto(s), a ser manifestado no ato da respectiva entrega, que deverá ser recusada, e que, caso recebida, o cliente deverá, observando os mesmos cuidados e pelas razões indicadas no item 1, que trata do regime de cancelamento, deverá no máximo de 24h (vinte e quatro), contados do recebimento deste(s) a ser comunicado/informado nos canais de relacionamento com o cliente, preenchendo as mesmas informações solicitadas no item 1, para dar início ao processo de troca de acordo com o pedido e especificações do motivo da troca.</p>

<p>O cliente, no caso de devolução de produto por erro operacional, poderá optar em receber o valor integral correspondente ao pedido realizado, cujo reembolso se dará através da mesma forma de pagamento utilizada para a compra.</p>

<p>O cliente poderá optar, também, pela troca apenas do produto que estiver em desacordo com o pedido e/ou por produto similar ou de mesmo valor da mercadoria adquirida.</p>

<p>Em todas as hipóteses se faz necessário a devolução do(s) produto(s) objeto da troca, acompanhada da respectiva nota fiscal.</p>

<p>As trocas poderão ser realizadas, também, com a observância de todas as cautelas já informadas em relação a condição do produto, diretamente, nas lojas Deep Freeze, mediante, prévio contato pelos meios acima indicados.</p>

<p>Caso o preço do produto a ser trocado seja menor, será fornecido um crédito, a ser consignado junto ao cadastro do cliente, no caso de troca de produto de maior valor o pagamento da diferença deverá ser realizado no ato da troca.</p>

<h3>3) Troca em decorrência do recebimento do produto fora das especificações</h3>

<p>Em caso de produto que esteja em desacordo com as especificações constantes do pedido, que possua avarias ou que possua vício de qualidade ou quantidade, a Deep Freeze assume a responsabilidade pela troca ou pelo ressarcimento integral do(s) produto(s).</p>

<p>Caso não tenha disponibilidade do(s) produto(s) em nossos estoques para troca, o cliente poderá escolher uma das seguintes alternativas:</p>

<ul>
<li>Receber o valor integral do produto, através da mesma forma de pagamento utilizada para a compra.</li>
<li>Autorizar a troca do produto que estiver em desacordo com o pedido por produto similar ou de mesmo valor da mercadoria adquirida.</li>
</ul>

<p>Caso o preço do produto a ser trocado seja menor, será fornecido um crédito, a ser consignado junto ao cadastro do cliente, no caso de troca de produto de maior valor o pagamento da diferença deverá ser realizado no ato da troca.</p>

<p>Seja qual for o interesse do cliente, tal como informado nos casos previstos nas hipóteses dos itens números 1 e 2 acima, deverá ser comunicado a Deep Freeze, através dos meios de contato/relacionamento, fornecendo os respectivos dados e indicações do motivo da troca.</p>

<h3>4) Cancelamento de pedido ainda não processado e/ou não entregue</h3>

<p>Os cancelamentos serão facilmente realizados, desde que o pedido ainda não tenha sido processado, bastando entrar em contato com um dos canais de relacionamento com o cliente; e no caso de processado, mas sem que tenha ocorrido a entrega, o que, igualmente, deverá ser manifestado através de tais canais de relacionamento, será efetuado o estorno de acordo com a forma de pagamento utilizada na compra.</p>

<h3>5) Situação de cancelamento por iniciativa da Deep Freeze</h3>

<p>A Deep Freeze se reserva o direito de cancelar pedidos realizados pelo cliente nas seguintes situações:</p>

<p><strong>5.1 Impossibilidade de execução do pagamento:</strong> Neste caso, uma vez comunicado o cliente deverá indicar outra forma de pagamento, sob pena de ser considerado como cancelado o pedido.</p>

<p><strong>5.2 Inconsistência de dados informados pelo cliente no pedido:</strong> caso os pedidos apresentem divergência e/ou ausência de dados que impossibilitem concluir o processo de compra, entraremos em contato para tentar finalizar o processo, e caso verificada a impossibilidade deste será considerado cancelado o pedido, comunicando ao cliente através de e-mail ou Central de Relacionamento.</p>

<p><strong>5.3 Cancelamento por ausência de responsável no ato do recebimento:</strong> em caso de ausência de pessoa responsável no momento da entrega, após uma tentativa comprovada de entrega, será cancelado o pedido, uma vez as entregas realizadas pela Deep Freeze são previamente agendadas pelo cliente e, em se tratando de produto perecível e que requer refrigeração adequada, é necessário que haja um responsável para receber o pedido e armazená-lo convenientemente a fim de garantir a qualidade e integridade dos produtos.</p>

<p>Nestes casos, o valor do pedido será estornado de acordo com a forma de pagamento utilizada na compra.</p>

<p>O cliente, caso queira, poderá retificar ou ajustar o pedido, através dos nossos meios de relacionamento, sendo que, nesta hipótese, de segunda entrega, haverá cobrança de nova taxa de entrega.</p>
HTML;
    }

    private function fretesEntregas(): string
    {
        return <<<'HTML'
<p>Nosso sistema está programado para receber pedidos de forma automática para as cidades do Rio de Janeiro - RJ, Niterói - RJ e outras regiões do RJ. Para outras cidades ou estados, entre em contato com nosso televendas (21) 3478-3000 (Rio).</p>

<h3>Zona Sul, Grande Tijuca e Barra da Tijuca</h3>

<p>De acordo com a grade de horários, para pedidos realizados com 24h de antecedência.</p>

<h3>Delivery</h3>

<p>Você pede e nós entregamos.</p>

<h3>Demais localidades do Rio e Niterói</h3>

<p>O valor mínimo dependerá da distância da loja mais próxima para a entrega, agendada de acordo com a nossa grade de horários.</p>

<h3>Grade de horários</h3>

<p>Segunda à Sexta, de 09h às 12h, 12h às 18h e 18h às 20h. Sábado de 10h às 16h.</p>
HTML;
    }

    private function comoDescongelar(): string
    {
        return <<<'HTML'
<h3 style="text-align: center; color: #000080;"><strong>Como Descongelar Corretamente o Seu Prato!</strong></h3>

<h4 style="color: #ff6600;">Sopas &amp; Caldinhos</h4>
<p>Podem ser descongelados no micro-ondas, no forno convencional ou na panela por um tempo médio de 6 minutos. Incremente com torradas, queijos e azeites de sua preferência.</p>

<h4 style="color: #ff6600;">Massas &amp; Panquecas</h4>
<p>As massas podem ter seu descongelamento no micro-ondas por um tempo médio de 5 minutos, mas para que fiquem mais saborosas, gratine no forno convencional, elétrico ou utilize o sistema grill de seu micro-ondas. Para preservar a textura e crocância de suas panquecas, aconselhamos que as mesmas sejam descongeladas diretamente em forno convencional ou elétrico.</p>

<h4 style="color: #ff6600;">Tortas &amp; Empadões</h4>
<p>O descongelamento de tortas e empadões deve ser realizado no forno convencional ou forno elétrico, mantendo assim a textura e crocância esperada.</p>

<h4 style="color: #ff6600;">Risottos</h4>
<p>Sugerimos o descongelamento no micro-ondas, caso se opte pelo forno convencional que seja utilizado em forno baixo para que o alimento descongele gradativamente mantendo a cremosidade.</p>

<h4 style="color: #ff6600;">Kit Refeição</h4>
<p>Exclusivo de uso em micro-ondas. Sugerimos que após retirar o plástico da embalagem, deixe a tampa fechada para que mantenha a umidade do alimento.</p>

<h4 style="color: #ff6600;">Lanches &amp; Pães</h4>
<p>Sugerimos o descongelamento somente em forno convencional ou forno elétrico para manter as características de crocância.</p>

<h4 style="color: #ff6600;">Sobremesas</h4>
<p>Excluindo a Canjica que deve ser descongelada em micro-ondas ou panela, todas as sobremesas devem ser descongeladas na geladeira gradativamente ou em temperatura ambiente para consumo imediato.</p>

<h4 style="color: #ff6600;">Carnes, Aves &amp; Peixes</h4>
<p>Podem ser descongeladas tanto no micro-ondas quanto no forno convencional, exceto os pratos light, que só podem ser levados ao micro-ondas devido ao material utilizado (polipropileno).</p>

<h4 style="color: #ff6600;">Acompanhamentos</h4>
<p>Podem ser descongelados no forno micro-ondas ou convencional. Para deixar seu arroz úmido e soltinho, sugerimos que seja colocado 1 ml de água filtrada por cima da preparação e deixe a tampa semi-aberta.</p>
HTML;
    }
}
