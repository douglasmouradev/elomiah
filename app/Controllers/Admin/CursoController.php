<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Curso;
use App\Models\FaqCurso;
use App\Models\ModuloCurso;

final class CursoController extends Controller
{
    public function edit(Request $request, array $params = []): never
    {
        $curso = Curso::firstWhere('status', 'ativo') ?? Curso::all()[0] ?? null;
        $this->view('admin/curso/form', [
            'title' => 'Curso — Ateliê',
            'curso' => $curso,
            'modulos' => $curso ? ModuloCurso::where('curso_id', (int) $curso['id'], 'ordem ASC') : [],
            'faqs' => $curso ? FaqCurso::where('curso_id', (int) $curso['id'], 'ordem ASC') : [],
        ], 'layouts/admin');
    }

    public function update(Request $request, array $params = []): never
    {
        $data = Validator::sanitize($request->all());
        $curso = Curso::find((int) ($data['id'] ?? 0));
        if (!$curso) {
            Session::setFlash('error', 'Curso não encontrado.');
            redirect('/admin/curso');
        }

        Curso::updateById((int) $curso['id'], [
            'titulo' => $data['titulo'] ?? $curso['titulo'],
            'descricao' => $data['descricao'] ?? '',
            'preco' => (float) str_replace(',', '.', (string) ($data['preco'] ?? $curso['preco'])),
            'status' => in_array($data['status'] ?? '', ['ativo', 'inativo'], true) ? $data['status'] : 'ativo',
        ]);

        $titulos = $data['modulo_titulo'] ?? [];
        $descs = $data['modulo_desc'] ?? [];
        $ids = $data['modulo_id'] ?? [];
        $duracoes = $data['modulo_duracao'] ?? [];
        if (is_array($titulos)) {
            foreach ($titulos as $i => $titulo) {
                $mid = (int) ($ids[$i] ?? 0);
                $payload = [
                    'titulo' => $titulo,
                    'descricao' => $descs[$i] ?? '',
                    'duracao' => $duracoes[$i] ?? '',
                    'ordem' => $i + 1,
                    'curso_id' => (int) $curso['id'],
                ];
                if ($mid) {
                    ModuloCurso::updateById($mid, $payload);
                } elseif (trim((string) $titulo) !== '') {
                    ModuloCurso::create($payload);
                }
            }
        }

        Auth::log('Atualizou curso', 'cursos', (int) $curso['id']);
        Session::setFlash('success', 'Página do curso atualizada.');
        redirect('/admin/curso');
    }
}
