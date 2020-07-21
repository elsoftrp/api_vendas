<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PedidoItemResource extends JsonResource
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
            'id' => $this->id,
            'pedido_id' => $this->pedido_id,
            'empresa_id' => $this->empresa_id,
            'produto_id' => $this->produto_id,
            'prcusto' => $this->prcusto,
            'prvenda' => $this->prvenda,
            'quantidade' => $this->quantidade,
            'desconto' => $this->desconto,
            'prtotal' => $this->prtotal,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'produto' => [
                'id' => $this->produto->id,
                'despro' => $this->produto->despro,
                'prvenda' => $this->produto->prvenda
            ]
        ];
    }
}
