<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProduto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('grupo_id');
            //$table->string('ean',50)->unique();
            $table->string('despro');
            //$table->double('prcompra')->nullable();
            //$table->double('vracrescimo')->nullable();
            $table->double('prcusto')->nullable();
            $table->double('plucro')->nullable();
            $table->double('prvenda')->nullable();
            $table->double('estoquep')->nullable();
            $table->double('ultprcompra')->nullable();
            $table->double('ultprvenda')->nullable();
            $table->dateTime('dtvenda')->nullable();
            $table->dateTime('dtcompra')->nullable();
            $table->boolean('inativo')->nullable();
            $table->dateTime('inativodt')->nullable();
            $table->timestamps();
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('grupo_id')->references('id')->on('grupos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}
