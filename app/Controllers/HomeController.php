<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Curso;
use App\Models\Depoimento;
use App\Models\Produto;

final class HomeController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $this->view('home/index', [
            'title' => 'Elomiah — Onde o sagrado encontra a essência',
            'produtos' => Produto::ativos(['destaque' => 1]),
            'depoimentos' => Depoimento::where('status', 'aprovado', 'id DESC'),
            'curso' => Curso::firstWhere('status', 'ativo'),
        ]);
    }
}
