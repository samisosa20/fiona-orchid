<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'password' => [
                    'required',
                ],
                'email' => [
                    'required',
                    'email'
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            if (!$user = User::where([
                ['email', $request->email],
                ['password', Hash::make($request->input('password'))]
            ])->first()) {
                return response()->json([
                    'message' => 'Credenciales inválidas'
                ], 401);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'token' => $token
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  $ex->errorInfo[0] === "23000" ? 'Usuario registrado' : 'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    public function register(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'email' => [
                    'required',
                    'email'
                ],
                'password' => [
                    'required'
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->input('password'))
            ]);
            $user->save();

            $token = JWTAuth::fromUser($user);
            event(new Registered($user));

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'token' => $token
            ], 201);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  $ex->errorInfo[0] === "23000" ? 'Usuario registrado' : 'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}