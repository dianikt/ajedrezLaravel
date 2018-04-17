<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function auth()
    {
       // $name = Auth::user()->name;  
        return "hola";
        /*Route::get('/auth/{name}', function($name){
        $users = users::where('name', $name)->select('token')->get();
        return "hola";
        if($users[0]['token'] == 0){

            $rand_part = str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'.uniqid());
            return "tu token es : ".$rand_part;
        }else{
            return $users[0]['token'];
        }

    });*/

    }
    
}
