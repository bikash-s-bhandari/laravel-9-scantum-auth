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


    public function logout(Request $request) {

		// Revoke the token that was used to authenticate the current request
		$request->user()->currentAccessToken()->delete();
		//$request->user->tokens()->delete(); // use this to revoke all tokens (logout from all devices)
        // auth()->user()->tokens()->delete();
		return response()->json(null, 200);
	}

    public function getAuthenticatedUser(Request $request) {
		return $request->user();
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
