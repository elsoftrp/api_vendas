<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinanceiroItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financeiro_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('financeiro_id');
            $table->unsignedBigInteger('pagto_tp_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('pagamentodt')->nullable();
            $table->double('valorpago',11,2);
            $table->string('obs',100)->nullable();
            $table->timestamps();
            $table->foreign('financeiro_id')->references('id')->on('financeiros')->onDelete('CASCADE');
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
        Schema::dropIfExists('financeiro_items');
    }
}
