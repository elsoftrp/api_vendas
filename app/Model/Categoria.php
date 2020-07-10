<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $primaryKey = 'codcategoria';
    protected $table = 'categorias';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = array('codcategoria', 'descricao', 'cnae');

    public function empresa()
    {
        return $this->hasMany(Empresa::class, 'codcategoria', 'codcategoria');
    }
}
