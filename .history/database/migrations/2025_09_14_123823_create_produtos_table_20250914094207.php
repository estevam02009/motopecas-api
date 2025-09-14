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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('codigo_produto')->unique();
            $table->decimal('preco', 10, 2);
            $table->integer('estoque')->default(0);
            $table->string('marca')->nullable();
            $table->string('modelo_moto')->nullable();
            $table->string('ano_fabricacao')->nullable();
            $table->json('imagens')->nullable();
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['categoria_id', 'ativo']);
            $table->index(['codigo_produto']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
