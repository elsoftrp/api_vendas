<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';
    protected $fillable = array(
        'nome',
        'cnpjcpf',
        'insrg',
        'insmunicipal',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade_id',
        'telefone',
        'celular',
        'inativo',
        'inativodt',
        'email',
        'obs'
    );

    public function busca($pesquisa = null, $order = null, $direct = 'asc')
    {

        $orderBy = $this->fieldOrder($order);
        $results = $this->where(function ($query) use ($pesquisa)
        {
            if ($pesquisa)
            {
                $query->whereRaw('lower(empresas.nome) LIKE ? ', [ strtolower($pesquisa).'%'])
                ->orWhere('empresas.cnpjcpf','LIKE','%'.$pesquisa.'%');
            } else
            {
                $query->where('inativo',false);
            }
        })->leftJoin('cidades', 'cidades.id','=','empresas.cidade_id')
        ->select('empresas.id', 'empresas.nome','empresas.cnpjcpf','empresas.email','empresas.telefone','empresas.celular','empresas.endereco','empresas.inativo','cidades.cidade')
        ->orderBy($orderBy, $direct)
        ->paginate(10);
        //->take(15)->get();
        return $results;
    }

    public function cidade()
    {
        return $this->hasOne(Cidade::class, 'id', 'cidade_id');

    }

    public function categoria()
    {
        return $this->hasOne(Categoria::class, 'codcategoria', 'codcategoria');

    }

    public function escritorio()
    {
        return $this->hasOne(Escritorio::class, 'codescritorio', 'codescritorio');

    }

    public function fieldOrder($value)
    {
        $fields = array('id' => 'empresas.id'
        ,'nome' => 'empresas.nome'
        ,'cnpjcpf' => 'empresas.cnpjcpf'
        ,'email' => 'empresas.email'
        ,'telefone' => 'empresas.telefone'
        ,'celular' => 'empresas.celular'
        ,'cidade' => 'cidades.cidade'
        ,'inativo' => 'empresas.inativo');
        if (!empty($fields[$value]))
            return $fields[$value];
        else
            return $fields['nome'];
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



    /*public function setDataCadAttribute($value)
    {
        $this->attributes['DataCad'] = $this->convertStringToDate($value);
    }*/

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

    private function clearField(?string $param)
    {
        if(empty($param)){
            return '';
        }

        return str_replace(['.', '-', '/', '(', ')', ' '], '', $param);
    }
}
