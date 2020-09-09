<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    //public static $wrap = 'pedido';

    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'pedidodt' => $this->pedidodt,
            'empresa_id' => $this->empresa_id,
            'pessoa_id' => $this->pessoa_id,
            'user_id' => $this->user_id,
            'totproduto' => $this->totproduto,
            'desconto' => $this->desconto,
            'devolucao' => $this->devolucao,
            'totpedido' => $this->totpedido,
            'tp_pagto' => $this->tp_pagto,
            'parcelas' => $this->parcelas,
            'dias_pri' => $this->dias_pri,
            'dias_prox' => $this->dias_prox,
            'pagto_tp_id' => $this->pagto_tp_id,
            'baixado' => $this->baixado,
            'baixadodt' => $this->baixadodt,
            'cancelado' => $this->cancelado,
            'canceladodt' => $this->canceladodt,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'pagto_tp' => new PagtoTpResource($this->pagtoTp),//['id' => $this->pagtoTp->id, 'despagtotp' => $this->pagtoTp->despagtotp],
            'pessoa' => ['id' => $this->pessoa->id, 'nome' => $this->pessoa->nome, 'cnpjcpf' => $this->pessoa->cnpjcpf],
            'empresa' => new EmpresaSimpleResources($this->empresa),
            'pedido_item' => PedidoItemResource::collection($this->pedidoItem),
        ];
    }
}
