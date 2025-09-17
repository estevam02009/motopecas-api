<?php
// app/Http/Controllers/Api/CategoriaController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    /**
     * Listar todas as categorias
     * GET /api/categorias
     */
    public function index()
    {
        try {
            $categorias = Categoria::with('produtos')
                ->where('ativo', true)
                ->orderBy('nome')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categorias,
                'message' => 'Categorias recuperadas com sucesso'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar categorias',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Criar nova categoria
     * POST /api/categorias
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255|unique:categorias',
                'descricao' => 'nullable|string',
                'ativo' => 'boolean'
            ]);

            $categoria = Categoria::create($validated);

            return response()->json([
                'success' => true,
                'data' => $categoria,
                'message' => 'Categoria criada com sucesso'
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
                'message' => 'Erro ao criar categoria',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Visualizar categoria específica
     * GET /api/categorias/{id}
     */
    public function show($id)
    {
        try {
            $categoria = Categoria::with('produtos')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $categoria,
                'message' => 'Categoria encontrada'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Categoria não encontrada'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Atualizar categoria
     * PUT /api/categorias/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);

            $validated = $request->validate([
                'nome' => ['required', 'string', 'max:255', Rule::unique('categorias')->ignore($id)],
                'descricao' => 'nullable|string',
                'ativo' => 'boolean'
            ]);

            $categoria->update($validated);

            return response()->json([
                'success' => true,
                'data' => $categoria,
                'message' => 'Categoria atualizada com sucesso'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Categoria não encontrada'
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
     * Deletar categoria
     * DELETE /api/categorias/{id}
     */
    public function destroy($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);

            // Verificar se há produtos associados
            if ($categoria->produtos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir categoria com produtos associados'
                ], Response::HTTP_CONFLICT);
            }

            $categoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoria excluída com sucesso'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Categoria não encontrada'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
