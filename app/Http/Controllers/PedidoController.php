<?php

namespace App\Http\Controllers;

use App\Http\Requests\PedidoRequest;
use App\Http\Resources\PedidoCollection;
use App\Http\Resources\PedidoResource;
use App\Model\Financeiro;
use App\Model\Pedido;
use App\Model\PedidoItem;
use App\Model\Produto;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    protected $model;
    protected $user;
    protected $nomeprograma = 'Pedidos';

    public function __construct(Pedido $pedido, User $user)
    {
        $this->model = $pedido;
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
            $direct = $direct === 'asc' ? 'desc' : 'asc';
            $resultado = $this->model->busca($pesquisa, $order, $direct, $request->user()->empresa_id);
            //return response()->json($resultado);
            return PedidoCollection::collection($resultado);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function store(PedidoRequest $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnincluir)
        {
            $resultado = DB::transaction(function() use ($request) {
                $pedidoCreate = new Pedido;
                $usuario = $this->user->show( $request->user()->id );
                $pedidoCreate->fill($request->all());
                $pedidoCreate->devolucao = 0;
                $pedidoCreate->empresa_id = $usuario->empresa_id;
                $pedidoCreate->user_id = $usuario->id;
                if (!empty($request->pessoa))
                    $pedidoCreate->pessoa_id = (int) $request->pessoa['id'];
                if (!empty($request->pagto_tp))
                    $pedidoCreate->pagto_tp_id = (int) $request->pagto_tp['id'];
                if ($pedidoCreate->tp_pagto === 'A')
                    $pedidoCreate->parcelas = 0;
                if ($pedidoCreate->save())
                {
                    ///// itens do pedido
                    $itensCreate = $request->get('pedido_item');
                    foreach ($itensCreate as $item) {
                        $idProd = (int) $item['produto']['id'];
                        $produto = Produto::where('id',$idProd)->first();
                        $item['empresa_id'] = $usuario->empresa_id;
                        $item['produto_id'] = $produto->id;
                        $item['prcusto'] = $produto->prcusto;
                        $pedidoCreate->pedidoItem()->create($item);
                    }
                    //// contas a receber
                    $this->geraFinanceiro($pedidoCreate);
                    $this->user->log($request, $this->nomeprograma, 'INCLUIR', $pedidoCreate->id);
                    return $pedidoCreate->id;
                }
            });
            return $resultado;
        } else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function geraFinanceiro($pedido)
    {
        if ($pedido->tp_pagto === 'A')
        {
            $receber = new Financeiro;
            $receber->fill([
                'pedido_id' => $pedido->id,
                'pessoa_id' => $pedido->pessoa_id,
                'empresa_id' => $pedido->empresa_id,
                'pagto_tp_id' => $pedido->pagto_tp_id,
                'tpfinanceiro' => 'R',
                'parcela' => 0,
                'vencimentodt' => $pedido->pedidodt,
                'pagamentodt' => $pedido->pedidodt,
                'quitadodt' => $pedido->pedidodt,
                'valor' => $pedido->totpedido,
                'valorpago' => $pedido->totpedido,
                'obs' => 'VENDA À VISTA'
            ]);
            $receber->save();
            $receber->financeiroItem()->create([
                'user_id' => $pedido->user_id,
                'pagto_tp_id' => $pedido->pagto_tp_id,
                'pagamentodt' => $pedido->pedidodt,
                'valorpago' => $pedido->totpedido,
                'obs' => 'VENDA À VISTA'
            ]);
        }
        else if ($pedido->tp_pagto === 'P')
        {
            for ($i=1; $i <= $pedido->parcelas; $i++) {
                if ($i === $pedido->parcelas && $pedido->parcelas > 1) $valorParcela = ($pedido->totpedido - ($pedido->totpedido / $pedido->parcelas));
                else $valorParcela = ($pedido->totpedido / $pedido->parcelas);
                $vencimento = $this->vencimentoParcela($i, $pedido->pedidodt, $pedido->dias_pri, $pedido->dias_prox);

                $receber = new Financeiro;
                $receber->fill([
                    'pedido_id' => $pedido->id,
                    'pessoa_id' => $pedido->pessoa_id,
                    'empresa_id' => $pedido->empresa_id,
                    'pagto_tp_id' => $pedido->pagto_tp_id,
                    'parcela' => $i,
                    'tpfinanceiro' => 'R',
                    'vencimentodt'=> $vencimento,
                    'valor' => $valorParcela,
                    'valorpago' => 0,
                    'obs' => 'VENDA À PRAZO'
                ]);
                $receber->save();
            }
        }
    }

    public function vencimentoParcela($parcela, $data, $dias_pri, $dias_prox = null)
    {
        if(empty($data)){
            return null;
        }
        list($day, $month, $year) = explode('/', $data);
        $datapadrao = (new \DateTime($year . '-' . $month . '-' . $day));
        $datapadrao->modify('+'.$dias_pri.'days');
        if ($parcela > 1 && $dias_prox)
        {
            $dias = $parcela === 2 ? $dias_prox : (($parcela-1)*$dias_prox);
            $datapadrao->modify('+'.$dias.'days');
        }
        return $datapadrao->format('d/m/Y');
    }

    public function show(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = $this->model->where('id',$id)->with('pessoa')->with('pedidoItem.produto')->get()->first();
            return new PedidoResource($data);//response()->json($data); //
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
            $data = $this->model->where('id',$id)->with('pessoa')->with('pedidoItem.produto')->get()->first();
            return new PedidoResource($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }


    public function update(PedidoRequest $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnalterar)
        {
            $existeBaixa = $this->existeFinanceiroBaixado($id);
            if ($existeBaixa) return response()->json(['Financeiro já baixado'], 405);
            else
            {
                $resultado = DB::transaction(function () use ($id, $request)
                {
                    $data  = $this->model->where('id',$id)->with('pedidoItem')->get()->first();
                    $data->fill($request->all());
                    if (!empty($request->pessoa)) $data->pessoa_id = (int) $request->pessoa['id'];
                    if (!empty($request->pagto_tp)) $data->pagto_tp_id = (int) $request->pagto_tp['id'];
                    if ($data->save())
                    {
                        $itensn = $request->get('pedido_item');
                        foreach ($itensn as $item) {
                            $itemUpdate = PedidoItem::where('id',$item['id'])->first();
                            $itemUpdate->fill($item);
                            if (!empty($item['produto'])) $itemUpdate->produto_id = (int) $item['produto']['id'];
                            $itemUpdate->save();
                        }

                        $receber = Financeiro::where('pedido_id',$id)->first();
                        if ($receber)
                        {
                            Financeiro::where('pedido_id',$id)
                                        ->where('pessoa_id',$data->pessoa_id)
                                        ->where('empresa_id',$data->empresa_id)
                                        ->delete();
                        }
                        $this->geraFinanceiro($data);

                        $this->user->log($request, $this->nomeprograma, 'ALTERAR', $id);
                        return $data;
                    }
                });
                return $resultado;
            }
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
            $existeBaixa = $this->existeFinanceiroBaixado($id);
            if ($existeBaixa) return response()->json(['Financeiro já baixado'], 405);
            else
            {
                $resultado = DB::transaction(function () use ($id, $request)
                {
                    $data = $this->model->where('id',$id)->first();
                    $data->cancelado = 'S';
                    $data->canceladodt = now();
                    if ($data->save())
                    {
                        Financeiro::where('pedido_id',$id)
                                        ->where('pessoa_id',$data->pessoa_id)
                                        ->where('empresa_id',$data->empresa_id)
                                        ->delete();
                        $this->user->log($request, $this->nomeprograma, 'CANCELADO', $id);
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

    public function existeFinanceiroBaixado($id)
    {
        $data = $this->model->where('id',$id)->first();
        $existeBaixa = false;
        if ($data->tp_pagto === 'P')
        {
            $titulos = Financeiro::where('pedido_id',$id)
                                ->where('pessoa_id',$data->pessoa_id)
                                ->where('empresa_id',$data->empresa_id)
                                ->get();
            if ($titulos)
            {
                foreach ($titulos as $titulo) {
                    if ($titulo->valorpago > 0) $existeBaixa = true;
                }
            }
        }
        return $existeBaixa;
    }

    public function buscaCliente(Request $request)
    {
        $valorPesquisa = $request->pesquisa;
        $resultado = DB::table('pessoas')->select(DB::raw('id, nome, cnpjcpf'))
        ->where('nome','LIKE', '%'.$valorPesquisa.'%')
        ->orWhere('cnpjcpf','LIKE', $valorPesquisa.'%')
        ->limit(10)
        ->get();
        return $resultado;
    }

    public function buscaProduto(Request $request)
    {
        $valorPesquisa = $request->pesquisa;
        $resultado = DB::table('produtos')->select(DB::raw('id, despro, prvenda'))
        ->where('despro','LIKE', '%'.$valorPesquisa.'%')
        ->limit(10)
        ->get();
        return $resultado;
    }

    public function buscaVendas(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = $this->model->where('pessoa_id',$id)
                ->whereNull('cancelado')
                ->select('id','pedidodt','totpedido')
                ->orderBy('pedidodt','desc')
                ->get();
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function buscaVendaProdutos(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = PedidoItem::where('pedido_id',$id)
                ->leftJoin('produtos', 'produtos.id','=','pedido_items.produto_id')
                ->select('produtos.id','produtos.despro','pedido_items.prvenda','pedido_items.quantidade','pedido_items.prtotal')
                ->get();
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function resumoVendas(Request $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $usuario = $this->user->show( $request->user()->id );
            $id_empresa = $usuario->empresa_id;
            $mes = now()->format('m');
            $mes_anterior = now()->modify('- 1 month')->format('m');
            $receitas_mes   = DB::table('pedidos')
                                ->select(DB::raw('SUM(totpedido) as valor'))
                                ->where('empresa_id',$id_empresa)
                                ->whereNull('cancelado')
                                ->whereMonth('pedidodt','=', $mes)
                                ->get();
            $receitas_mes_ant = DB::table('pedidos')
                                ->select(DB::raw('SUM(totpedido) as valor'))
                                ->where('empresa_id',$id_empresa)
                                ->whereNull('cancelado')
                                ->whereMonth('pedidodt','=', $mes_anterior)
                                ->get();
            $receitas_ano   = DB::table('pedidos')
                                ->select(DB::raw('SUM(totpedido) as valor'))
                                ->where('empresa_id',$id_empresa)
                                ->whereNull('cancelado')
                                ->whereYear('pedidodt','=', now())
                                ->get();
            return response()->json(['mes_atual' => $receitas_mes, 'mes_anterior' => $receitas_mes_ant, 'ano_atual' => $receitas_ano]);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function resumoDiario(Request $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $usuario = $this->user->show( $request->user()->id );
            $id_empresa = $usuario->empresa_id;
            $mes = now()->format('m');
            $ano = now()->format('Y');
            if ($request->has('mes')) $mes = $request->query('mes');
            $resultado   = DB::table('pedidos')
                                ->select('pedidodt as dia',DB::raw('count(id) as qde, SUM(totpedido) as valor'))
                                ->where('empresa_id',$id_empresa)
                                ->whereNull('cancelado')
                                ->whereMonth('pedidodt','=', $mes)
                                ->whereYear('pedidodt','=' ,$ano)
                                ->groupBy('pedidodt')
                                ->orderBy('pedidodt','desc')
                                ->get();

            return response()->json($resultado);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function listaVendas(Request $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $usuario = $this->user->show( $request->user()->id );
            $id_empresa = $usuario->empresa_id;
            $date = now();
            if ($request->has('date')) $date = $this->convertStringToDate($request->query('date'));
            $resultado   = Pedido::where('empresa_id',$id_empresa)
                                    ->whereNull('cancelado')
                                    ->where('pedidodt','=', $date)
                                    ->get();

            return PedidoCollection::collection($resultado);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    private function convertStringToDate(?string $param)
    {
        if(empty($param)){
            return null;
        }

        list($day, $month, $year) = explode('-', $param);
        return (new \DateTime($year . '-' . $month . '-' . $day))->format('Y-m-d');
    }


}
