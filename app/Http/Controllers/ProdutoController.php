<?php

namespace App\Http\Controllers;

use App\Model\Produto;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    protected $model;
    protected $user;
    protected $nomeprograma = 'Produtos';

    public function __construct(Produto $produto, User $user)
    {
        $this->model = $produto;
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
                $usuario = $this->user->show( $request->user()->id );
                $dataCreate = new Produto;
                $dataCreate->fill($request->all());
                $dataCreate->empresa_id = $usuario->empresa_id;
                $dataCreate->inativo = false;
                if (!empty($request->grupo)) $dataCreate->grupo_id = (int) $request->grupo['id'];
                if (!empty($request->unidade)) $dataCreate->unidade_id = (int) $request->unidade['id'];
                if ($dataCreate->save())
                {
                    $this->user->log($request, $this->nomeprograma, 'INCLUIR', $dataCreate->id);
                    return $dataCreate;
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
            $data = $this->model->where('id',$id)->with('grupo')->with('unidade')->get()->first();
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
            $data = $this->model->where('id',$id)->with('grupo')->with('unidade')->get()->first();
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
                if (!empty($request->grupo))  $data->grupo_id = (int) $request->grupo['id'];
                if (!empty($request->unidade))  $data->unidade_id = (int) $request->unidade['id'];
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
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function seek(Request $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $valorPesquisa = $request->campo;
            $data = DB::table('produtos')->select(DB::raw("id, despro, ean"))
            ->where('despro','=', $valorPesquisa)
            ->orWhere('ean','=',$valorPesquisa)
            ->limit(10)
            ->get();
            return $data;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }
}
