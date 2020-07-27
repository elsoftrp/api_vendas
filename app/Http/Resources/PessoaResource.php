<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PessoaResource extends JsonResource
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
            //'empresa_id' => $this->empresa_id,
            'nome' => $this->nome,
            'cnpjcpf' => $this->cnpjcpf,
            'insrg' => $this->insrg,
            'insmunicipal' => $this->insmunicipal,
            'razaosocial' => $this->razaosocial,
            'fantasia' => $this->fantasia,
            'abrevnome' => $this->abrevnome,
            'telefone' => $this->telefone,
            'celular' => $this->celular,
            'celular2' => $this->celular2,
            'email' => $this->email,
            'cep' => $this->cep,
            'endereco' => $this->endereco,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'cidade_id' => $this->cidade_id,
            'inativo' => $this->inativo,
            'inativodt' => $this->inativodt,
            'obs' => $this->obs,
            'created_at' => $this->created_at,
            'cidade' => [ 'cidade' => $this->cidade->cidade, 'uf' => $this->cidade->uf ],
            'pessoa_tp' => PessoaTpResource::collection( $this->pessoaTp),
        ];
    }
}
