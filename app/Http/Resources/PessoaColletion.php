<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PessoaColletion extends JsonResource
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
            'nome' => $this->nome,
            'cnpjcpf' => $this->cnpjcpf,
            'telefone' => $this->telefone,
            'celular' => $this->celular,
            'inativo' => $this->inativo,
            'cidades' => ['cidade' => $this->cidade->cidade],
            'pessoa_tp' => PessoaTpResource::collection($this->pessoaTp),
        ];
    }
}
