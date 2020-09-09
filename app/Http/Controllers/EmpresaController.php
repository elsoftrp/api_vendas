<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmpresaRequest;
use App\Http\Resources\EmpresaResources;
use App\Model\Categoria;
use App\Model\Cidade;
use App\Model\Contador;
use App\Model\Empresa;
use App\Model\EmpresaDocumento;
use App\Model\EmpresaHistorico;
use App\Model\Escritorio;
use App\Model\Recolhimentos;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;

use function GuzzleHttp\Promise\all;

class EmpresaController extends Controller
{

    protected $model;
    protected $user;
    protected $nomeprograma = 'Empresas';

    public function __construct(Empresa $empresa, User $user)
    {
        $this->model = $empresa;
        $this->user  = $user;
    }

    public function index(Request $request)
    {
        $order = null;
        $direct = 'asc';
        $pesquisa = null;
        //$direitos = $this->user->permissao($request, $this->nomeprograma);
        //if ($direitos)
        //{
            if ($request->has('order')) $order = $request->query('order');
            if ($request->has('dir'))  $direct = $request->query('dir');
            if ($request->has('pesquisa'))  $pesquisa = $request->query('pesquisa');
            if ($request->has('posicao')) $posicao = $request->query('posicao');
            $empresas = $this->model->busca($pesquisa, $order, $direct );
            //return response()->json($empresas);
            return EmpresaResources::collection($empresas);
        //}
        //else
        //{
        //    return response()->json(['sem permissão'], 403);
        //}
    }

    public function store(EmpresaRequest $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnincluir)
        {
            $resultado = DB::transaction(function() use ($request) {
                $empresaCreate = new Empresa;
                $empresaCreate->fill($request->all());
                $empresaCreate->inativo = false;
                if (!empty($request->cidade))
                    $empresaCreate->cidade_id = (int) $request->cidade['id'];
                if ($empresaCreate->save())
                {
                    $this->user->log($request, $this->nomeprograma, 'INCLUIR', $empresaCreate->id);
                    return $empresaCreate->id;
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
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $empresa = Empresa::where('id',$id)->with('cidade')->get()->first();
            return response()->json($empresa);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function show($id, Request $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $empresa = Empresa::where('id',$id)->with('cidade')->get()->first();
            return response()->json($empresa);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function update(EmpresaRequest $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnalterar)
        {
            $resultado = DB::transaction(function () use ($id, $request) {
                $empresa = Empresa::find($id);
                $empresa->fill($request->all());
                if (!empty($request->cidade))
                    $empresa->cidade_id     = (int) $request->cidade['id'];
                if ($empresa->save())
                {
                    $this->user->log($request, $this->nomeprograma, 'ALTERAR', $id);
                    return $id;
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
            $valorPesquisa = $request->cnpjcpf;
            $empresas = DB::table('empresas')->select(DB::raw("id, nome, cnpjcpf"))
            ->where('cnpjcpf','=', $valorPesquisa)
            ->limit(10)
            ->get();
            return $empresas;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

}
