<?php
// app/Http/Controllers/Api/ClienteController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    /**
     * Listar clientes
     * GET /api/clientes
     */
    public function index(Request $request)
    {
        try {
            $query = Cliente::where('ativo', true);

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('cpf', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 15);
            $clientes = $query->orderBy('nome')->paginate($perPage);

            // Remover dados sensíveis da resposta
            $clientes->getCollection()->transform(function($cliente) {
                unset($cliente->password);
                return $cliente;
            });

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'message' => 'Clientes recuperados com sucesso'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar clientes',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Criar novo cliente
     * POST /api/clientes
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'email' => 'required|email|unique:clientes',
                'password' => 'required|string|min:8',
                'telefone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|size:11|unique:clientes',
                'data_nascimento' => 'nullable|date|before:today',
                'endereco' => 'nullable|array',
                'endereco.cep' => 'nullable|string|size:8',
                'endereco.logradouro' => 'nullable|string|max:255',
                'endereco.numero' => 'nullable|string|max:20',
                'endereco.complemento' => 'nullable|string|max:100',
                'endereco.bairro' => 'nullable|string|max:100',
                'endereco.cidade' => 'nullable|string|max:100',
                'endereco.estado' => 'nullable|string|size:2',
                'ativo' => 'boolean'
            ]);

            // Hash da senha
            $validated['password'] = Hash::make($validated['password']);

            $cliente = Cliente::create($validated);

            // Remover senha da resposta
            unset($cliente->password);

            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente criado com sucesso'
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
                'message' => 'Erro ao criar cliente',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Visualizar cliente específico
     * GET /api/clientes/{id}
     */
    public function show($id)
    {
        try {
            $cliente = Cliente::with('pedidos')->findOrFail($id);
            unset($cliente->password);

            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente encontrado'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Atualizar cliente
     * PUT /api/clientes/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('clientes')->ignore($id)],
                'telefone' => 'nullable|string|max:20',
                'cpf' => ['nullable', 'string', 'size:11', Rule::unique('clientes')->ignore($id)],
                'data_nascimento' => 'nullable|date|before:today',
                'endereco' => 'nullable|array',
                'endereco.cep' => 'nullable|string|size:8',
                'endereco.logradouro' => 'nullable|string|max:255',
                'endereco.numero' => 'nullable|string|max:20',
                'endereco.complemento' => 'nullable|string|max:100',
                'endereco.bairro' => 'nullable|string|max:100',
                'endereco.cidade' => 'nullable|string|max:100',
                'endereco.estado' => 'nullable|string|size:2',
                'ativo' => 'boolean'
            ]);

            $cliente->update($validated);
            unset($cliente->password);

            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente atualizado com sucesso'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
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
     * Deletar cliente
     * DELETE /api/clientes/{id}
     */
    public function destroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            // Verificar se há pedidos associados
            if ($cliente->pedidos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir cliente com pedidos associados'
                ], Response::HTTP_CONFLICT);
            }

            $cliente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente excluído com sucesso'
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
