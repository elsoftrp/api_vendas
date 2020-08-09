<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\User;

class FinanceiroItem extends Model
{
    protected $table = 'financeiro_items';
    protected $fillable = [
        'financeiro_id',
        'pagto_tp_id',
        'user_id',
        'pagamentodt',
        'valorpago',
        'obs',
    ];


    public function financeiro()
    {
        return $this->belongsTo(Financeiro::class, 'financeiro_id');
    }

    public function pagtoTp()
    {
        return $this->hasOne(PagtoTp::class, 'id','pagto_tp_id');
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function setPagamentodtAttribute($value)
    {
        $this->attributes['pagamentodt'] = $this->convertStringToDate($value);
    }

    public function getPagamentodtAttribute($value)
    {
        if ($value)
        {
            return date('d/m/Y', strtotime($value));
        }
        else return null;
    }

    private function convertStringToDate(?string $param)
    {
        if(empty($param)){
            return null;
        }

        list($day, $month, $year) = explode('/', $param);
        return (new \DateTime($year . '-' . $month . '-' . $day))->format('Y-m-d');
    }

    private function clearField(?string $param)
    {
        if(empty($param)){
            return '';
        }

        return str_replace(['.', '-', '/', '(', ')', ' '], '', $param);
    }

}
