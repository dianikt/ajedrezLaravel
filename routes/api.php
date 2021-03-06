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
Route::POST('/finalizar', function(Request $request) {

    $jugador = $request->only('idPartida', "idJugador1", "idJugador2");
    
    $jugador1 = User::where('id', $jugador['idJugador1'])->update(['status' => 0]);
    $jugador2 = User::where('id', $jugador['idJugador2'])->update(['status' => 0]);  

    $partida = partidas::where('idPartida', $jugador['idPartida'])->update(['estados' => -1]);   
 
    return response()->json(
        ['jugadores' => $jugador]);
});



/*
*Devuelve la lista de jugadores menos el que acaba de iniciar sesion
*/
Route::POST('/recargaTablero', function(Request $request) {

    $fichas = $request->only('idFicha1', "idFicha2", 'pos1', 'pos2');
    
    $ficha1 = fichas::where('idFicha',$fichas['idFicha1'])->update(['pos' => $fichas['pos1']]);  
    $ficha2 = fichas::where('idFicha',$fichas['idFicha2'])->update(['pos' => $fichas['pos2']]);  

    $ficha1 = fichas::where('idFicha',$fichas['idFicha1'])->get(); 
    $ficha2 = fichas::where('idFicha',$fichas['idFicha2'])->get();   
    
    return response()->json(
        ['ficha1' => $ficha1, 
         'ficha2' => $ficha2
    ]);
});

/*
*Devuelve la lista de jugadores menos el que acaba de iniciar sesion
* status jugadores que han iniciado sesion
*/
Route::POST('/eligeJugador', function(Request $request) {

	$jugador = $request->only('idJugador1');
    $jugadores = User::where('status','1')->where('id','<>',$jugador['idJugador1'])->get();   
    
    return response()->json(
        ['jugadores' => $jugadores]);
});


/*
*Devuelve la partida que tiene el jugador 
*/
Route::POST('/comprobarPartidas', function(Request $request) {

    $jugador = $request->only('idJugador1');

    $partida = partidas::where('jugador1', $jugador['idJugador1'])->orwhere('jugador2', $jugador['idJugador1'])->where('estados', '>',0)->get(); 
   
       
    return response()->json(
        ['partidas' => $partida     
         
        ]);
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

	$partida = $request->only('idFicha1','estado', 'idPartida', 'posNueva');	
		
		$ok = partidas::where('idPartida', $partida['idPartida'])->update(['estados' => $partida['estado']]);
		$ok1 = fichas::where('idFicha', $partida['idFicha1'] )->update(['pos' => $partida['posNueva']]);
		$estado = 1;
        	
		
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

