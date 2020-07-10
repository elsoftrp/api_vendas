<?php

namespace App;

use App\Model\Cidade;
use App\Model\ProgramasUsuario;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','darkmode','cidade_id','empresa_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function cidade()
    {
        return $this->hasOne(Cidade::class,'id','cidade_id');
    }

    public function empresa()
    {
        return $this->hasOne(Empresa::class,'id','empresa_id');
    }

    public function permissao($request, $janela)
    {
        $id = $request->user()->id;
        $resultado = ProgramasUsuario::leftJoin('programas', 'programas.id','=','programasusuario.programa_id')
            ->select('menutitle','menuicon','itemtitle','itemicon','link','name','btnincluir','btnalterar',
                    'btnexcluir','btnvisualizar','btnimprimir', 'btnchave1','btnchave2','btnchave3','btnchave4')
            ->where('nomeprograma',$janela)
            ->where('user_id',$id)
            ->first();
        return $resultado;
    }

    public function log($request, $janela, $evento, $campochave, $campochave1 = null, $campochave2 = null)
    {
        $id = $request->user()->id;
        $ip = $request->ip();
        $user = $this->show($id);
        $log = ['user_id' => $id,
                'usuario' => $user->name,
                'evento' => $evento,
                'data' => now(),
                'tabela' => $janela,
                'fid' => $campochave,
                'fid1' => $campochave1,
                'fid2' => $campochave2,
                'ip' => $ip
        ];
        DB::table('log')->insert($log);
    }

    public function show($id)
    {
        return User::find($id);
    }

    public function busca($pesquisa = null, $order = null, $direct = 'asc')
    {
        $orderBy = $this->fieldOrder($order);
        $results = $this->where(function ($query) use ($pesquisa)
        {
            if ($pesquisa)
            {
                $query->where('users.name','LIKE', $pesquisa.'%')
                ->orWhere('users.email','LIKE','%'.$pesquisa.'%');
            }
        })
        ->select('users.id','users.name','users.email')
        ->orderBy($orderBy, $direct)
        ->paginate(10);
        return $results;
    }

    public function fieldOrder($value)
    {
        $fields = array('id' => 'id'
            ,'name'      => 'name'
            ,'email'     => 'email'
        );
        if (!empty($fields[$value]))
            return $fields[$value];
        else
            return $fields['name'];
    }

}
