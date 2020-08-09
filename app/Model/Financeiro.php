<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Financeiro extends Model
{
    protected $table = 'financeiros';
    protected $fillable = [
        'empresa_id',
        'pedido_id',
        'pagto_tp_id',
        'plano_conta_id',
        'tpfinanceiro',
        'parcela',
        'vencimentodt',
        'pagamentodt',
        'quitadodt',
        'valor',
        'valorpago',
        'obs',
        'pessoa_id',
    ];

    public function busca($pesquisa = null, $order = null, $direct = 'asc', $idEmpresa = null, $posicao = 'T')
    {
        $orderBy = $this->fieldOrder($order);
        if ($posicao === 'B' && $orderBy === 'vencimentodt' && $direct === 'asc')
        {
            $orderBy = 'pagamentodt';
            $direct = 'desc';
        }

        $results = $this->where(function ($query) use ($pesquisa, $idEmpresa, $posicao)
        {
            if ($pesquisa)
            {
                $query->whereRaw('lower(pessoas.nome) LIKE ? ', [strtolower($pesquisa).'%'])
                ->orWhere('pessoas.cnpjcpf','LIKE','%'.$pesquisa.'%');
            } else
            {
                $query->where('pessoas.inativo',false);
            }
            if ($idEmpresa) $query->where('financeiros.empresa_id', $idEmpresa);
            if ($posicao === 'A') $query->whereNull('financeiros.quitadodt');
            if ($posicao === 'B') $query->whereNotNull('financeiros.quitadodt');

        })->leftJoin('pessoas', 'pessoas.id','=','financeiros.pessoa_id')
        ->leftJoin('pagto_tp','pagto_tp.id','=','financeiros.pagto_tp_id')
        ->leftJoin('plano_contas','plano_contas.id','=','financeiros.plano_conta_id')
        ->select('financeiros.id', 'financeiros.pessoa_id','pessoas.nome', 'pessoas.cnpjcpf','financeiros.vencimentodt','financeiros.pagamentodt'
                ,'financeiros.quitadodt','financeiros.valor','financeiros.valorpago','financeiros.tpfinanceiro', 'financeiros.pagto_tp_id','pagto_tp.despagtotp'
                ,'financeiros.plano_conta_id','plano_contas.desplano')
        ->orderBy($orderBy, $direct)
        ->paginate(10);
        return $results;
    }

    public function fieldOrder($value)
    {
        $fields = array('id' => 'financeiros.id'
            ,'nome' => 'pessoas.nome'
            ,'cnpjcpf' => 'pessoas.cnpjcpf'
            ,'vencimentodt' => 'financeiros.vencimentodt'
            ,'pagamentodt' => 'financeiros.pagamentodt'
            ,'quitadodt' => 'financeiros.quitadodt'
            ,'valor' => 'financeiros.valor'
            ,'valorpago' => 'financeiros.valorpago'
            ,'tpfinanceiro' => 'financeiros.tpfinanceiro'
        );
        if (!empty($fields[$value]))
            return $fields[$value];
        else
            return $fields['vencimentodt'];
    }

    public function financeiroItem()
    {
        return $this->hasMany(FinanceiroItem::class, 'financeiro_id');
    }

    public function pessoa()
    {
        return $this->hasOne(Pessoa::class, 'id','pessoa_id');
    }

    public function pedido()
    {
        return $this->hasOne(Pedido::class, 'id','pedido_id');
    }

    public function pagtoTp()
    {
        return $this->hasOne(PagtoTp::class, 'id','pagto_tp_id');
    }

    public function planoConta()
    {
        return $this->hasOne(PlanoConta::class, 'id','plano_conta_id');
    }

    public function getCreatedAtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
    }

    public function getUpdatedAtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
    }

    public function setVencimentodtAttribute($value)
    {
        $this->attributes['vencimentodt'] = $this->convertStringToDate($value);
    }

    public function getVencimentodtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
    }

    public function setPagamentodtAttribute($value)
    {
        $this->attributes['pagamentodt'] = $this->convertStringToDate($value);
    }

    public function getPagamentodtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
    }

    public function setQuitadodtAttribute($value)
    {
        $this->attributes['quitadodt'] = $this->convertStringToDate($value);
    }

    public function getQuitadodtAttribute($value)
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
