<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'nome',
        'descricao',
        'slug',
        'ativo',
    ];

    protected $cast = [
        'ativo' => 'boolean',
    ];

    // Relacionamento: uma categoria tem muitos produtos
    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    // Automaticamente criar slug ao salvar
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nome);
            }
        });
    }
