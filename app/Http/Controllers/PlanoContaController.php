<?php

namespace App\Http\Controllers;

use App\Model\PlanoConta;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanoContaController extends Controller
{
    protected $model;
    protected $user;
    protected $nomeprograma = 'PlanoConta';

    public function __construct(PlanoConta $planoConta, User $user)
    {
        $this->model = $planoConta;
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
            $resultado = $this->model->busca($pesquisa, $order, $direct, $request->user()->empresa_id);
            return response()->json($resultado);
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
                $dataCreate = new PlanoConta;
                $dataCreate->fill($request->all());
                if (!empty($request->plano_conta_pai))
                    $dataCreate->plano_conta_id = (int) $request->plano_conta_pai['id'];
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
            $data = $this->model->where('id',$id)->with('planoContaPai')->first();
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
            $data = $this->model->where('id',$id)->with('planoContaPai')->first();
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
                if (!empty($request->plano_conta_pai))
                    $data->plano_conta_id = (int) $request->plano_conta_pai['id'];
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

    public function buscaPlano(Request $request)
    {
        $valorPesquisa = $request->pesquisa;
        $resultado = DB::table('plano_contas')->select(DB::raw('id, desplano, classificacao'))
        ->where('desplano','LIKE', '%'.$valorPesquisa.'%')
        ->orWhere('classificacao','LIKE', $valorPesquisa.'%')
        ->limit(10)
        ->get();
        return $resultado;
    }
}
