<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->decimal('valor_total', 10, 2);
            $table->decimal('valor_frete', 8, 2)->default(0);
            $table->enum('status', ['pendente', 'confirmado', 'preparando', 'enviado', 'entregue', 'cancelado'])->default('pendente');
            $table->enum('forma_pagamento', ['pix', 'cartao_credito', 'cartao_debito', 'boleto'])->nullable();
            $table->json('endereco_entrega');
            $table->text('observacoes')->nullable();
            $table->timestamp('data_entrega')->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'status']);
            $table->index('numero_pedido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
