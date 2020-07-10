<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $table = 'programas';
    protected $fillable = [
        'nomeprograma',
        'descricao',
        'menutitle',
        'menuicon',
        'itemtitle',
        'itemicon',
        'link',
        'name',
    ];

}
