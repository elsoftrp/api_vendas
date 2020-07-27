<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PagtoTp extends Model
{
    protected $table = 'pagto_tp';
    protected $fillable = [
        'despagto'
    ];
}
