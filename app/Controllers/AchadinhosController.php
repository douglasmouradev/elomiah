<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Produto;

final class AchadinhosController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $this->view('achadinhos/index', [
            'title' => 'Achadinhos da Geo — Elomiah',
            'produtos' => Produto::ativos(['achadinho' => 1]),
        ]);
    }
}
