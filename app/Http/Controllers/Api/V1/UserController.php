<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Tymon\JWTAuth\Facades\JWTAuth;
 
use App\Models\User;
use App\Models\Category;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        
        $data = User::find($user->id);

        if($data) {
            return response()->json($data);
        }
        return response([
            'message' =>  'Datos no encontrados',
            'detail' => 'La información no existe'
        ], 400);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'email' => [
                    'required'
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

            $pass = Hash::make($request->input('password'));
            $user = User::create([
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'password' => $pass,
            ]);

            Category::create([
                'name' => 'Transferencia',
                'group_id' => env('GROUP_TRANSFER_ID') ?? 1,
                'user_id' => $user->id
            ]);
            
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = auth()->user();

        $data = User::find($user->id)
        ->first();

        if($data) {
            return response()->json($data);
        }
        return response([
            'message' =>  'Datos no encontrados',
            'detail' => 'La información no existe'
        ], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try{

            $user = User::find(auth()->user()->id);

            $user->name = $request->input('name');

            
            if($request->password) {
                $user->password = Hash::make($request->password);
            }
            if($request->badge_id) {
                $user->badge_id = $request->badge_id;
            }
            $user->save();

            return response()->json([
                'message' => 'Datos guardados',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'badge_id' => $user->badge_id,
                ]
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * Display the specified user information.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        $user = auth()->user();
        unset($user->password);
        return response()->json($user);
    }

    /**
     * Display the specified user information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();
            User::find($user->id)
            ->update([
                'name' => $request->input('name'),
            ]);
            
            if($request->input('password')) {
                User::find($user->id)
                ->update([
                    'password' => Hash::make($request->input('password'))
                ]);
            }
            return response()->json([
                'message' => 'Datos guardados',
                'data' => [
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                ]
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }
}
