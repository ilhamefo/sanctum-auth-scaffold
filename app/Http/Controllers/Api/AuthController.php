<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->only(['email', 'password', 'password_confirmation', 'name']), [
            'email' => ['required', 'email', 'unique:users'],
            'name' => ['required', 'max:32', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)->letters()->numbers()->uncompromised()],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));

            $response = [
                'status' => true,
                'messages' => 'User created',
                'user' => $user // new UserResource($user)
            ];

            return response()->json($response, Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => $th->getMessage()
            ];
            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function logout()
    {
        try {
            Auth::logout();

            $response = [
                'success' => true,
                'messages' => "Logged Out!"
            ];
            return response()->json($response, Response::HTTP_RESET_CONTENT);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => $th->getMessage()
            ];
            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->only(['email', 'password']), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'success'   => false,
                    'message' => ['These credentials does not match our records.']
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $token = $user->createToken('ApiToken')->plainTextToken;

            $response = [
                'success'   => true,
                'user'      => $user,
                'token'     => $token
            ];

            return response()->json($response, Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => $th->getMessage()
            ];
            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
