<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{

    protected $table = 'cidades';
    public $timestamps = false;
    protected $fillable = ['cidade', 'uf'];

    public function empresa()
    {
        return $this->hasMany(Empresa::class,'cidade_id','id');
    }
}
