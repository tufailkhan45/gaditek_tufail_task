<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function createUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:6',
            ]);

            if ($validator->fails()) {
                //return validation errror
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();
            $token = $user->createToken('API Access Token')->plainTextToken;
            return ['success' => 'User created successfully', 'token' => $token];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:6',
            ]);

            if ($validator->fails()) {
                //return validation errror
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return ['error' => 'invalid'];
            } else {
                $token = auth()->user()->createToken('API Access Token')->plainTextToken;
                return ['success' => 'User created successfully', 'token' => $token];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
