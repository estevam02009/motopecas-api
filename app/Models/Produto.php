<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'codigo_produto',
        'preco',
        'estoque',
        'marca',
        'modelo_moto',
        'ano_fabricacao',
        'imagens',
        'categoria_id',
        'ativo'
    ];

    // Relacionamento: Um produto pertence a uma categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relacionamento: Um produto pode estar em muitos pedidos (muitos-para-muitos)
    public function pedidoItems()
    {
        return $this->hasMany(PedidoItem::class);
    }

    // Scopes para filtrar produtos ativos
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    // Scopes para produtos com estoque
    public function scopeComEstoque($query)
    {
        return $query->where('estoque', '>', 0);
    }
}
