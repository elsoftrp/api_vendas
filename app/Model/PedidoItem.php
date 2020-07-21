<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $table = 'pedido_items';
    protected $fillable = [
        'pedido_id',
        'empresa_id',
        'produto_id',
        'prcusto',
        'prvenda',
        'quantidade',
        'desconto',
        'prtotal',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function produto()
    {
        return $this->hasOne(Produto::class, 'id','produto_id');
    }
}
