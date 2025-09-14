<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'numero_pedido',
        'cliente_id',
        'valor_total',
        'valor_frete',
        'status',
        'forma_pagamento',
        'endereco_entrega',
        'observacoes',
        'data_entrega'
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_frete' => 'decimal:2',
        'endereco_entrega' => 'array',
        'data_entrega' => 'date',
    ];

    // rtelacionamento: um pedido pertence a um cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // relacionamento: um pedido pode ter muitos itens
    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }

    // Gerar numero do pedido automaticamente ao criar um novo pedido
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pedido) {
            if (empty($pedido->numero_pedido)) {
                $pedido->numero_pedido = 'PED-' . date('Y') . '-' . str_pad(static::max('id') + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
