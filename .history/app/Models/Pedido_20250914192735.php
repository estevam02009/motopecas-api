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
}
