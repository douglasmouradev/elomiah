<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Configuracao;
use App\Support\MercadoPago;
use App\Support\Pix;

final class AjudaController extends Controller
{
    public function envios(Request $request, array $params = []): never
    {
        $this->view('ajuda/envios', [
            'title' => 'Envios e frete — Elomiah',
            'frete' => frete(),
            'vitrine' => Configuracao::vitrine(),
        ]);
    }

    public function pagamento(Request $request, array $params = []): never
    {
        $this->view('ajuda/pagamento', [
            'title' => 'Pagamento — Elomiah',
            'cartao' => MercadoPago::configurado(),
            'pix' => MercadoPago::configurado() || Pix::configurado(),
            'vitrine' => Configuracao::vitrine(),
        ]);
    }

    public function trocas(Request $request, array $params = []): never
    {
        $this->view('ajuda/trocas', ['title' => 'Trocas e devoluções — Elomiah']);
    }
}
