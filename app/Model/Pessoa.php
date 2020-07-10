<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{

    protected $table = 'pessoas';
    protected $fillable = [
        'nome',
        'cnpjcpf',
        'insrg',
        'insmunicipal',
        'razaosocial',
        'fantasia',
        'abrevnome',
        'telefone',
        'celular',
        'celular2',
        'email',
        'inativo',
        'inativodt',
        'obs',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade_id',
        'empresa_id'
    ];

    public function busca($pesquisa = null, $order = null, $direct = 'asc')
    {

        $orderBy = $this->fieldOrder($order);
        $results = $this->where(function ($query) use ($pesquisa)
        {
            if ($pesquisa)
            {
                $query->whereRaw('lower(pessoas.nome) LIKE ? ', [strtolower($pesquisa).'%'])
                ->orWhere('pessoas.cnpjcpf','LIKE','%'.$pesquisa.'%');
            } else
            {
                $query->where('pessoas.inativo',false);
            }
        })->leftJoin('cidades', 'cidades.id','=','pessoas.cidade_id')
        ->select('pessoas.id', 'pessoas.nome','pessoas.cnpjcpf','pessoas.email','pessoas.telefone','pessoas.celular','pessoas.inativo','cidades.cidade')
        ->orderBy($orderBy, $direct)
        ->paginate(10);
        return $results;
    }

    public function fieldOrder($value)
    {
        $fields = array('id' => 'pessoas.id'
        ,'nome' => 'pessoas.nome'
        ,'cnpjcpf' => 'pessoas.cnpjcpf'
        ,'email' => 'pessoas.email'
        ,'telefone' => 'pessoas.telefone'
        ,'celular' => 'pessoas.celular'
        ,'cidade' => 'cidades.cidade'
        ,'inativo' => 'pessoas.inativo');
        if (!empty($fields[$value]))
            return $fields[$value];
        else
            return $fields['nome'];
    }

    public function enderecos()
    {
        return $this->hasMany(PessoaEnderecos::class, 'pessoa_id');
    }

    public function cidade()
    {
        return $this->hasOne(Cidade::class, 'id','cidade_id');
    }

    public function pessoaTp()
    {
        return $this->belongsToMany(PessoaTp::class, 'pessoa_tp_pessoa', 'pessoa_id','pessoa_tp_id')->withPivot([
            'created_at',
            'updated_at',
        ]);
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

    public function setCnpjcpfAttribute($value)
    {
        $this->attributes['cnpjcpf'] = $this->clearField($value);
    }

    public function getCnpjcpfAttribute($value)
    {
        if (strlen($value)===11)
        {
            return substr($value, 0, 3) . '.' . substr($value, 3, 3) . '.' . substr($value, 6, 3) . '-' . substr($value, 9, 2);
        } else if (strlen($value)==14)
        {
            return substr($value, 0, 2) . '.' . substr($value, 2, 3) . '.' . substr($value, 5, 3) .
            '/' . substr($value, 8, 4) . '-' . substr($value, 12, 2);
        } else
        {
            return $value;
        }
    }

    private function convertStringToDate(?string $param)
    {
        if(empty($param)){
            return null;
        }

        list($day, $month, $year) = explode('/', $param);
        return (new \DateTime($year . '-' . $month . '-' . $day))->format('Y-m-d');
    }

    private function clearField(?string $param)
    {
        if(empty($param)){
            return '';
        }

        return str_replace(['.', '-', '/', '(', ')', ' '], '', $param);
    }

    /*public function busca($pesquisa = null, $order = null, $direct = 'asc')
    {

        $orderBy = $this->fieldOrder($order);
        $results = $this->where(function ($query) use ($pesquisa)
        {
            if ($pesquisa)
            {
                $query->where('pessoas.nome','LIKE', $pesquisa.'%')
                ->orWhere('pessoas.cnpjcpf','LIKE','%'.$pesquisa.'%');
            } else
            {
                $query->where('pessoas.inativo',false);
            }
        })->leftJoin('pessoa_enderecos', function ($join) {
            $join->on('pessoa_enderecos.pessoa_id','=','pessoas.id')
            ->where('pessoa_enderecos.principal',true);
        })
        ->leftJoin('cidades', 'cidades.id','=','pessoa_enderecos.cidade_id')
        ->select('pessoas.id', 'pessoas.nome','pessoas.cnpjcpf','pessoas.email','pessoas.telefone','pessoas.celular','pessoas.inativo','cidades.cidade')
        ->orderBy($orderBy, $direct)
        ->paginate(10);
        //->take(15)->get();
        return $results;
    }*/
}
