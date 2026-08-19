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
use App\Models\Produto;

final class CursoController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $curso = Curso::firstWhere('status', 'ativo');
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

        $kit = Produto::firstWhere('slug', 'kit-ritual-matinal');
        if ($kit) {
            Cart::add((int) $kit['id'], 1);
        }
        Session::setFlash('success', 'A matrícula será confirmada no checkout. O kit ritual acompanha o início — ajuste o carrinho se preferir apenas o curso.');
        Session::set('curso_matricula', (int) $curso['id']);
        redirect('/checkout');
    }
}
