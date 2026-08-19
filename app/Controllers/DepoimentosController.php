<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Depoimento;

final class DepoimentosController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $this->view('depoimentos/index', [
            'title' => 'Depoimentos — Elomiah',
            'depoimentos' => Depoimento::where('status', 'aprovado', 'id DESC'),
        ]);
    }

    public function store(Request $request, array $params = []): never
    {
        if (RateLimiter::tooMany('depoimento')) {
            Session::setFlash('error', 'Aguarde um pouco antes de enviar outro depoimento.');
            redirect('/depoimentos');
        }

        $data = Validator::sanitize($request->all());
        $errors = Validator::make($data, [
            'nome' => 'required|min:2|max:120',
            'texto' => 'required|min:20|max:800',
            'nota' => 'required|in:1,2,3,4,5',
        ]);

        if ($errors) {
            Session::set('_old', $data);
            Session::setFlash('error', implode(' ', $errors));
            redirect('/depoimentos');
        }

        $foto = null;
        if (!empty($_FILES['foto']['name'])) {
            $foto = Upload::one($_FILES['foto'], 'depoimentos');
        }

        Depoimento::create([
            'usuario_id' => Auth::id(),
            'nome' => $data['nome'],
            'texto' => $data['texto'],
            'nota' => (int) $data['nota'],
            'foto' => $foto,
            'status' => 'pendente',
        ]);

        RateLimiter::hit('depoimento');
        Session::setFlash('success', 'Obrigada. Seu relato entra no refúgio após uma leitura atenta.');
        redirect('/depoimentos');
    }
}
