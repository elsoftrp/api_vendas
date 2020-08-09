<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PlanoConta extends Model
{
    protected $table = 'plano_contas';
    protected $fillable = [
        'plano_conta_id',
        'desplano',
        'classificacao',
        'tipo'
    ];

    public function busca($pesquisa = null, $order = null, $direct = 'asc')
    {

        $orderBy = $this->fieldOrder($order);
        $results = $this->where(function ($query) use ($pesquisa)
        {
            if ($pesquisa)
            {
                $query->whereRaw('lower(desplano) LIKE ?', [strtolower($pesquisa).'%']);
            }
        })->with('planoContaPai')
        ->orderBy($orderBy, $direct)
        ->paginate(10);
        return $results;
    }

    public function fieldOrder($value)
    {
        $fields = array('id' => 'id' ,'desplano' => 'desplano');
        if (!empty($fields[$value]))
            return $fields[$value];
        else
            return $fields['desplano'];
    }

    public function planoContaPai()
    {
        return $this->hasOne(PlanoConta::class, 'id','plano_conta_id');
    }

    public function planoContaFilhos()
    {
        return $this->hasMany(PlanoConta::class, 'plano_conta_id');
    }
}
