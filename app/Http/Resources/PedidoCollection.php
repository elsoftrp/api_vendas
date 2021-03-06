<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PedidoCollection extends JsonResource
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
            'pedidodt' => $this->pedidodt,
            'pessoa_id' => $this->pessoa_id,
            'pessoa' => new PessoaFinanceiroResource($this->pessoa),
            'totpedido' => $this->totpedido
        ];
    }
}
