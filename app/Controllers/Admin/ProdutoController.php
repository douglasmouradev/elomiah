<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Categoria;
use App\Models\ImagemProduto;
use App\Models\Produto;

final class ProdutoController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $produtos = Database::fetchAll(
            'SELECT p.*, c.nome AS categoria_nome,
                    (SELECT caminho FROM imagens_produto i WHERE i.produto_id = p.id ORDER BY ordem, id LIMIT 1) AS imagem
             FROM produtos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             ORDER BY p.id DESC'
        );

        $this->view('admin/produtos/index', [
            'title' => 'Produtos — Ateliê',
            'produtos' => $produtos,
        ], 'layouts/admin');
    }

    public function create(Request $request, array $params = []): never
    {
        $this->formView(null);
    }

    public function store(Request $request, array $params = []): never
    {
        $this->save($request, null);
    }

    public function edit(Request $request, array $params = []): never
    {
        $produto = Produto::find((int) ($params['id'] ?? 0));
        if (!$produto) {
            Response::abort(404, 'Produto não encontrado.');
        }
        $this->formView($produto);
    }

    public function update(Request $request, array $params = []): never
    {
        $produto = Produto::find((int) ($params['id'] ?? 0));
        if (!$produto) {
            Response::abort(404, 'Produto não encontrado.');
        }
        $this->save($request, $produto);
    }

    public function destroy(Request $request, array $params = []): never
    {
        $id = (int) ($params['id'] ?? 0);
        $produto = Produto::find($id);
        if ($produto) {
            Auth::log('Excluiu produto', 'produtos', $id, json_encode($produto, JSON_UNESCAPED_UNICODE));
            Produto::deleteById($id);
        }
        Session::setFlash('success', 'Produto removido.');
        redirect('/admin/produtos');
    }

    private function formView(?array $produto): never
    {
        $this->view('admin/produtos/form', [
            'title' => $produto ? 'Editar produto' : 'Novo produto',
            'produto' => $produto,
            'categorias' => Categoria::all('ordem ASC'),
            'imagens' => $produto ? Produto::imagens((int) $produto['id']) : [],
        ], 'layouts/admin');
    }

    private function save(Request $request, ?array $produto): never
    {
        $data = Validator::sanitize($request->all());
        $errors = Validator::make($data, [
            'nome' => 'required|min:2|max:160',
            'preco' => 'required|numeric',
            'categoria_id' => 'required',
        ]);

        if ($errors) {
            Session::set('_old', $data);
            Session::setFlash('error', implode(' ', $errors));
            redirect($produto ? '/admin/produtos/' . $produto['id'] . '/editar' : '/admin/produtos/novo');
        }

        $slug = slugify((string) (($data['slug'] ?? '') !== '' ? $data['slug'] : $data['nome']));
        $payload = [
            'categoria_id' => (int) $data['categoria_id'],
            'nome' => $data['nome'],
            'slug' => $slug,
            'descricao' => $data['descricao'] ?? '',
            'descricao_curta' => $data['descricao_curta'] ?? '',
            'preco' => (float) str_replace(',', '.', (string) $data['preco']),
            'preco_promocional' => ($data['preco_promocional'] ?? '') === '' ? null : (float) str_replace(',', '.', (string) $data['preco_promocional']),
            'estoque' => (int) ($data['estoque'] ?? 0),
            'sku' => $data['sku'] ?? null,
            'volume' => $data['volume'] ?? null,
            'notas_topo' => $data['notas_topo'] ?? null,
            'notas_coracao' => $data['notas_coracao'] ?? null,
            'notas_fundo' => $data['notas_fundo'] ?? null,
            'ficha_tecnica' => $data['ficha_tecnica'] ?? null,
            'cor_destaque' => preg_match('/^#[0-9A-Fa-f]{6}$/', (string) ($data['cor_destaque'] ?? '')) ? $data['cor_destaque'] : '#1B4332',
            'aroma' => $data['aroma'] ?? null,
            'citacao' => $data['citacao'] ?? null,
            'colecao' => $data['colecao'] ?? 'Coleção Refúgio',
            'modo_usar' => $data['modo_usar'] ?? null,
            'precaucoes' => $data['precaucoes'] ?? null,
            'destaque' => isset($data['destaque']) ? 1 : 0,
            'achadinho_geo' => 0,
            'status' => in_array($data['status'] ?? '', ['ativo', 'inativo'], true) ? $data['status'] : 'ativo',
            'compra_tipo' => in_array($data['compra_tipo'] ?? '', ['carrinho', 'shopee', 'amazon', 'mercadolivre'], true)
                ? $data['compra_tipo'] : 'carrinho',
            'url_shopee' => $data['url_shopee'] ?? null,
            'url_amazon' => $data['url_amazon'] ?? null,
            'url_mercadolivre' => $data['url_mercadolivre'] ?? null,
        ];

        if ($produto) {
            Produto::updateById((int) $produto['id'], $payload);
            $id = (int) $produto['id'];
            Auth::log('Atualizou produto', 'produtos', $id, json_encode($produto, JSON_UNESCAPED_UNICODE), json_encode($payload, JSON_UNESCAPED_UNICODE));
        } else {
            $id = Produto::create($payload);
            Auth::log('Criou produto', 'produtos', $id, null, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        if (!empty($_FILES['imagens']['name'][0])) {
            $paths = Upload::images($_FILES['imagens'], 'produtos');
            $ordem = count(Produto::imagens($id));
            foreach ($paths as $path) {
                ImagemProduto::create([
                    'produto_id' => $id,
                    'caminho' => $path,
                    'alt' => $payload['nome'],
                    'ordem' => $ordem++,
                ]);
            }
        }

        $recusadas = Upload::recusadas();
        if ($recusadas) {
            Session::setFlash('error', 'Produto guardado, mas estas fotos não entraram: ' . implode('; ', $recusadas) . '. Envie de novo pela edição do produto.');
            redirect('/admin/produtos/' . $id . '/editar');
        }
        Session::setFlash('success', 'Produto guardado com cuidado.');
        redirect('/admin/produtos');
    }
}
