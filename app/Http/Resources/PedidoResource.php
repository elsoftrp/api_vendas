<?php

namespace App\Http\Resources;

use App\Model\PedidoItem;
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
            'dinheiro' => $this->dinheiro,
            'troco' => $this->troco,
            'fiado' => $this->fiado,
            'cartaodebito' => $this->cartaodebito,
            'cartaocredito' => $this->cartaocredito,
            'boleto' => $this->boleto,
            'baixado' => $this->baixado,
            'baixadodt' => $this->baixadodt,
            'cancelado' => $this->cancelado,
            'canceladodt' => $this->canceladodt,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'pessoa' => ['id' => $this->pessoa->id, 'nome' => $this->pessoa->nome, 'cnpjcpf' => $this->pessoa->cnpjcpf],
            'pedido_item' => PedidoItemResource::collection($this->pedidoItem),
        ];
    }
}
