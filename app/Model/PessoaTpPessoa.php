<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PessoaTpPessoa extends Pivot
{
    protected $table = 'pessoa_tp_pessoa';
    public $incrementing = true;


}
