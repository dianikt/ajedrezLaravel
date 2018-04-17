<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFichasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fichas', function (Blueprint $table) {
            $table->increments('idFicha');
            $table->integer('idPartida')->unsigned(); 
            $table->integer("jugador")->unsigned();
            $table->String("pos");            
            $table->timestamps();
           
            $table->foreign('jugador')->references('id')->on('users');
            $table->foreign('idPartida')->references('idPartida')->on('partidas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fichas');
    }
}
