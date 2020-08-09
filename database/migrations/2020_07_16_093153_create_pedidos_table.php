<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('pessoa_id');
            $table->unsignedBigInteger('user_id');
            $table->double('totproduto');
            $table->double('desconto');
            $table->double('devolucao');
            $table->double('totpedido');

            $table->string('tp_pagto',1)->nullable();
            $table->unsignedTinyInteger('parcelas');
            $table->unsignedSmallInteger('dias_pri');
            $table->unsignedSmallInteger('dias_prox');
            $table->unsignedBigInteger('pagto_tp_id')->nullable();

            $table->string('baixado',1)->nullable();
            $table->dateTime('baixadodt')->nullable();

            $table->string('cancelado',1)->nullable();
            $table->dateTime('canceladodt')->nullable();

            $table->timestamps();
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('pessoa_id')->references('id')->on('pessoas');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('pagto_tp_id')->references('id')->on('pagto_tp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
}
