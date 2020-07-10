<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PessoaTp extends Model
{
    protected $table = 'pessoa_tp';
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
                $query->whereRaw('lower(pessoa_tp.descricao) LIKE ?', [strtolower($pesquisa).'%']);
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

    public function pessoa()
    {
        return $this->belongsToMany(Pessoa::class, 'pessoa_tp_pessoa', 'pessoa_tp_id','pessoa_id');
    }
}
