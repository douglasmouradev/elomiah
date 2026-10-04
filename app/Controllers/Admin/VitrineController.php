<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Configuracao;
use App\Models\Newsletter;
use App\Support\MercadoPago;

final class VitrineController extends Controller
{
    public function edit(Request $request, array $params = []): never
    {
        $this->view('admin/vitrine', [
            'title' => 'Vitrine da loja',
            'vitrine' => Configuracao::vitrine(),
            'faq' => faq_loja(),
            'mpPronto' => MercadoPago::configurado(),
            'inscritos' => Newsletter::todos(),
        ], 'layouts/admin');
    }

    public function update(Request $request, array $params = []): never
    {
        Configuracao::setVitrine($request->all());
        Auth::log('Atualizou a vitrine da loja', 'configuracoes', null);
        Session::setFlash('success', 'Vitrine atualizada. O site já mostra as mudanças.');
        redirect('/admin/vitrine');
    }

    public function exportar(Request $request, array $params = []): never
    {
        Auth::log('Exportou a lista da newsletter', 'newsletter_inscritos', null);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="newsletter-elomiah-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'wb');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['email', 'inscrito_em'], ';');
        foreach (Newsletter::todos() as $i) {
            fputcsv($out, [$i['email'], $i['created_at']], ';');
        }
        exit;
    }

    public function remover(Request $request, array $params = []): never
    {
        Newsletter::remover((int) ($params['id'] ?? 0));
        Auth::log('Removeu um e-mail da newsletter', 'newsletter_inscritos', (int) ($params['id'] ?? 0));
        Session::setFlash('success', 'E-mail removido da lista.');
        redirect('/admin/vitrine#newsletter');
    }
}
