<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartidasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partidas', function (Blueprint $table) {
            $table->increments('idPartida')->unique();
            $table->integer("jugador1")->unsigned();
            $table->integer("jugador2")->unsigned();
            $table->integer("estados")->default(0);            
            $table->timestamps();
            $table->foreign('jugador1')->references('id')->on('users');  // id usuario
            $table->foreign('jugador2')->references('id')->on('users');  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partidas');
    }
}
