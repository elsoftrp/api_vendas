<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    protected $fillable = [
        'pedidodt',
        'empresa_id',
        'pessoa_id',
        'user_id',
        'totproduto',
        'desconto',
        'devolucao',
        'total',
        'totpedido',
        'tp_pagto',
        'parcelas',
        'dias_pri',
        'dias_prox',
        'pagto_tp_id',
        'baixado',
        'baixadodt',
        'cancelado',
        'canceladodt'
    ];

    public function busca($pesquisa = null, $order = null, $direct = 'asc', $idEmpresa = null)
    {

        $orderBy = $this->fieldOrder($order);
        $results = $this->where(function ($query) use ($pesquisa, $idEmpresa)
        {
            if ($pesquisa)
            {
                $query->whereRaw('lower(pessoas.nome) LIKE ? ', [strtolower($pesquisa).'%'])
                ->orWhere('pessoas.cnpjcpf','LIKE','%'.$pesquisa.'%');
            } else
            {
                $query->where('pessoas.inativo',false);
            }
            if ($idEmpresa) $query->where('pedidos.empresa_id', $idEmpresa);
            $query->whereNull('pedidos.cancelado');
        })->leftJoin('pessoas', 'pessoas.id','=','pedidos.pessoa_id')
        ->select('pedidos.id', 'pedidos.pedidodt','pedidos.pessoa_id','pessoas.nome','pessoas.cnpjcpf','pedidos.totpedido')
        ->orderBy($orderBy, $direct)
        ->paginate(10);
        return $results;
    }

    public function fieldOrder($value)
    {
        $fields = array('id' => 'pedidos.id'
        ,'pedidodt' => 'pedidos.pedidodt'
        ,'nome' => 'pessoas.nome'
        ,'cnpjcpf' => 'pessoas.cnpjcpf'
        ,'totpedido' => 'pedidos.totpedido'
        );
        if (!empty($fields[$value]))
            return $fields[$value];
        else
            return $fields['pedidodt'];
    }

    public function pedidoItem()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    public function pessoa()
    {
        return $this->hasOne(Pessoa::class, 'id','pessoa_id');
    }

    public function pagtoTp()
    {
        return $this->hasOne(PagtoTp::class, 'id','pagto_tp_id');
    }

    public function empresa()
    {
        return $this->hasOne(Empresa::class, 'id','empresa_id');
    }

    public function getCreatedAtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y H:i', strtotime($value));
        }
        else return null;
    }

    public function setPedidodtAttribute($value)
    {
        $this->attributes['pedidodt'] = $this->convertStringToDate($value);
    }

    public function getPedidodtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
    }

    public function setBaixadodtAttribute($value)
    {
        $this->attributes['baixadodt'] = $this->convertStringToDate($value);
    }

    public function getBaixadodtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
    }


    public function getCanceladodtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
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


}
