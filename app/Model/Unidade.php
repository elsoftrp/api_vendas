<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    protected $table = 'unidades';
    protected $fillable = [
        'unidade',
        'desunidade'
    ];

    public function busca($pesquisa = null, $order = null, $direct = 'asc')
    {

        $orderBy = $this->fieldOrder($order);
        $results = $this->where(function ($query) use ($pesquisa)
        {
            if ($pesquisa)
            {
                $query->whereRaw('lower(unidade) LIKE ?', [strtolower($pesquisa).'%']);
            }
        })->orderBy($orderBy, $direct)
        ->paginate(10);
        return $results;
    }

    public function fieldOrder($value)
    {
        $fields = array('id' => 'id' ,'desunidade' => 'desunidade', 'unidade' => 'unidade');
        if (!empty($fields[$value]))
            return $fields[$value];
        else
            return $fields['unidade'];
    }

    public function produto()
    {
        return $this->hasMany(Produto::class, 'unidade_id','id');
    }
}
