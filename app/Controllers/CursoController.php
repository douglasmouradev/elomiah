<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\Curso;
use App\Models\FaqCurso;
use App\Models\ModuloCurso;

final class CursoController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $curso = Curso::firstWhere('status', 'ativo');
        if ($curso) {
            Curso::garantirProduto($curso);
        }
        $modulos = $curso ? ModuloCurso::where('curso_id', (int) $curso['id'], 'ordem ASC') : [];
        $faqs = $curso ? FaqCurso::where('curso_id', (int) $curso['id'], 'ordem ASC') : [];

        $this->view('curso/index', [
            'title' => ($curso['titulo'] ?? 'Curso') . ' — Elomiah',
            'curso' => $curso,
            'modulos' => $modulos,
            'faqs' => $faqs,
            'depoimentos' => Database::fetchAll(
                'SELECT * FROM depoimentos WHERE status = :s ORDER BY id DESC LIMIT 4',
                ['s' => 'aprovado']
            ),
        ]);
    }

    public function matricular(Request $request, array $params = []): never
    {
        $curso = Curso::firstWhere('status', 'ativo');
        if (!$curso) {
            Session::setFlash('error', 'O curso não está disponível no momento.');
            redirect('/curso');
        }

        $produto = Curso::garantirProduto($curso);
        if (!$produto || ($produto['status'] ?? '') !== 'ativo') {
            Session::setFlash('error', 'A matrícula não está aberta neste momento.');
            redirect('/curso');
        }

        Cart::update((int) $produto['id'], 1);
        Session::setFlash('success', $produto['nome'] . ' entrou na sacola. Conclua o pagamento para confirmar a matrícula.');
        Session::set('curso_matricula', (int) $curso['id']);
        redirect('/checkout');
    }
}
