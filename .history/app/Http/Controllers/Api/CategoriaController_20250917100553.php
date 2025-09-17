<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Categoria;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categorias = Categoria::with('produtos')
                ->orderBy('ativo', true)
                ->orderBy('nome')
                ->get();

                return response()->json([
                    'success' => true,
                    'data' => $categorias,
                    'message' => 'Categorias listadas com sucesso',
                ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar categorias',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $requets->validate([
                'nome' => 'required|string|max:255\unique:categorias',
                'descricao' => 'nullable|string',
                'ativo' => 'boolean',
            ]);

            $categoria = Categoria::create($validated);

            return response()->json([
                'success' => true,
                'data' => $categoria,
                'message' => 'Categoria criada com sucesso',
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar categoria',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
