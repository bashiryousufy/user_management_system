<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        //validate parameters
        $registerData = $request->validate([
            'name' => 'max:128|required',
            'email' => 'email|required|unique:users',
            'password' => 'required'
        ]);

        //create user using validated data
        $user = User::create([
            'name' => $registerData['name'],
            'email' => $registerData['email'],
            'password' => Hash::make($registerData['password'])
        ]);


        //create Access token
        $accessToken = $user->createToken('access_token')->accessToken;

        return response([
            'user' => $user,
            'access_token' => $accessToken
        ], 201);
    }

    public function login(Request $request)
    {

        //validate parameters
        $loginCreds = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        $user = User::where('email', $loginCreds['email'])->first();
        //invalid login credentials
        if (!$user || !Hash::check($loginCreds['password'], $user->password)) {
            return response([
                'message'=> 'invalid credentials!'
            ], 404);
        }



        $accessToken = $user->createToken('access_token')->accessToken;

        return response([
            'user' => $user,
            'access_token' => $accessToken
        ]);
    }
}