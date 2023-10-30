<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Group;
use App\Models\TypeAccount;
use App\Models\Period;

use App\Controllers\Types\CommonTypesController;

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

            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Usuario o contraseña incorrecta'
                ], 401);
            }

            $user = auth()->user();

            return response()->json([
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'transfer_id' => $user->transferId->id,
                    'currency' => $user->badge_id,
                ],
                'token' => $token,
                'currencies' => Currency::get(),
                'accounts_type' => TypeAccount::get(),
                'groups_category' => Group::where([['id', '<>', env('GROUP_TRANSFER_ID')]])->get(),
                'periods' => Period::select('id as value', 'name as label')->get(),
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
                'badge_id' => [
                    'required'
                ],
            ]);

            if($validator->fails()){
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->input('password')),
                'badge_id' => $request->badge_id,
                'permissions' => ['platform.index' => "1"]
            ]);

            Category::create([
                'name' => 'Transferencia',
                'group_id' => env('GROUP_TRANSFER_ID') ?? 1,
                'user_id' => $user->id
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'transfer_id' => $user->transferId->id,
                    'currency' => $user->badge_id,
                ],
                'token' => $token,
                'currencies' => Currency::get(),
                'accounts_type' => TypeAccount::get(),
                'groups_category' => Group::where([['id', '<>', env('GROUP_TRANSFER_ID')]])->get(),
                'periods' => CommonTypesController::listPeriodicity(),
            ], 201);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  $ex->errorInfo[0] === "23000" ? 'El Usuario ya esta registrado' : 'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    public function resetPassword(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
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

            $user = User::where([
                ['email', $request->email]
            ])->first();

            if($user){
                $temp_pass = Str::random(10);
                $user->password = Hash::make($temp_pass);
                $user->save();

                $user->notify(new PasswordResetNotification($temp_pass));
            } else {
                return response()->json([
                   'message' => 'Usuario no registrado'
                ], 400);
            }



            return response()->json([
                'message' => 'Te Llegara una email, con mas informacion',
                'data' => [],
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();

        return response()->json(['message' => 'Se cerro la sesion exitosamente']);
    }
    
    public function currencies(Request $request)
    {
        return response()->json(Currency::select('id', 'code', 'name')->get());
    }
}