<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('produto_id');

            $table->double('prcusto');
            $table->double('prvenda');
            $table->double('quantidade');
            $table->double('desconto');
            $table->double('prtotal');

            $table->timestamps();
            $table->foreign('pedido_id')->references('id')->on('pedidos');
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('produto_id')->references('id')->on('produtos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedido_items');
    }
}
