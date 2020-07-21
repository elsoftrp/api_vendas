<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramasusuario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programasusuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('programa_id');
            $table->boolean('btnincluir')->nullable();
            $table->boolean('btnalterar')->nullable();
            $table->boolean('btnvisualizar')->nullable();
            $table->boolean('btnexcluir')->nullable();
            $table->boolean('btnimprimir')->nullable();
            $table->boolean('btnchave1')->nullable();
            $table->boolean('btnchave2')->nullable();
            $table->boolean('btnchave3')->nullable();
            $table->boolean('btnchave4')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('programa_id')->references('id')->on('programas')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('programasusuario');
    }
}
