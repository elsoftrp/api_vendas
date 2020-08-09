<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinanceiroRequest;
use App\Http\Resources\FinanceiroResource;
use App\Http\Resources\FinanceiroCollection;
use App\Model\Financeiro;
use App\Model\FinanceiroItem;
use App\Model\PagtoTp;
use App\Repository\AbstractRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceiroController extends Controller
{
    protected $model;
    protected $user;
    protected $nomeprograma = 'Financeiros';

    public function __construct(Financeiro $financeiro, User $user)
    {
        $this->model = $financeiro;
        $this->user  = $user;
    }

    public function index(Request $request)
    {
        $order = null;
        $direct = 'asc';
        $pesquisa = null;
        $posicao = 'T';
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            if ($request->has('order')) $order = $request->query('order');
            if ($request->has('dir'))  $direct = $request->query('dir');
            if ($request->has('pesquisa'))  $pesquisa = $request->query('pesquisa');
            if ($request->has('posicao')) $posicao = $request->query('posicao');
            //$direct = $direct === 'asc' ? 'desc' : 'asc';
            $resultado = $this->model->busca($pesquisa, $order, $direct, $request->user()->empresa_id, $posicao);
            //return response()->json($resultado);
            return FinanceiroCollection::collection($resultado);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }


    public function store(FinanceiroRequest $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnincluir)
        {
            $resultado = DB::transaction(function() use ($request) {
                $dataCreate = new Financeiro;
                $usuario = $this->user->show( $request->user()->id );
                $dataCreate->fill($request->all());
                $dataCreate->empresa_id = $usuario->empresa_id;
                if (!empty($request->pessoa))
                    $dataCreate->pessoa_id = (int) $request->pessoa['id'];
                if (!empty($request->pedido))
                    $dataCreate->pedido_id = (int) $request->pedido['id'];
                if (!empty($request->pagto_tp))
                    $dataCreate->pagto_tp_id = (int) $request->pagto_tp['id'];
                if (!empty($request->plano_conta))
                    $dataCreate->plano_conta_id = (int) $request->plano_conta['id'];
                if (!$request->has('valorpago'))
                    $dataCreate->valorpago = 0;
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
            return new FinanceiroResource($data);
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
            return new FinanceiroResource($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function update(FinanceiroRequest $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnalterar)
        {
            if ($this->existeFinanceiroBaixado($id)) return response()->json(['Financeiro já baixado'], 405);
            else
            {
                $resultado = DB::transaction(function() use ($request,$id)
                {
                    $data = Financeiro::where('id',$id)->first();
                    $data->fill($request->all());
                    $data->pedido_id = $request->has('pedido') ? (int) $request->pedido['id'] : null;
                    if (!empty($request->pessoa))
                        $data->pessoa_id = (int) $request->pessoa['id'];
                    if (!empty($request->pagto_tp))
                        $data->pagto_tp_id = (int) $request->pagto_tp['id'];
                    if (!empty($request->plano_conta))
                        $data->plano_conta_id = (int) $request->plano_conta['id'];
                        if ($data->save())
                    {
                        $this->user->log($request, $this->nomeprograma, 'ALTERAR', $data->id);
                        return $data->id;
                    }
                });
                return $resultado;
            }
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }


    public function destroy(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnexcluir)
        {
            $data = Financeiro::where('id',$id)->first();
            if ($data->valorpago > 0) return response()->json(['Financeiro já baixado'], 405);
            if ($data->pedido_id) return response()->json(['Venda relacionada, não é possível excluir esse título'], 405);
            else
            {
                $resultado = DB::transaction(function() use ($request,$data)
                {
                    if ($data->delete())
                    {
                        $this->user->log($request, $this->nomeprograma, 'EXCLUIR', $data->id);
                        return $data;
                    }
                });
                return $resultado;
            }
        }
         else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function buscaFinanceiro(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = $this->model->where('pessoa_id',$id)->get();
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function baixaFinanceiro(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnchave1)
        {
            if ($request->has('financeiro_item'))
            {
                $resultado = DB::transaction(function() use ($request,$id)
                {

                    $data = Financeiro::where('id',$id)->first();
                    $this->validate($request, [
                        'pagto_tp' => 'required',
                        'pagamentodt' => 'required',
                        'valorpago' => 'required'
                    ]);
                    $itens = $request->get('financeiro_item');
                    foreach ($itens as $item) {
                        $idPagto = (int) $item['pagto_tp']['id'];
                        $pagto = PagtoTp::where('id',$idPagto)->first();
                        $item['pagto_tp_id'] = $pagto->id;

                        $data->financeiroItem()->create($item);
                    }
                });
                return $resultado;
            }
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function estorno(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnchave2)
        {
            $idItem = FinanceiroItem::where('financeiro_id',$id)->select('id')->latest()->first();
            if ($idItem)
            {
                return app(FinanceiroItemController::class)->destroy($request, $idItem['id']);
            }
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function existeFinanceiroBaixado($id)
    {
        $existeBaixa = false;
        $data = $this->model->where('id',$id)->first();
        if ($data)
        {
            if ($data->valorpago > 0) $existeBaixa = true;
        }
        return $existeBaixa;
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
