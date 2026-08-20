<?php

declare(strict_types=1);

use App\Controllers\AchadinhosController;
use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\ContatoController as AdminContatoController;
use App\Controllers\Admin\CursoController as AdminCursoController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\DepoimentoController as AdminDepoimentoController;
use App\Controllers\Admin\FinanceiroController as AdminFinanceiroController;
use App\Controllers\Admin\LgpdController as AdminLgpdController;
use App\Controllers\Admin\LogController;
use App\Controllers\Admin\NotificacaoController as AdminNotificacaoController;
use App\Controllers\Admin\PedidoController as AdminPedidoController;
use App\Controllers\Admin\ProdutoController as AdminProdutoController;
use App\Controllers\AuthController;
use App\Controllers\CarrinhoController;
use App\Controllers\CheckoutController;
use App\Controllers\ContaController;
use App\Controllers\ContatoController;
use App\Controllers\CursoController;
use App\Controllers\DepoimentosController;
use App\Controllers\HomeController;
use App\Controllers\LgpdController;
use App\Controllers\LojaController;
use App\Controllers\PagamentoController;
use App\Controllers\ProdutoController;
use App\Controllers\SobreController;
use App\Core\Router;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/loja', [LojaController::class, 'index']);
    $router->get('/produto/{slug}', [ProdutoController::class, 'show']);
    $router->get('/curso', [CursoController::class, 'index']);
    $router->post('/curso/matricular', [CursoController::class, 'matricular']);
    $router->get('/achadinhos', [AchadinhosController::class, 'index']);
    $router->get('/sobre', [SobreController::class, 'index']);
    $router->get('/depoimentos', [DepoimentosController::class, 'index']);
    $router->post('/depoimentos', [DepoimentosController::class, 'store']);
    $router->get('/contato', [ContatoController::class, 'index']);
    $router->post('/contato', [ContatoController::class, 'store']);

    $router->get('/carrinho', [CarrinhoController::class, 'index']);
    $router->post('/carrinho/adicionar', [CarrinhoController::class, 'add']);
    $router->post('/carrinho/atualizar', [CarrinhoController::class, 'update']);
    $router->post('/carrinho/remover', [CarrinhoController::class, 'remove']);

    $conta = [AuthMiddleware::class];
    $router->get('/checkout', [CheckoutController::class, 'index'], $conta);
    $router->post('/checkout', [CheckoutController::class, 'store'], $conta);
    $router->get('/pedido/{codigo}/status', [CheckoutController::class, 'status']);
    $router->get('/pedido/{codigo}/nota', [CheckoutController::class, 'nota']);
    $router->post('/pedido/{codigo}/pagar', [CheckoutController::class, 'pagarCartao']);
    $router->get('/pedido/{codigo}', [CheckoutController::class, 'obrigado']);
    $router->get('/pagamento/retorno', [PagamentoController::class, 'retorno']);
    $router->get('/webhooks/mercadopago', [PagamentoController::class, 'webhook']);
    $router->post('/webhooks/mercadopago', [PagamentoController::class, 'webhook']);

    $router->get('/entrar', [AuthController::class, 'loginForm']);
    $router->post('/entrar', [AuthController::class, 'login']);
    $router->get('/cadastro', [AuthController::class, 'registerForm']);
    $router->post('/cadastro', [AuthController::class, 'register']);
    $router->get('/recuperar-senha', [AuthController::class, 'recuperarForm']);
    $router->post('/recuperar-senha', [AuthController::class, 'recuperar']);
    $router->get('/recuperar-senha/{token}', [AuthController::class, 'redefinirForm']);
    $router->post('/recuperar-senha/{token}', [AuthController::class, 'redefinir']);
    $router->get('/sair', [AuthController::class, 'logout']);
    $router->get('/conta', [ContaController::class, 'index'], $conta);
    $router->post('/conta/endereco', [ContaController::class, 'endereco'], $conta);
    $router->post('/conta/perfil', [ContaController::class, 'perfil'], $conta);
    $router->post('/conta/senha', [ContaController::class, 'senha'], $conta);
    $router->get('/conta/pedidos/{codigo}', [ContaController::class, 'pedido'], $conta);
    $router->post('/conta/pedidos/{codigo}/cancelar', [ContaController::class, 'cancelar'], $conta);
    $router->get('/conta/curso', [ContaController::class, 'curso'], $conta);

    $router->get('/privacidade', [LgpdController::class, 'privacidade']);
    $router->get('/termos', [LgpdController::class, 'termos']);
    $router->get('/meus-dados', [LgpdController::class, 'meusDados']);
    $router->post('/meus-dados', [LgpdController::class, 'solicitar']);
    $router->post('/cookies/consentimento', [LgpdController::class, 'cookies']);

    $admin = [AdminMiddleware::class];
    $router->get('/admin/login', [AdminAuthController::class, 'form']);
    $router->post('/admin/login', [AdminAuthController::class, 'login']);
    $router->get('/admin/sair', [AdminAuthController::class, 'logout'], $admin);
    $router->get('/admin/conta', [AdminAuthController::class, 'senhaForm'], $admin);
    $router->post('/admin/conta', [AdminAuthController::class, 'senha'], $admin);

    $router->get('/admin', [DashboardController::class, 'index'], $admin);

    $router->get('/admin/produtos', [AdminProdutoController::class, 'index'], $admin);
    $router->get('/admin/produtos/novo', [AdminProdutoController::class, 'create'], $admin);
    $router->post('/admin/produtos', [AdminProdutoController::class, 'store'], $admin);
    $router->get('/admin/produtos/{id}/editar', [AdminProdutoController::class, 'edit'], $admin);
    $router->post('/admin/produtos/{id}', [AdminProdutoController::class, 'update'], $admin);
    $router->post('/admin/produtos/{id}/excluir', [AdminProdutoController::class, 'destroy'], $admin);

    $router->get('/admin/pedidos', [AdminPedidoController::class, 'index'], $admin);
    $router->get('/admin/pedidos/{id}', [AdminPedidoController::class, 'show'], $admin);
    $router->post('/admin/pedidos/{id}/status', [AdminPedidoController::class, 'status'], $admin);

    $router->get('/admin/notificacoes', [AdminNotificacaoController::class, 'index'], $admin);
    $router->post('/admin/notificacoes/lidas', [AdminNotificacaoController::class, 'todas'], $admin);
    $router->post('/admin/notificacoes/{id}/lida', [AdminNotificacaoController::class, 'lida'], $admin);

    $router->get('/admin/depoimentos', [AdminDepoimentoController::class, 'index'], $admin);
    $router->post('/admin/depoimentos/{id}', [AdminDepoimentoController::class, 'update'], $admin);

    $router->get('/admin/curso', [AdminCursoController::class, 'edit'], $admin);
    $router->post('/admin/curso', [AdminCursoController::class, 'update'], $admin);

    $router->get('/admin/contato', [AdminContatoController::class, 'index'], $admin);
    $router->post('/admin/contato/{id}/lida', [AdminContatoController::class, 'lida'], $admin);

    $router->get('/admin/lgpd', [AdminLgpdController::class, 'index'], $admin);
    $router->post('/admin/lgpd/{id}/atender', [AdminLgpdController::class, 'atender'], $admin);

    $router->get('/admin/pagamento', [AdminFinanceiroController::class, 'edit'], $admin);
    $router->post('/admin/pagamento', [AdminFinanceiroController::class, 'update'], $admin);
    $router->post('/admin/despacho', [AdminFinanceiroController::class, 'despacho'], $admin);
    $router->post('/admin/pix', [AdminFinanceiroController::class, 'pix'], $admin);
    $router->post('/admin/loja', [AdminFinanceiroController::class, 'loja'], $admin);

    $router->get('/admin/logs', [LogController::class, 'index'], $admin);
};
