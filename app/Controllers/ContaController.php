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
        ]);
    }
}
