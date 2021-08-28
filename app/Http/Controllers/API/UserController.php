<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'=> ['required','string','max:255'],
                'username'=>['required','string','max:255','unique:users'],
                'email'=>['required','string','email','max:255','unique:users'],
                'password'=>['required','string',new Password],
                'phone'=>['nullable','string','max:255'],
            ]);
            User::create([
                'name'=>$request->name,
                'username'=>$request->usernamename,
                'email'=>$request->email,
                'phone'=>$request->phone,
                'password'=>Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();
            $tokenResault = $user->createToken('autToken')->plantTextToken;
            return ResponseFormatter::success([
                'access_token'=> $tokenResault,
                'token_type'=> 'Bearer',
                'user' => $user 

            ],'user Register');
        } catch (Exception $error){
            return ResponseFormatter::error([
               'message'=>'someething went wrong',
                'error' => $error
               

            ], 'Authenticarion Failed',500,);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|reueired',
                'password'=> 'requeired'
            ]);
            $credentials = request(['mail','password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message'=> 'Unauthorized'

                ],'Aunthentication Failed',500);
            }
            $user = User::where('email',$request->email)->first();
            if(! Hash::check($request->password, $user->password [])){
                throw new \Exception('inivailid');
            }

            $tokenResault = $user->createToken('authToken')->pliainTextToken;
            return ResponseFormatter   ::success([
                'access_token'=>$tokenResault,
                'token_type'=> 'bearer',
                'user'=>$user
            ],'Authenticated');


        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message'=>'something went wrong',
                'error'=>$error
            ],'Authentication Failed',500);
            //throw $th;
        }
    }
    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(),'Data profil user berhasil diambil');
    }
    public function updateProfil(Request $request)
    {
        $data = $request->all();
        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user,'profile update');
    }
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token,'Token Resauld');
    }


}
