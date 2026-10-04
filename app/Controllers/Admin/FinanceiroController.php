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
            'frete' => Configuracao::frete(),
            'pix' => Configuracao::pix(),
            'loja' => Configuracao::loja(),
            'vitrine' => Configuracao::vitrine(),
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

    public function despacho(Request $request, array $params = []): never
    {
        $valor = (float) str_replace(',', '.', (string) $request->input('frete_padrao', '18.90'));
        $nome = (string) $request->input('frete_nome', '');
        $prazo = (string) $request->input('frete_prazo', '');
        Configuracao::setFrete($valor, $nome, $prazo);
        Auth::log('Atualizou o despacho', 'configuracoes', null);
        Session::setFlash('success', 'Despacho atualizado. O checkout já usa este valor.');
        redirect('/admin/pagamento');
    }

    public function vitrine(Request $request, array $params = []): never
    {
        Configuracao::setVitrine(
            (float) str_replace(',', '.', (string) $request->input('frete_gratis_acima', '0')),
            (string) $request->input('faixa_avisos', ''),
            (string) $request->input('atendimento_horario', '')
        );
        Auth::log('Atualizou a vitrine da loja', 'configuracoes', null);
        Session::setFlash('success', 'Vitrine atualizada. O site já mostra as mudanças.');
        redirect('/admin/pagamento');
    }

    public function pix(Request $request, array $params = []): never
    {
        Configuracao::setPix(
            (string) $request->input('pix_chave', ''),
            (string) $request->input('pix_nome', ''),
            (string) $request->input('pix_cidade', '')
        );
        Auth::log('Atualizou a chave Pix', 'configuracoes', null);
        Session::setFlash('success', 'Pix atualizado. O checkout já usa esta chave.');
        redirect('/admin/pagamento');
    }

    public function loja(Request $request, array $params = []): never
    {
        Configuracao::setLoja(
            (string) $request->input('loja_razao', ''),
            (string) $request->input('loja_cnpj', ''),
            (string) $request->input('loja_ie', ''),
            (string) $request->input('loja_endereco', '')
        );
        Auth::log('Atualizou os dados do recibo', 'configuracoes', null);
        Session::setFlash('success', 'Dados do emitente atualizados. O recibo já os usa.');
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
