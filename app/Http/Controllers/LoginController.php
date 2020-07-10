<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Model\Programa;
use App\Model\ProgramasUsuario;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected $model;
    protected $nomeprograma = 'Usuarios';

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required'
        ]);
        $user = User::where('email',$request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password))
        {
            throw ValidationException::withMessages(['As credenciais não estão corretas']);
        }

        return $user->createToken($request->device_name)->plainTextToken;
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json('logout', 201);
    }

    public function index(Request $request)
    {
        $order = null;
        $direct = 'asc';
        $pesquisa = null;
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            if ($request->has('order')) $order = $request->query('order');
            if ($request->has('dir'))  $direct = $request->query('dir');
            if ($request->has('pesquisa'))  $pesquisa = $request->query('pesquisa');
            $entity = $this->model->busca($pesquisa, $order, $direct);
            return response()->json($entity);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function store(UserRequest $request, User $userCreate)
    {
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnincluir)
        {
            $resultado = DB::transaction(function() use ($request, $userCreate) {
                $userCreate->create([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'password' => bcrypt($request['password']),
                    'darkmode' => $request['darkmode'],
                    'cidade_id' => $request['cidade_id'],
                    'empresa_id' => $request['empresa_id']
                ]);
                if ($userCreate)
                {
                    $this->model->log($request, $this->nomeprograma, 'INCLUIR', $userCreate->id);
                    return $userCreate->id;
                }
            });
            return $resultado;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function edit($id, Request $request)
    {
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $entity     = User::where('id',$id)->first();
            return response()->json($entity);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function update(Request $request, $id)
    {
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnalterar)
        {
            $resultado = DB::transaction(function () use ($id, $request) {
                $entity = User::where('id', $id)->first();
                $entity->fill([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'darkmode' => $request['darkmode'],
                    'cidade_id' => $request['cidade_id'],
                    'empresa_id' => $request['empresa_id']
                    ]);
                if ($entity->save())
                {
                    $this->model->log($request, $this->nomeprograma, 'ALTERAR', $id);
                    return $entity;
                }
            });
            return $resultado;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function seek(Request $request)
    {
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if (!$direitos)
        {
            return response()->json(['sem permissão'], 403);
        }
        else
        {
            $valorPesquisa = $request->email;
            $entity = DB::table('users')->select(DB::raw("id, name, email"))
            ->where('email','=', $valorPesquisa)
            ->limit(10)
            ->get();
            return $entity;
        }
    }

    public function mudarsenha(Request $request)
    {
        $direitos = $this->model->permissao($request, 'MudaSenha');
        if ($direitos)
        {
            $email     = $request['email'];
            $novasenha = $request['novasenha'];
            $novasenhaconfirma = $request['novasenhaconfirma'];
            if ( $novasenha === $novasenhaconfirma )
            {
                $resultado = DB::transaction(function () use ($request, $email, $novasenha) {
                    $entity = User::where('email', $email)->first();
                    $entity->fill(['password' => bcrypt($novasenha)]);
                    if ($entity->save())
                    {
                        $this->model->log($request, 'MudaSenha', 'ALTERAR', $entity->id);
                        return 'Alterado com sucesso';
                    }
                });
                return $resultado;
            }
            else
            {
                return response()->json(['Senhas não conferem'], 406);
            }
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function menuUser(Request $request)
    {
        $id = $request->user()->id;
        $menu = ProgramasUsuario::leftJoin('programas', 'programas.id','=','programasusuario.programa_id')
            ->select('menutitle','menuicon','itemtitle','itemicon','link','name','btnincluir','btnalterar',
                     'btnexcluir','btnchave1','btnchave2','btnchave3','btnchave4','descricao')
            ->where('programasusuario.user_id',$id)
            ->orderBy('menutitle')
            ->orderBy('itemtitle')
            ->get();
        return response()->json($menu);
    }

    public function indexPermissao($id, Request $request)
    {
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if (!$direitos)
        {
            return response()->json(['sem permissão'], 403);
        }
        else
        {
            $resultado = ProgramasUsuario::leftJoin('programas', 'programas.id','=','programasusuario.programa_id')
                ->select('btnincluir','btnalterar','btnexcluir','btnchave1', 'btnchave2','btnchave3', 'btnchave4',
                        'menutitle','menuicon','itemtitle','itemicon','link', 'user_id',
                        'name','nomeprograma','programasusuario.programa_id','programasusuario.id')
                ->where('user_id',$id)
                ->orderBy('menutitle')
                ->orderBy('itemtitle')
                ->get();
            return response()->json($resultado);
        }
    }

    public function storePermissao(Request $request)
    {
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnincluir)
        {
            $retorno = DB::transaction(function () use ($request) {
                $ProgramasUsuario = ProgramasUsuario::create($request->all());
                if ($ProgramasUsuario)
                {
                    $this->model->log($request, $this->nomeprograma.' PERMISSAO', 'INCLUIR',
                         $ProgramasUsuario->user_id, $ProgramasUsuario->programa_id, $ProgramasUsuario->id);
                    return $ProgramasUsuario->id;
                }
            });
            return $retorno;
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function updatePermissao(Request $request, $id)
    {
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnalterar)
        {
            $resultado = DB::transaction(function () use ($request, $id) {
                $programasUsuario = ProgramasUsuario::where('id', $id)->first();
                $programasUsuario->fill([
                    'btnincluir' => $request['btnincluir'],
                    'btnalterar' => $request['btnalterar'],
                    'btnexcluir' => $request['btnexcluir'],
                    'btnchave1' => $request['btnchave1'],
                    'btnchave2' => $request['btnchave2'],
                    'btnchave3' => $request['btnchave3'],
                    'btnchave4' => $request['btnchave4']
                ]);
                if ($programasUsuario->save())
                {
                    $this->model->log($request, $this->nomeprograma.' PERMISSAO', 'ALTERAR', $programasUsuario->id);
                    return $programasUsuario;
                }

            });
            return $resultado;
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function destroyPermissao($id, Request $request)
    {
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnalterar)
        {
            $retorno = DB::transaction(function () use ($request, $id) {
                $programasUsuario = ProgramasUsuario::where('id', $id)->delete();
                if ($programasUsuario)
                {
                    $this->model->log($request, $this->nomeprograma.' PERMISSAO', 'EXCLUIR', $id);
                    return $programasUsuario;
                }
            });
            return $retorno;
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function modulos($id, Request $request)
    {
        $direitos = $this->model->permissao($request, $this->nomeprograma);
        if (!$direitos)
        {
            return response()->json(['sem permissão'], 403);
        }
        else
        {
            $resultado = Programa::whereNotIn('id',function ($query) use($id) {
                        $query->select('programa_id')->from('programasusuario')
                        ->where('programasusuario.user_id',$id)->get();
                    })
                ->orderBy('menutitle')
                ->orderBy('itemtitle')
                ->get();
            return response()->json($resultado);
        }
    }


}
