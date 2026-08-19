<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Configuracao;
use App\Support\MercadoPago;

final class FinanceiroController extends Controller
{
    public function edit(Request $request, array $params = []): never
    {
        $token = trim((string) Configuracao::get('mercadopago_access_token', ''));
        $public = trim((string) Configuracao::get('mercadopago_public_key', ''));

        $this->view('admin/financeiro', [
            'title' => 'Pagamento — Ateliê',
            'temToken' => MercadoPago::configurado(),
            'temPublica' => MercadoPago::chavePublica() !== '',
            'tokenMascara' => self::mascara($token),
            'publicaMascara' => self::mascara($public),
        ], 'layouts/admin');
    }

    public function update(Request $request, array $params = []): never
    {
        $token = trim((string) $request->input('mercadopago_access_token', ''));
        $public = trim((string) $request->input('mercadopago_public_key', ''));

        if ($request->input('limpar_mp') === '1') {
            Configuracao::set('mercadopago_access_token', '');
            Configuracao::set('mercadopago_public_key', '');
        } else {
            if ($token !== '') {
                Configuracao::set('mercadopago_access_token', $token);
            }
            if ($public !== '') {
                Configuracao::set('mercadopago_public_key', $public);
            }
        }

        Auth::log('Atualizou credenciais de pagamento', 'configuracoes', null);
        Session::setFlash('success', 'Pagamento atualizado. Pix já cobra na página; o cartão confirma quando o Mercado Pago estiver ligado.');
        redirect('/admin/pagamento');
    }

    private static function mascara(string $valor): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return '';
        }
        $tail = substr($valor, -4);

        return '•••• ' . $tail;
    }
}
