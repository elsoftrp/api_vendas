<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinanceiroItemResource extends JsonResource
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
            'financeiro_id' => $this->financeiro_id,
            'pagto_tp_id' => $this->pagto_tp_id,
            'pagto_tp' => ['id' => $this->pagtoTp->id, 'despagtotp' => $this->pagtoTp->despagtotp],
            'pagamentodt' => $this->pagamentodt,
            'valorpago' => $this->valorpago,
            'obs' => $this->obs,
            'created_at' => $this->created_at,
            'usuario' => ['id'=> $this->usuario->id, 'name' => $this->usuario->name ]
        ];
    }
}
