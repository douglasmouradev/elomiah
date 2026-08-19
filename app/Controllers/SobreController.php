<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

final class SobreController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $this->view('sobre/index', [
            'title' => 'Sobre a Elomiah e a Geo',
        ]);
    }
}
