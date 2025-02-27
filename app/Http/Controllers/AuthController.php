<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    function index(){
        return view("login/index");
    }
    function login(Request $request){

//        Session::flash('email', $request->email);
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'Email Student is required',
            'email.email' => 'Email Student is invalid',
            'password.required' => 'Password is required'
        ]);

        $infologin = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if(Auth::attempt($infologin)){
            return redirect('/dashboard')->with('success', 'Login Berhasil');
        }else{
            return redirect('/login')->with('error', 'Email Student atau Password yang dimasukkan tidak valid!');
        }

    }
}
