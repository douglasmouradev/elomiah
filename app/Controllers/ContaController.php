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
