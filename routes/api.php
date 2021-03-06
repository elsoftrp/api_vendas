<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
    Route::middleware('auth:sanctum')->get('/usuario', function (Request $request) {
        return $request->user();
    });

*/

Route::post('/login', 'LoginController@login')->name('login');

Route::group(['middleware' => ['auth:sanctum']], function()
{
    Route::get('/usuario', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', 'LoginController@logout')->name('logout');
    Route::get('/menu', 'LoginController@menuUser');
    Route::get('/permissao/{programasusuario}','LoginController@indexPermissao');
    Route::post('/permissao','LoginController@storePermissao');
    Route::put('/permissao/{programasusuario}','LoginController@updatePermissao');
    Route::delete('/permissao/{programasusuario}','LoginController@destroyPermissao');
    Route::get('/modulos/{modulos}', 'LoginController@modulos');
    Route::post('/cidades','CidadeController@index');

    Route::resource('usuarios', 'LoginController');
    Route::post('usuarios/mudar','LoginController@mudarsenha');
    Route::post('usuarios/seek','LoginController@seek');

    Route::resource('empresas', 'EmpresaController');
    Route::post('empresas/seek','EmpresaController@seek');

    Route::get('pessoastp/lista','PessoaTpController@lista');
    Route::resource('pessoastp', 'PessoaTpController');
    Route::post('pessoastp/seek','PessoaTpController@seek');

    Route::get('pessoas/lista','PessoaController@lista');
    Route::resource('pessoas', 'PessoaController');
    Route::post('pessoas/seek','PessoaController@seek');


    Route::resource('grupos', 'GrupoController');
    Route::post('grupos/seek','GrupoController@seek');
    Route::resource('unidades', 'UnidadeController');
    Route::post('unidades/seek','UnidadeController@seek');

    Route::resource('produtos', 'ProdutoController');
    Route::post('produtos/seek','ProdutoController@seek');

    Route::get('pedidos/resumo/{resumo}','PedidoController@resumoVendas');
    Route::get('pedidos/resumopormes','PedidoController@resumoVendasPorMes');
    Route::get('pedidos/resultadoano','PedidoController@resultadoAnual');
    Route::get('pedidos/resumodiario','PedidoController@resumoDiario');
    Route::get('pedidos/lista','PedidoController@listaVendas');
    Route::resource('pedidos', 'PedidoController');
    Route::get('pedidos/vendas/{id}','PedidoController@buscaVendas');
    Route::get('pedidos/vendaitens/{id}','PedidoController@buscaVendaProdutos');
    Route::post('pedidos/cliente','PedidoController@buscaCliente');
    Route::post('pedidos/produto','PedidoController@buscaProduto');

    Route::resource('pagtotp', 'PagtoTpController');

    Route::resource('planoconta', 'PlanoContaController');
    Route::post('planoconta/planopai','PlanoContaController@buscaPlano');

    Route::get('financeiro/resumo/{resumo}','FinanceiroController@resumoFinanceiro');
    Route::resource('financeiro', 'FinanceiroController');
    Route::get('financeiro/pessoa/{id}','FinanceiroController@buscaFinanceiro');
    Route::post('financeiro/planoconta','FinanceiroController@buscaPlano');
    Route::delete('financeiro/estorno/{id}','FinanceiroController@estorno');



    Route::resource('financeiroitem', 'FinanceiroItemController');


});
