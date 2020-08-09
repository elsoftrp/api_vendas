<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinanceiroItemRequest;
use App\Http\Resources\FinanceiroItemResource;
use App\Model\Financeiro;
use App\Model\FinanceiroItem;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repository\FinanceiroItemRepository;

class FinanceiroItemController extends Controller
{
    protected $model;
    protected $user;
    protected $nomeprograma = 'Financeiros';

    public function __construct(FinanceiroItem $financeiro, User $user)
    {
        $this->model = $financeiro;
        $this->user  = $user;
    }


    public function index(Request $request)
    {
        $resultado = $this->model;
        $repository = new FinanceiroItemRepository($resultado);
        if($request->has('conditions')) {
            $repository->selectCoditions($request->get('conditions'));
        }
        if($request->has('fields')) {
            $repository->selectFilter($request->get('fields'));
        }
        if($request->has('orders')) {
            $repository->selectOrders($request->get('orders'));
        }
        return FinanceiroItemResource::collection($repository->getResult()->paginate(10));
        //return response()->json($repository->getResult()->paginate(10));
    }


    public function store(FinanceiroItemRequest $request)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnchave1)
        {
            $financeiro = Financeiro::where('id', $request->financeiro['id'])->first();
            if (!$financeiro->quitadodt)
            {
                $resultado = DB::transaction(function() use ($request, $financeiro) {
                    $dataCreate = new FinanceiroItem;
                    $dataCreate->fill($request->all());
                    $dataCreate->user_id = (int) $request->user()->id;
                    $dataCreate->financeiro_id = (int) $request->financeiro['id'];
                    $dataCreate->pagto_tp_id = (int) $request->pagto_tp['id'];
                    if ($dataCreate->save())
                    {
                        $financeiro->valorpago = ($financeiro->valorpago + $dataCreate->valorpago);
                        $financeiro->pagamentodt = $dataCreate->pagamentodt;
                        if ($financeiro->valor <= $financeiro->valorpago)
                        {
                            $financeiro->quitadodt = $dataCreate->pagamentodt;
                        }
                        $financeiro->save();

                        $this->user->log($request, $this->nomeprograma, 'INCLUIR', $dataCreate->id);
                        return $dataCreate->id;
                    }
                });
                return $resultado;
            }
            else
            {
                return response()->json(['Financeiro já quitado'], 406);
            }
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
            $data = $this->model->where('id',$id)->with('pagtoTp')->first();
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
            $data = $this->model->where('id',$id)->with('pagtoTp')->first();
            return response()->json($data);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }


    public function update(FinanceiroItemRequest $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnchave1)
        {
            $resultado = DB::transaction(function() use ($request, $id) {
                $data = FinanceiroItem::where('id', $id)->first();
                $valor = $data->valorpago;
                $data->fill($request->all());
                $data->user_id = $request->user()->id;
                $data->pagto_tp_id = (int) $request->pagto_tp['id'];
                if ($data->save())
                {
                    $financeiro = Financeiro::where('id', $data->financeiro->id)->first();
                    $financeiro->valorpago = (($financeiro->valorpago - $valor) + $data->valorpago);
                    $financeiro->pagamentodt = $data->pagamentodt;
                    if ($financeiro->valor <= $financeiro->valorpago)
                    {
                        $financeiro->quitadodt = $data->pagamentodt;
                    }
                    $financeiro->save();

                    $this->user->log($request, $this->nomeprograma, 'ALTERAR', $data->id);
                    return $data->id;
                }
            });
            return $resultado;
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }


    public function destroy(Request $request, $id)
    {
        $direitos = $this->user->permissao($request, $this->nomeprograma);
        if ($direitos && $direitos->btnchave3)
        {
            $resultado = DB::transaction(function () use ($id, $request) {
                $data = $this->model->where('id', $id)->first();
                if ($data->delete())
                {
                    $financeiro = Financeiro::where('id', $data->financeiro_id)->first();
                    $financeiro->valorpago = ($financeiro->valorpago - $data->valorpago);
                    $financeiro->pagamentodt = $this->ultimoPagamento($data->financeiro->id, $id);
                    if ($financeiro->valor > $financeiro->valorpago)
                    {
                        $financeiro->quitadodt = null;
                    }
                    $financeiro->save();

                    $this->user->log($request, $this->nomeprograma, 'EXCLUIR', $id);
                    return $financeiro;
                }
            });
            return response()->json($resultado);
        }
        else
        {
            return response()->json(['sem permissão'], 403);
        }
    }

    public function ultimoPagamento($id, $itemId)
    {
        $resultado = FinanceiroItem::where('financeiro_id', $id)->where('id','<>',$itemId)->orderBy('pagamentodt','desc')->first();
        return $resultado ? $resultado->pagamentodt: null;
    }

}
