<?php

namespace App\Http\Controllers;

use App\Http\Requests\PedidoRequest;
use App\Http\Resources\PedidoResource;
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
            $resultado = $this->model->busca($pesquisa, $order, $direct, $request->user()->empresa_id);
            return response()->json($resultado);
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
                $pedidoCreate->dinheiro = 0;
                $pedidoCreate->troco = 0;
                $pedidoCreate->fiado = 0;
                $pedidoCreate->cartaodebito = 0;
                $pedidoCreate->cartaocredito = 0;
                $pedidoCreate->boleto = 0;
                $pedidoCreate->empresa_id = $usuario->empresa_id;
                $pedidoCreate->user_id = $usuario->id;
                if (!empty($request->pessoa))
                    $pedidoCreate->pessoa_id = (int) $request->pessoa['id'];
                if ($pedidoCreate->save())
                {
                    $itensCreate = $request->get('pedido_item');
                    foreach ($itensCreate as $item) {
                        $idProd = (int) $item['produto']['id'];
                        $produto = Produto::where('id',$idProd)->first();
                        $item['empresa_id'] = $usuario->empresa_id;
                        $item['produto_id'] = $produto->id;
                        $item['prcusto'] = $produto->prcustof;
                        $pedidoCreate->pedidoItem()->create($item);
                    }
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

    /*public function store(PedidoRequest $request)
    {

                $pedidoCreate = new Pedido;
                $usuario = $this->user->show( $request->user()->id );
                $pedidoCreate->fill($request->all());
                $pedidoCreate->devolucao = 0;
                $pedidoCreate->dinheiro = 0;
                $pedidoCreate->troco = 0;
                $pedidoCreate->fiado = 0;
                $pedidoCreate->cartaodebito = 0;
                $pedidoCreate->cartaocredito = 0;
                $pedidoCreate->boleto = 0;
                $pedidoCreate->empresa_id = $usuario->empresa_id;
                $pedidoCreate->user_id = $usuario->id;
                if (!empty($request->pessoa))
                    $pedidoCreate->pessoa_id = (int) $request->pessoa['id'];

                $itensCreate = $request->get('pedidoItem');
                foreach ($itensCreate as $item) {
                    $item['empresa_id'] = $usuario->empresa_id;
                    $item['produto_id'] = $item['produto']['id'];
                    dd($item);
                    //

                    //$pedidoCreate->pedidoItem()->create($item);
                }

    }*/


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
            $resultado = DB::transaction(function () use ($id, $request) {
                $data  = $this->model->where('id',$id)->with('pedidoItem')->get()->first();
                //$itens = $data->pedidoItem->toArray();
                $data->fill($request->all());
                if (!empty($request->pessoa)) $data->pessoa_id = (int) $request->pessoa['id'];
                if ($data->save())
                {
                    $itensn = $request->get('pedido_item');
                    foreach ($itensn as $item) {
                        $itemUpdate = PedidoItem::where('id',$item['id'])->first();
                        $itemUpdate->fill($item);
                        if (!empty($item['produto'])) $itemUpdate->produto_id = (int) $item['produto']['id'];
                        $itemUpdate->save();
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
                $data->cancelado = 'S';
                $data->canceladodt = now();
                if ($data->save())
                {
                    $this->user->log($request, $this->nomeprograma, 'CANCELADO', $id);
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

    public function buscaCliente(Request $request)
    {
        $valorPesquisa = $request->pesquisa;
        $pessoas = DB::table('pessoas')->select(DB::raw('id, nome, cnpjcpf'))
        ->where('nome','LIKE', $valorPesquisa.'%')
        ->orWhere('cnpjcpf','LIKE', $valorPesquisa.'%')
        ->limit(10)
        ->get();
        return $pessoas;
    }

    public function buscaProduto(Request $request)
    {
        $valorPesquisa = $request->pesquisa;
        $pessoas = DB::table('produtos')->select(DB::raw('id, despro, prvenda'))
        ->where('despro','LIKE', $valorPesquisa.'%')
        ->orWhere('ean','LIKE', $valorPesquisa.'%')
        ->limit(10)
        ->get();
        return $pessoas;
    }

    public function buscaVendas(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos)
        {
            $data = $this->model->where('pessoa_id',$id)->select('id','pedidodt','totpedido')->orderBy('pedidodt','desc')->get();
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
                ->select('produtos.id','produtos.despro','pedido_items.prvenda','pedido_items.quantidade','pedido_items.prtotal')->get();
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }
}
