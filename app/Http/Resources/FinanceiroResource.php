<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinanceiroResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=> $this->id,
            'empresa_id'=> $this->empresa_id,
            'pessoa_id'=> $this->pessoa_id,
            'pessoa' => new PessoaFinanceiroResource($this->pessoa),
            'pedido_id'=> $this->pedido_id,
            'tpfinanceiro'=> $this->tpfinanceiro,
            'parcela' => $this->parcela,
            'vencimentodt'=> $this->vencimentodt,
            'pagamentodt'=> $this->pagamentodt,
            'quitadodt'=> $this->quitadodt,
            'valor'=> $this->valor,
            'valorpago'=> $this->valorpago,
            'obs'=> $this->obs,
            'plano_conta_id' => $this->plano_conta_id,
            'plano_conta' => new PlanoContaResource($this->planoConta),
            'pagto_tp_id' => $this->pagto_tp_id,
            'pagto_tp' => new PagtoTpResource($this->pagtoTp),
            'created_at'=> $this->created_at,
            'updated_at'=> $this->updated_at,
            'financeiro_item' => FinanceiroItemResource::collection($this->financeiroItem),
        ];
    }
}
