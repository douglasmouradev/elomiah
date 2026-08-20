<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Endereco;
use App\Models\Pedido;
use App\Models\Usuario;

final class ContaController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $this->view('conta/index', [
            'title' => 'Minha conta — Elomiah',
            'pedidos' => Pedido::doCliente((int) Auth::id()),
            'usuario' => Auth::user(),
            'endereco' => Endereco::ultimoDoCliente((int) Auth::id()),
            'cursoAcesso' => \App\Models\Curso::clienteTemAcesso((int) Auth::id()),
            'cursoUrl' => \App\Models\Curso::urlAcesso(),
        ]);
    }

    public function endereco(Request $request, array $params = []): never
    {
        $data = Validator::sanitize($request->all());
        $errors = Validator::make($data, [
            'cep' => 'required|cep',
            'logradouro' => 'required|max:180',
            'numero' => 'required|max:20',
            'bairro' => 'required|max:120',
            'cidade' => 'required|max:120',
            'estado' => 'required|max:2',
        ]);

        if ($errors) {
            Session::setFlash('error', implode(' ', $errors));
            redirect('/conta');
        }

        Endereco::salvarDoCliente((int) Auth::id(), $data);
        Session::setFlash('success', 'Endereço guardado nesta conta. O checkout já o usa.');
        redirect('/conta');
    }

    public function perfil(Request $request, array $params = []): never
    {
        $data = Validator::sanitize($request->all());
        $errors = Validator::make($data, [
            'nome' => 'required|min:3|max:120',
            'telefone' => 'required|min:10|max:20',
        ]);
        if ($errors) {
            Session::setFlash('error', implode(' ', $errors));
            redirect('/conta');
        }

        Usuario::updateById((int) Auth::id(), [
            'nome' => $data['nome'],
            'telefone' => preg_replace('/\D+/', '', (string) $data['telefone']),
        ]);
        Session::setFlash('success', 'Seus dados foram atualizados.');
        redirect('/conta');
    }

    public function senha(Request $request, array $params = []): never
    {
        $atual = (string) $request->input('senha_atual');
        $nova = (string) $request->input('senha');
        $conf = (string) $request->input('senha_confirmation');
        $user = Auth::user();

        if (!$user || !password_verify($atual, (string) ($user['senha_hash'] ?? ''))) {
            Session::setFlash('error', 'A senha atual não confere.');
            redirect('/conta');
        }
        if (mb_strlen($nova) < 8 || $nova !== $conf) {
            Session::setFlash('error', 'A senha nova precisa ter ao menos 8 caracteres e coincidir com a confirmação.');
            redirect('/conta');
        }

        Usuario::updateById((int) Auth::id(), [
            'senha_hash' => password_hash($nova, PASSWORD_DEFAULT),
        ]);
        Session::setFlash('success', 'Senha atualizada.');
        redirect('/conta');
    }

    public function pedido(Request $request, array $params = []): never
    {
        $codigo = (string) ($params['codigo'] ?? '');
        $pedido = Pedido::doClientePorCodigo((int) Auth::id(), $codigo);
        if (!$pedido) {
            Response::abort(404, 'Pedido não encontrado nesta conta.');
        }

        $this->view('conta/pedido', [
            'title' => 'Pedido ' . $pedido['codigo'] . ' — Elomiah',
            'pedido' => $pedido,
            'reciboLiberado' => \App\Support\NotaCompra::liberada($pedido),
            'cursoUrl' => (in_array((string) ($pedido['status'] ?? ''), ['pago', 'enviado', 'entregue'], true) && pedido_tem_formacao($pedido))
                ? \App\Models\Curso::urlAcesso()
                : '',
        ]);
    }

    public function cancelar(Request $request, array $params = []): never
    {
        $codigo = (string) ($params['codigo'] ?? '');
        $pedido = Pedido::doClientePorCodigo((int) Auth::id(), $codigo);
        if (!$pedido) {
            Response::abort(404, 'Pedido não encontrado nesta conta.');
        }
        if (($pedido['status'] ?? '') !== 'pendente') {
            Session::setFlash('error', 'Só é possível cancelar enquanto o pagamento estiver aberto.');
            redirect('/conta/pedidos/' . $codigo);
        }

        Pedido::mudarStatus((int) $pedido['id'], 'cancelado');
        \App\Models\Notificacao::criar(
            'pedido_cancelado',
            'Pedido cancelado ' . $codigo,
            (string) (Auth::user()['nome'] ?? 'Cliente') . ' desistiu do pagamento.',
            '/admin/pedidos/' . (int) $pedido['id'],
            (int) $pedido['id']
        );
        Session::setFlash('success', 'Pedido cancelado. As peças voltam ao ateliê.');
        redirect('/conta');
    }

    public function curso(Request $request, array $params = []): never
    {
        if (!\App\Models\Curso::clienteTemAcesso((int) Auth::id())) {
            Session::setFlash('error', 'A formação libera nesta conta depois do pagamento.');
            redirect('/conta');
        }
        $url = \App\Models\Curso::urlAcesso();
        if ($url === '') {
            Session::setFlash('success', 'O pagamento entrou. A Geo ainda está colocando o caminho das aulas — avisamos por e-mail.');
            redirect('/conta');
        }
        redirect($url);
    }
}
