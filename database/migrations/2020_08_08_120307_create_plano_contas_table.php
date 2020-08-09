<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanoContasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plano_contas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plano_conta_id')->nullable();
            $table->string('classificacao',50);
            $table->string('desplano',50);
            $table->string('tipo',50)->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('plano_contas');
    }
}
