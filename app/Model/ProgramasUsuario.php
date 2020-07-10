<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProgramasUsuario extends Model
{
    protected $table = 'programasusuario';
    protected $fillable = [
        'user_id',
        'programa_id',
        'btnincluir',
        'btnalterar',
        'btnvisualizar',
        'btnexcluir',
        'btnimprimir',
        'btnchave1',
        'btnchave2',
        'btnchave3',
        'btnchave4'
    ];


    public function programa()
    {
        return $this->hasOne(Programa::class,'id','programa_id');
    }

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function setBtnincluirAttribute($value)
    {
        $this->attributes['btnincluir'] = $value ? '1':'0' ;
    }

    public function getBtnincluirAttribute($value)
    {
        if ($value)
        {
            return $value == '1' ? true : false;
        }
        else return null;
    }

    public function setBtnalterarAttribute($value)
    {
        $this->attributes['btnalterar'] = $value ? '1':'0' ;
    }

    public function getBtnalterarAttribute($value)
    {
        if ($value)
        {
            return $value == '1' ? true : false;
        }
        else return null;
    }


    public function setBtnvisualizarAttribute($value)
    {
        $this->attributes['btnvisualizar'] = $value ? '1':'0' ;
    }

    public function getBtnvisualizarAttribute($value)
    {
        if ($value)
        {
            return $value == '1' ? true : false;
        }
        else return null;
    }


    public function setBtnexcluirAttribute($value)
    {
        $this->attributes['btnexcluir'] = $value ? '1':'0' ;
    }

    public function getBtnexcluirAttribute($value)
    {
        if ($value)
        {
            return $value == '1' ? true : false;
        }
        else return null;
    }


    public function setBtnimprimirAttribute($value)
    {
        $this->attributes['btnimprimir'] = $value ? '1':'0' ;
    }

    public function getBtnimprimirAttribute($value)
    {
        if ($value)
        {
            return $value == '1' ? true : false;
        }
        else return null;
    }


    public function setBtnchave1Attribute($value)
    {
        $this->attributes['btnchave1'] = $value ? '1':'0' ;
    }

    public function getBtnchave1Attribute($value)
    {
        if ($value)
        {
            return $value == '1' ? true : false;
        }
        else return null;
    }


    public function setBtnchave2Attribute($value)
    {
        $this->attributes['btnchave2'] = $value ? '1':'0' ;
    }

    public function getBtnchave2Attribute($value)
    {
        if ($value)
        {
            return $value == '1' ? true : false;
        }
        else return null;
    }


    public function setBtnchave3Attribute($value)
    {
        $this->attributes['btnchave3'] = $value ? '1':'0' ;
    }

    public function getBtnchave3Attribute($value)
    {
        if ($value)
        {
            return $value == '1' ? true : false;
        }
        else return null;
    }


    public function setBtnchave4Attribute($value)
    {
        $this->attributes['btnchave4'] = $value ? '1':'0' ;
    }

    public function getBtnchave4Attribute($value)
    {
        if ($value)
        {
            return $value == '1' ? true : false;
        }
        else return null;
    }


}
