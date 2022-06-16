<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Auth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
        * @OA\Post(
        * path="/api/v1/login",
        * operationId="authLogin",
        * tags={"Login"},
        * summary="User Login",
        * description="Login User Here",
        *     @OA\RequestBody(
        *         @OA\JsonContent(),
        *         @OA\MediaType(
        *            mediaType="multipart/form-data",
        *            @OA\Schema(
        *               type="object",
        *               required={"email", "password"},
        *               @OA\Property(property="email", type="email"),
        *               @OA\Property(property="password", type="password")
        *            ),
        *        ),
        *    ),
        *      @OA\Response(
        *          response=201,
        *          description="Login Successfully",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=200,
        *          description="Login Successfully",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=422,
        *          description="Unprocessable Entity",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(response=400, description="Bad request"),
        *      @OA\Response(response=404, description="Resource Not Found"),
        * )
        */

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

            if ($user) {
                if(Hash::check($request->password,$user->password)){
                    $token = $user->createToken('token')->plainTextToken;
                    $cookie = cookie('jwt', $token, 60 * 24); // 1 day
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                         ],
                        'token'=>$token,
                        'message' => 'Login Success!'
                    ])->withCookie($cookie);

                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Password did not match!'
                    ], 401);

                }


            }else{

                return response()->json([
                    'success' => false,
                    'message' => 'User Not Found!'
                ], 400);

            }




    }

     /**
        * @OA\Post(
        * path="/api/v1/register",
        * operationId="Register",
        * tags={"Register"},
        * summary="User Register",
        * description="User Register here",
        *     @OA\RequestBody(
        *         @OA\JsonContent(),
        *         @OA\MediaType(
        *            mediaType="multipart/form-data",
        *            @OA\Schema(
        *               type="object",
        *               required={"name","email", "password", "password_confirmation"},
        *               @OA\Property(property="name", type="text"),
        *               @OA\Property(property="email", type="text"),
        *               @OA\Property(property="password", type="password"),
        *               @OA\Property(property="password_confirmation", type="password")
        *            ),
        *        ),
        *    ),
        *      @OA\Response(
        *          response=201,
        *          description="Register Successfully",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=200,
        *          description="Register Successfully",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(
        *          response=422,
        *          description="Unprocessable Entity",
        *          @OA\JsonContent()
        *       ),
        *      @OA\Response(response=400, description="Bad request"),
        *      @OA\Response(response=404, description="Resource Not Found"),
        * )
        */


    public function register(Request $request){

        $rules = [
            'name'=>'required|string|min:2|max:50',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:5|max:50',
            'confirm_password'=>'required|same:password'

        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message'=>'Validation Failed.',
                'errors' => $validator->messages()
            ], 422);
        }

        $user=User::create([
            'name'=>$request->input('name'),
            'email'=>$request->input('email'),
            'password'=>Hash::make($request->password),

        ]);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Registration success!'
        ],201);


    }


    public function logout(Request $request) {

		// Revoke the token that was used to authenticate the current request
		$request->user()->currentAccessToken()->delete();
		//$request->user->tokens()->delete(); // use this to revoke all tokens (logout from all devices)
        // auth()->user()->tokens()->delete();
		return response()->json(['message'=>'User successfully logout', 200]);
	}

    public function getAuthenticatedUser(Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user(),

        ],200);

	}


	public function sendPasswordResetLinkEmail(Request $request) {
		$request->validate(['email' => 'required|email']);

		$status = Password::sendResetLink(
			$request->only('email')
		);

		if($status === Password::RESET_LINK_SENT) {
			return response()->json(['message' => __($status)], 200);
		} else {
			throw ValidationException::withMessages([
				'email' => __($status)
			]);
		}
	}

	public function resetPassword(Request $request) {
		$request->validate([
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|min:8|confirmed',
		]);

		$status = Password::reset(
			$request->only('email', 'password', 'password_confirmation', 'token'),
			function ($user, $password) use ($request) {
				$user->forceFill([
					'password' => Hash::make($password)
				])->setRememberToken(Str::random(60));

				$user->save();

				event(new PasswordReset($user));
			}
		);

		if($status == Password::PASSWORD_RESET) {
			return response()->json(['message' => __($status)], 200);
		} else {
			throw ValidationException::withMessages([
				'email' => __($status)
			]);
		}
	}

}
