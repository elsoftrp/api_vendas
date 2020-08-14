<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinanceirosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financeiros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('pessoa_id')->nullable();
            $table->unsignedBigInteger('pagto_tp_id');
            $table->unsignedBigInteger('plano_conta_id')->nullable();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos');
            $table->string('tpfinanceiro',1);
            $table->unsignedTinyInteger('parcela');
            $table->dateTime('vencimentodt')->nullable();
            $table->dateTime('pagamentodt')->nullable();
            $table->dateTime('quitadodt')->nullable();
            $table->double('valor',11,2);
            $table->double('valorpago',11,2);
            $table->string('obs',100)->nullable();
            $table->timestamps();
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('pessoa_id')->references('id')->on('pessoas');
            $table->foreign('pagto_tp_id')->references('id')->on('pagto_tp');
            $table->foreign('plano_conta_id')->references('id')->on('plano_contas');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financeiros');
    }
}
