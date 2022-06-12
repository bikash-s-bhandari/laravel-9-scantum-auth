<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Auth;

class AuthController extends Controller
{
    public function login(Request $request){
        $input = $request->all();
        $credentials = $request->only('email', 'password');
        $rules = [
            'email' => 'required|email',
            'password' => 'required',

        ];


        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                // 'message' => trans('validation.all_required'),
                'errors' => $validator->messages()
            ], 400);
        }

            $user = User::where('email', $input['email'])->first();
            //check if user exists
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User Not Found!'
                ], 400);
            }
             try {


                if (!Auth::attempt($request->only('email', 'password'))) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid User'
                    ], 400);
                }


                $user = Auth::user();

                $token = $user->createToken('token')->plainTextToken;
                $cookie = cookie('jwt', $token, 60 * 24); // 1 day
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'token'=>$token

                    ],
                    'message' => trans('messages.login.success')
                ])->withCookie($cookie);
            } catch (Exception $e) {

                // something went wrong while attempting to encode the token
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong'
                ], 500);
            }

    }
}
