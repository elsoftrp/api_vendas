<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $table = 'produtos';
    protected $fillable = [
        'despro',
        'empresa_id',
        'grupo_id',
        'unidade_id',
        'ean',
        'despro',
        'prcusto',
        'plucro',
        'prvenda',
        'estoquep',
        'ultprcompra',
        'ultprvenda',
        'dtvenda',
        'dtcompra',
        'inativo',
        'inativodt'
    ];

    public function busca($pesquisa = null, $order = null, $direct = 'asc')
    {

        $orderBy = $this->fieldOrder($order);
        $results = $this->where(function ($query) use ($pesquisa)
        {
            if ($pesquisa)
            {
                $query->whereRaw('lower(despro) LIKE ?', [strtolower($pesquisa).'%']);
            }
        })->leftJoin('grupos', 'grupos.id','=','produtos.grupo_id')
        ->leftJoin('unidades','unidades.id','=','produtos.unidade_id')
        ->select('produtos.id', 'despro','ean','unidades.unidade','grupos.descricao','prvenda','estoquep')
        ->orderBy($orderBy, $direct)
        ->paginate(10);
        return $results;
    }

    public function fieldOrder($value)
    {
        $fields = array('id' => 'id' ,'despro' => 'despro');
        if (!empty($fields[$value]))
            return $fields[$value];
        else
            return $fields['despro'];
    }

    public function grupo()
    {
        return $this->hasOne(Grupo::class, 'id','grupo_id');
    }

    public function unidade()
    {
        return $this->hasOne(Unidade::class, 'id','unidade_id');
    }

    public function getCreatedAtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
    }

    public function setInativodtAttribute($value)
    {
        $this->attributes['inativodt'] = $this->convertStringToDate($value);
    }

    public function getInativodtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
    }

    private function convertStringToDate(?string $param)
    {
        if(empty($param)){
            return null;
        }

        list($day, $month, $year) = explode('/', $param);
        return (new \DateTime($year . '-' . $month . '-' . $day))->format('Y-m-d');
    }
}
