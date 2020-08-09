<?php

namespace App\Http\Controllers;

use App\Http\Requests\PessoaRequest;
use App\Http\Resources\PessoaColletion;
use App\Http\Resources\PessoaResource;
use App\Model\Pessoa;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PessoaController extends Controller
{
    protected $model;
    protected $user;
    protected $nomeprograma = 'Pessoas';

    public function __construct(Pessoa $pessoa, User $user)
    {
        $this->model = $pessoa;
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
            return PessoaColletion::collection($resultado);
            //return response()->json($resultado);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function store(PessoaRequest $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnincluir)
        {
            $resultado = DB::transaction(function() use ($request) {
                $pessoaCreate = new Pessoa;
                $usuario = $this->user->show( $request->user()->id );
                $pessoaCreate->fill($request->all());
                $pessoaCreate->empresa_id = $usuario->empresa_id;
                $pessoaCreate->inativo = false;
                if (!empty($request->cidade))
                    $pessoaCreate->cidade_id = (int) $request->cidade['id'];
                if ($pessoaCreate->save())
                {
                    if (!empty($request->pessoa_tp))
                    {
                        $pessoaCreate->pessoaTp()->sync($request->pessoa_tp);
                    }
                    $this->user->log($request, $this->nomeprograma, 'INCLUIR', $pessoaCreate->id);
                    return $pessoaCreate->id;
                }
            });

            return $resultado;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function edit(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = $this->model->where('id',$id)->with('cidade')->with('pessoaTp')->get()->first();
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function show(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = $this->model->where('id',$id)->with('cidade')->with('pessoaTp')->get()->first();
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function update(PessoaRequest $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnalterar)
        {
            $resultado = DB::transaction(function () use ($id, $request) {
                $data  = $this->model->where('id',$id)->with('pessoaTp')->get()->first();
                $tipos = $data->pessoaTp->toArray();
                $tipon = $request->pessoa_tp;
                $data->fill($request->all());
                if (!empty($request->cidade)) $data->cidade_id = (int) $request->cidade['id'];
                if ($data->save())
                {
                    if (!empty($tipon) && !$this->identical_values($tipos, $tipon ) )
                    {
                        $data->pessoaTp()->sync( $tipon );
                    }
                    else if (empty($tipon) && !empty($tipos) )
                    {
                        $data->pessoaTp()->detach();
                    }
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

    public function lista(Request $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = $this->model->all();
            return PessoaResource::collection($data); //response()->json($data);
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
            $valorPesquisa = $request->cnpjcpf;
            $data = DB::table('pessoas')->select(DB::raw("id, nome, cnpjcpf"))
            ->where('cnpjcpf','=', $valorPesquisa)
            ->limit(10)
            ->get();
            return $data;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    private function identical_values( $arrayA , $arrayB ) {

        sort( $arrayA );
        sort( $arrayB );

        return $arrayA == $arrayB;
    }
}
