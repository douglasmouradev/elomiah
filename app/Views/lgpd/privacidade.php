<section class="page-hero container">
    <h1>Política de Privacidade</h1>
</section>
<section class="container prose">
    <p>A Elomiah trata dados pessoais de acordo com a Lei nº 13.709/2018 (LGPD). Controladora: Elomiah / Geo, contato via <?= e(url('/contato')) ?> e <?= e(url('/meus-dados')) ?>.</p>
    <h2>Dados que coletamos</h2>
    <ul>
        <li>Nome, e-mail e telefone — cadastro, pedidos e comunicação.</li>
        <li>CEP e endereço completo — entrega dos pedidos.</li>
        <li>No Pix: a chave de recebimento e o valor do pedido. No cartão, quando houver intermediador: bandeira, quatro últimos dígitos e parcelas. Número completo e CVV não ficam no site.</li>
        <li>IP (e, no rastreio de visitas, apenas o hash do IP) — segurança, rate limiting e auditoria.</li>
        <li>Consentimentos (cookies, privacidade) com data, IP e user-agent.</li>
    </ul>
    <h2>Finalidade</h2>
    <p>Executar o contrato de compra, atender solicitações, cumprir obrigações legais, prevenir fraude e, somente com aceite, entender o uso do site de forma agregada.</p>
    <h2>Base legal</h2>
    <p>Execução de contrato, consentimento (cookies não essenciais e comunicações opcionais) e legítimo interesse em segurança (rate limiting, logs de admin).</p>
    <h2>Compartilhamento</h2>
    <p>Transportadoras, instituição Pix (Nubank) e, no cartão, o intermediador cadastrado (Mercado Pago ou similar). Marketplaces quando a venda é Shopee, Amazon ou Mercado Livre. Não vendemos listas de clientes.</p>
    <h2>Retenção</h2>
    <p>Dados de pedido são mantidos pelo prazo fiscal. Demais dados, enquanto a conta existir ou até o titular solicitar exclusão, ressalvadas obrigações legais.</p>
    <h2>Direitos</h2>
    <p>Acesso, correção, portabilidade, eliminação, informação sobre compartilhamentos e revogação de consentimento. Exercício em <a href="<?= e(url('/meus-dados')) ?>">Seus dados</a>.</p>
    <h2>Cookies</h2>
    <p>Essenciais: sessão, CSRF, sacola. Não essenciais: métricas de visita, gravadas só se você tocar em Aceitar. “Só o essencial” e “Recusar” não geram esse rastreio.</p>
    <h2>Segurança</h2>
    <p>Senhas com hash (Argon2id/bcrypt). Sessões httponly, SameSite=Strict e Secure em produção. Prepared statements PDO. HTTPS obrigatório em produção.</p>
</section>
