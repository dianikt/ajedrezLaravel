<?php
use Illuminate\Http\Request;
use App\User;
use App\partidas;
use App\fichas;


Route::post('/login', function(Request $request){
	
    $user = \Auth::User();  
	$credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials)) {  
    	$rand_part = str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'.uniqid());
		$ok = User::where('email', $credentials['email'])->update(['token' => $rand_part]);

		return response()->json(['email' => $credentials['email'],'token' => $rand_part,'mensaje'=>1]);
	}else {
		return response()->json(['email' => $credentials['email'],'mensaje'=> 'Usuario No identificado!']);
	}
});

Route::POST('/logout', function(Request $request){	

    $credentials = $request->only('email');
    
    $user = \Auth::User();
	$ok = User::where('email', $credentials['email'])->update(['token' => '0']);
	
    return response()->json([ 'email' => $credentials['email'],
        'mensaje' => 'Logout!!.']);	
});


/*
*Devuelve la lista de jugadores menos el que acaba de iniciar sesion
*/
Route::POST('/eligeJugador/', function(Request $request) {

	$jugador = $request->only('idJugador1');
    $jugadores = User::where('token','0')->get();   
    
    return response()->json(
        ['jugadores' => $jugadores]);
});

/*
* Inicia Partida una vez haya elegido un jugador con quien jugar
*/     
Route::POST('/initPartida', function(Request $request) {

	$datosPartida = $request->only('idJugador1', 'idJugador2', 'init');
	$fichas = [];
	try {
        $partida = new partidas;
            $partida['jugador1'] = $datosPartida['idJugador1']; 
            $partida['jugador2'] = $datosPartida['idJugador2'];             
            $partida['estados'] = 1;
        $partida->save();

        //jugador que ha iniciado sesion
        $jugadorIni = $datosPartida['idJugador1']; 
        //jugador elegigo de la lista
        $jugadorEle = $datosPartida['idJugador2'];

        $fichas = posicionInicialFichas($partida['id'], $jugadorIni, $jugadorEle);

        $estado = "Partida Init";
        $mensaje = "A Jugar !! ";
        $fichas;

    } catch (Exception $e) {
        $estado = "Partida Exit";
        $mensaje = $e->getMessage();
    }

    return response()->json(
        [               
          'estado' => $estado, 
          'mensaje' => $mensaje,
          'partida' => $partida,
          'fichas'  => $fichas
    ]);
});

function posicionInicialFichas($idPartida, $jugadorIni, $jugadorEle ){
	
    $fichasJug1 = new fichas;
        $fichasJug1['jugador'] = $jugadorIni;
        $fichasJug1['idPartida'] = $idPartida;         
        $fichasJug1['pos'] = 'A4';       
    $fichasJug1->save();

    $fichasJug2 = new fichas;
        $fichasJug2['jugador'] = $jugadorEle;
        $fichasJug2['idPartida'] = $idPartida;         
        $fichasJug2['pos'] = 'H5';      
    $fichasJug2->save();

    $ficha1 = $fichasJug1['pos'];
    $ficha2 = $fichasJug2['pos'];

    $fichas = [];
    $fichas[1]  = $ficha1;
    $fichas[2]  = $ficha2;
    
    return $fichas;
     
}

/*
* envia turno y movimiento del jugador 
*/     
Route::POST('/turno', function(Request $request) {

	$partida = $request->only('jugador', 'idPartida', 'nuevaPos', 'estado', 'idFicha');
	
		if(($partida['jugador'] == 1) && ($partida['estado']%2 != 0 )){ // turno del jugador 1 !!! 
    		$ok = partidas::where('idPartida', $partida['idPartida'])->update(['estados' => $partida['estado']]);
    		$ok1 = fichas::where('idFicha', $partida['idFicha'] )->update(['pos' => $partida['nuevaPos']]);
    		$estado = "movimiento ok";
        	
		}if(($partida['jugador'] == 2) && ($partida['estado']%2 == 0)){ // turno del jugador 2 !!! 
    		$ok =  partidas::where('idPartida', $partida['idPartida'] )->update(['estados' => $partida['estado']]);
    		$ok1 = fichas::where('idFicha', $partida['idFicha'] )->update(['pos' => $partida['nuevaPos']]);
    		$estado = "movimiento ok";
		}else{
		    $estado = "Es el turno del otro jugador";   
		    $ok = 0;
		    $ok1 = 0;     	
		}       

    return response()->json(
        [               
          'estado' => $estado,          
          'partida' => $partida,
          'ok' => $ok, 
          'ok1' => $ok1        
    ]);
});

function compruebaMovimiento($nuevaPos){
	$mvto = 0;
	return $mvto;
}

