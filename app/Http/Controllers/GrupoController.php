<?php

namespace App\Http\Controllers;

use App\Model\Grupo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoController extends Controller
{
    protected $model;
    protected $user;
    protected $nomeprograma = 'Grupos';

    public function __construct(Grupo $grupo, User $user)
    {
        $this->model = $grupo;
        $this->user  = $user;
    }

    public function index(Request $request)
    {
        $order = null;
        $direct = 'asc';
        $pesquisa = null;
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            if ($request->has('order')) $order = $request->query('order');
            if ($request->has('dir'))  $direct = $request->query('dir');
            if ($request->has('pesquisa'))  $pesquisa = $request->query('pesquisa');
            $data = $this->model->busca($pesquisa, $order, $direct);
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function store(Request $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnincluir)
        {
            $resultado = DB::transaction(function() use ($request) {
                $dataCreate = new Grupo;
                $dataCreate->fill($request->all());
                if ($dataCreate->save())
                {
                    $this->user->log($request, $this->nomeprograma, 'INCLUIR', $dataCreate->id);
                    return $dataCreate->id;
                }
            });
            return $resultado;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }


    public function show(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = $this->model->where('id',$id)->first();
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }


    public function edit(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = $this->model->where('id',$id)->first();
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function update(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnalterar)
        {
            $resultado = DB::transaction(function () use ($id, $request) {
                $data = $this->model->where('id',$id)->first();
                $data->fill($request->all());
                if ($data->save())
                {
                    $this->user->log($request, $this->nomeprograma, 'ALTERAR', $id);
                    return $data;
                }
            });
            return $resultado;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function destroy(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnexcluir)
        {
            $resultado = DB::transaction(function () use ($id, $request) {
                $data = $this->model->where('id',$id)->first();
                if ($data->delete())
                {
                    $this->user->log($request, $this->nomeprograma, 'EXCLUIR', $id);
                    return $data;
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
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $valorPesquisa = $request->descricao;
            $data = DB::table('grupos')->select(DB::raw("id, descricao"))
            ->where('descricao','=', $valorPesquisa)
            ->limit(10)
            ->get();
            return $data;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }
}
