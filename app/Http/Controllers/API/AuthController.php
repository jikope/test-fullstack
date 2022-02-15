<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:writer,member'
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => "Validation Failed"
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $permission = $this->generate_permission($request->role);
        $token = $user->createToken('auth_token', $permission)->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => "Validation Failed"
            ], 400);
        }


        if (!Auth::attempt($request->only(['email', 'password'])))
        {
            return response()->json([
                'message' => "Invalid Email or Password"
            ], 400);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function generate_permission($role) {
        if ($role === "writer") {
            return ['create-post', 'update-post', 'detele-post'];
        } else if ($role === "member") {
            return ['create-comment', 'update-comment', 'detele-comment'];
        }
    }
}
