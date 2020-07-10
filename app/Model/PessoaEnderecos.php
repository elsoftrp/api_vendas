<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PessoaEnderecos extends Model
{
    protected $table = 'pessoa_enderecos';
    protected $fillable = [
        'pessoa_id',
        'descricao',
        'principal',
        'inativo',
        'inativodt',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade_id',
    ];

    public function cidade()
    {
        return $this->hasOne(Cidade::class, 'id','cidade_id');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
