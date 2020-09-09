<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResources extends JsonResource
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
            'email' => $this->email,
            'telefone' => $this->telefone,
            'celular' => $this->celular,
            'endereco' => $this->endereco,
            'inativo' => $this->inativo,
            'descricao' => $this->nome.'  '.$this->cnpjcpf,
            'cidade' => new CidadeResources($this->cidade),
        ];
    }
}
