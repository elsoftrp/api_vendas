<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contador extends Model
{
    protected $primaryKey = 'coluna';
    protected $table = 'ncontador';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = array('coluna', 'valor');

    public function codigo(String $coluna)
    {
        $codigo = (int) DB::table('ncontador')->select(DB::raw("valor"))
        ->where('coluna','=', $coluna)
        ->value('valor');
        if ($codigo != 0)
        {
            DB::table('ncontador')->where('coluna','=', $coluna)->increment('valor');
            return $codigo;
        } else
        {
            DB::table('ncontador')->insert(['coluna' => $coluna, 'valor' => 2]);
            return 1;
        }
    }
}
