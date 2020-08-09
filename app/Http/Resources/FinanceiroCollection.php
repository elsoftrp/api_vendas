<?php

namespace App\Http\Resources;

use App\Model\PlanoConta;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceiroCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pessoa' => ['id' => $this->pessoa->id, 'nome' => $this->pessoa->nome, 'cnpjcpf' => $this->pessoa->cnpjcpf ],
            'vencimentodt' => $this->vencimentodt,
            'pagamentodt' => $this->pagamentodt,
            'quitadodt' => $this->quitadodt,
            'valor' => $this->valor,
            'valorpago' => $this->valorpago,
            'tpfinanceiro' => $this->tpfinanceiro,
            'plano_conta_id' => $this->plano_conta_id,
            'plano_conta' => new PlanoContaResource($this->planoConta),
            'pagto_tp_id' => $this->pagto_tp_id,
            'pagto_tp' => new PagtoTpResource($this->pagtoTp)
        ];
    }
}
