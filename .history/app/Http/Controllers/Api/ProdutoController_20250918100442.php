<?php
// app/Http/Controllers/Api/ProdutoController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ProdutoController extends Controller
{
    /**
     * Listar produtos com filtros
     * GET /api/produtos
     */
    public function index(Request $request)
    {
        try {
            $query = Produto::with('categoria')->ativo();

            // Filtros opcionais
            if ($request->has('categoria_id')) {
                $query->where('categoria_id', $request->categoria_id);
            }

            if ($request->has('marca')) {
                $query->where('marca', 'like', '%' . $request->marca . '%');
            }

            if ($request->has('modelo_moto')) {
                $query->where('modelo_moto', 'like', '%' . $request->modelo_moto . '%');
            }

            if ($request->has('com_estoque') && $request->com_estoque) {
                $query->comEstoque();
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('descricao', 'like', "%{$search}%")
                      ->orWhere('codigo_produto', 'like', "%{$search}%");
                });
            }

            // Paginação
            $perPage = $request->get('per_page', 15);
            $produtos = $query->orderBy('nome')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $produtos,
                'message' => 'Produtos recuperados com sucesso'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar produtos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Criar novo produto
     * POST /api/produtos
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'codigo_produto' => 'required|string|unique:produtos',
                'preco' => 'required|numeric|min:0',
                'estoque' => 'required|integer|min:0',
                'marca' => 'nullable|string|max:100',
                'modelo_moto' => 'nullable|string|max:100',
                'ano_fabricacao' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'imagens' => 'nullable|array',
                'imagens.*' => 'string|url',
                'categoria_id' => 'required|exists:categorias,id',
                'ativo' => 'boolean'
            ]);

            $produto = Produto::create($validated);

            return response()->json([
                'success' => true,
                'data' => $produto->load('categoria'),
                'message' => 'Produto criado com sucesso'
            ], Response::HTTP_CREATED);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar produto',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Visualizar produto específico
     * GET /api/produtos/{id}
     */
    public function show($id)
    {
        try {
            $produto = Produto::with('categoria')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $produto,
                'message' => 'Produto encontrado'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Atualizar produto
     * PUT /api/produtos/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $produto = Produto::findOrFail($id);

            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'codigo_produto' => ['required', 'string', Rule::unique('produtos')->ignore($id)],
                'preco' => 'required|numeric|min:0',
                'estoque' => 'required|integer|min:0',
                'marca' => 'nullable|string|max:100',
                'modelo_moto' => 'nullable|string|max:100',
                'ano_fabricacao' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'imagens' => 'nullable|array',
                'imagens.*' => 'string|url',
                'categoria_id' => 'required|exists:categorias,id',
                'ativo' => 'boolean'
            ]);

            $produto->update($validated);

            return response()->json([
                'success' => true,
                'data' => $produto->load('categoria'),
                'message' => 'Produto atualizado com sucesso'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado'
            ], Response::HTTP_NOT_FOUND);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Deletar produto
     * DELETE /api/produtos/{id}
     */
    public function destroy($id)
    {
        try {
            $produto = Produto::findOrFail($id);

            // Verificar se há pedidos associados
            if ($produto->pedidoItens()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir produto que já foi vendido'
                ], Response::HTTP_CONFLICT);
            }

            $produto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produto excluído com sucesso'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Atualizar estoque do produto
     * PATCH /api/produtos/{id}/estoque
     */
    public function updateEstoque(Request $request, $id)
    {
        try {
            $produto = Produto::findOrFail($id);

            $validated = $request->validate([
                'estoque' => 'required|integer|min:0'
            ]);

            $produto->update(['estoque' => $validated['estoque']]);

            return response()->json([
                'success' => true,
                'data' => $produto,
                'message' => 'Estoque atualizado com sucesso'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado'
            ], Response::HTTP_NOT_FOUND);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
