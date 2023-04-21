<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\Events\Registered;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\Currency;
use App\Models\Group;

class GeneralController extends Controller
{
    public function general(Request $request)
    {
        try{

            return response()->json([
                'currencies' => Currency::get(),
                'groups' => Group::where('id', '<>', env('GROUP_TRANSFER_ID'))
                ->get()
            ]);
        } catch(\Illuminate\Database\QueryException $ex){
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

}