<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePessoaTpPessoa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pessoa_tp_pessoa', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('pessoa_tp_id');
            $table->unsignedInteger('pessoa_id');
            $table->timestamps();
            $table->foreign('pessoa_tp_id')->references('id')->on('pessoa_tp')->onDelete('CASCADE');
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pessoa_tp_pessoa');
    }
}
