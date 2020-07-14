<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePessoa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->string('cnpjcpf',50)->unique();
            $table->string('insrg')->nullable();
            $table->string('insmunicipal')->nullable();
            $table->string('razaosocial')->nullable();
            $table->string('fantasia')->nullable();
            $table->string('abrevnome')->nullable();
              /** contact */
            $table->string('telefone')->nullable();
            $table->string('celular')->nullable();
            $table->string('celular2')->nullable();
            $table->string('email')->nullable();
            /** address */
            $table->string('cep')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->unsignedBigInteger('cidade_id');

            $table->boolean('inativo')->nullable();
            $table->dateTime('inativodt')->nullable();
            $table->text('obs')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('cidade_id')->references('id')->on('cidades');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pessoas');
    }
}
