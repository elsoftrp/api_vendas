<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $fillable = [
        'descricao'
    ];

    public function busca($pesquisa = null, $order = null, $direct = 'asc')
    {

        $orderBy = $this->fieldOrder($order);
        $results = $this->where(function ($query) use ($pesquisa)
        {
            if ($pesquisa)
            {
                $query->whereRaw('lower(descricao) LIKE ?', [strtolower($pesquisa).'%']);
            }
        })->orderBy($orderBy, $direct)
        ->paginate(10);
        return $results;
    }

    public function fieldOrder($value)
    {
        $fields = array('id' => 'id' ,'descricao' => 'descricao');
        if (!empty($fields[$value]))
            return $fields[$value];
        else
            return $fields['descricao'];
    }

    public function produto()
    {
        return $this->hasMany(Produto::class, 'grupo_id','id');
    }
}
