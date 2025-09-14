<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Cliente extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'cpf',
        'data_nascimento',
        'endereco',
        'password',
        'ativo'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'data_nascimento' => 'date',
        'endereco' => 'array',
        'ativo' => 'boolean',
    ];

    // Relacionamento: Um cliente pode ter muitos pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}

