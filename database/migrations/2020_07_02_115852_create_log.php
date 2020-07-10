<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('usuario')->nullable();
            $table->string('evento')->nullable();
            $table->string('data')->nullable();
            $table->string('tabela')->nullable();
            $table->string('fid')->nullable();
            $table->string('fid1')->nullable();
            $table->string('fid2')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log');
    }
}
