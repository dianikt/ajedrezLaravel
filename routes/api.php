<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\User;
use App\partidas;
use App\fichas;


Route::post('/login', function(Request $request){
	
    $user = \Auth::User();  
	$credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials)) {  
    	$rand_part = str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'.uniqid());
		$ok = User::where('email', $credentials['email'])->update(['token' => $rand_part, 'status' => '1']);
        $id = Auth::user()->id;    

		return response()->json(['email' => $credentials['email'], 'idJugador1'=> $id,
            'token' => $rand_part,'mensaje'=>1]);
	}else {
		return response()->json(['email' => $credentials['email'],'mensaje'=> 'Usuario No identificado!']);
	}
});

Route::POST('/logout', function(Request $request){	

    $credentials = $request->only('email');
    
    $user = \Auth::User();
	$ok = User::where('email', $credentials['email'])->update(['token' => '0', 'status' => '0']);
	
    return response()->json([ 'email' => $credentials['email'],
        'mensaje' => 'Logout!!.', 'ok' => 1]);	
});


/*
*Devuelve la lista de jugadores menos el que acaba de iniciar sesion
*/
Route::POST('/eligeJugador', function(Request $request) {

	$jugador = $request->only('idJugador1');
    $jugadores = User::where('status','1')->where('id','<>',$jugador['idJugador1'])->get();   
    
    return response()->json(
        ['jugadores' => $jugadores]);
});

/*
* Registro de usuario. 
*/     
Route::POST('/registro', function(Request $request) {

    $datos = $request->only('nombre', 'email', 'password');  
    $pass = bcrypt($datos['password']);  
    try {
        $user = new User;
        $user['name'] = $datos['nombre']; 
        $user['email'] = $datos['email'];             
        $user['password'] = $pass;
        $user->save();
        $msj = "Usuario Registrado Correctamente!! ";
    } catch (Exception $e) {
        $msj = "No se ha podido registar!!";
        $mensaje = $e->getMessage();
    }
    return response()->json(['mensaje' => $msj]);
});
/*
* Inicia Partida una vez haya elegido un jugador con quien jugar
*/     
Route::POST('/initPartida', function(Request $request) {

	$datosPartida = $request->only('idJugador1', 'idJugador2','token');

    $token = User::where('id', $datosPartida['idJugador1'])->get();

    if( $token[0]['token'] == $datosPartida['token']){
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
            
            $ficha1 = fichas::where('idPartida', $partida['id'])->where('jugador',$datosPartida['idJugador1'])->get();

            $ficha2 = fichas::where('idPartida', $partida['id'])->where('jugador',$datosPartida['idJugador2'])->get();

        } catch (Exception $e) {
            $estado = "Partida Exit";
            $mensaje = $e->getMessage();
        }

        return response()->json(
            [               
              'ficha1' => $ficha1,
              'ficha2' => $ficha2,
              'estado' => $partida['estados']

        ]);
    }else {
        return response()->json([ 'token'=>$token, 'mensaje' => 'El token no es coincidente']);
    }
});

function posicionInicialFichas($idPartida, $jugadorIni, $jugadorEle ){
	
    $fichasJug1 = new fichas;
        $fichasJug1['jugador'] = $jugadorIni;
        $fichasJug1['idPartida'] = $idPartida;         
        $fichasJug1['pos'] = '15';       
    $fichasJug1->save();

    $fichasJug2 = new fichas;
        $fichasJug2['jugador'] = $jugadorEle;
        $fichasJug2['idPartida'] = $idPartida;         
        $fichasJug2['pos'] = '85';      
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
Route::POST('/movimiento', function(Request $request) {

	$partida = $request->only('idFicha1', 'idFicha2','estado', 'idPartida', 'posNueva');
	
		if(($partida['estado'] == 1) || ($partida['estado']%2 != 0 )){ // turno del jugador 1 !!! 
    		$ok = partidas::where('idPartida', $partida['idPartida'])->update(['estados' => $partida['estado']]);
    		$ok1 = fichas::where('idFicha', $partida['idFicha1'] )->update(['pos' => $partida['posNueva']]);
    		$estado = "movimiento ok";
        	
		}if($partida['estado']%2 == 0){ // turno del jugador 2 !!! 
    		$ok =  partidas::where('idPartida', $partida['idPartida'] )->update(['estados' => $partida['estado']]);
    		$ok1 = fichas::where('idFicha', $partida['idFicha2'] )->update(['pos' => $partida['posNueva']]);
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

