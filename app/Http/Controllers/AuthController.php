<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $user = User::query()->create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
        ]);

        if ($user) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'Register successfully',
                'token' => $token,
            ]);
        }else{
            return response()->json([
                'message' => 'Register Failed',
            ]);
        }
    }

    public function login(LoginRequest $request)
    {

        try {
            $user = User::where('email', $request->email)->firstOrFail();

            if (Hash::check($request->password, $user->password)) {
                Auth::login($user);
                $user->tokens()->delete();
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'message' => 'Login successfully',
                    'token' => $token,
                ]);
            }

            return response()->json(['message' => 'Unauthorized'], 401);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

    }
}
