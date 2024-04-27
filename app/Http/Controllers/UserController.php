<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                // Password must contain almost 1 character and 1 number
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            ],
            'password_confirmation' => 'required|same:password',
        ]);
    
        if ($validator->fails()) {
            
            $response = ['errors' => null];
            
            $errors = $validator->errors();
           
            if ($errors->has('name')) $response['errors']['name'] = 'Name is required.';
            
            if ($errors->has('email')) $response['errors']['email'] = 'Email is invalid or has already been taken.';
            
            if ($errors->has('password')) $response['errors']['password'] = 'Password must contain at least 8 characters, 1 letter and 1 number.';
            if ($errors->has('password_confirmation')) $response['errors']['password'] = 'Password must be the same.';

            return response()->json($response, 400);
        } else {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->save();

            $user->sendEmailVerificationNotification();
            
            return response()->json(['message' => 'User registered successfully. Please check your email.'], 200);
        }
    }
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                // Password must contain almost 1 character and 1 number
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            ],
            'remember' => 'nullable|boolean',
        ]);
    
        if ($validator->fails()) {
            $response = ['errors' => null];

            $errors = $validator->errors();

            if($errors->has('email')) $response['errors']['email'] = 'Invalid email.';
            if($errors->has('password')) $response['errors']['password'] = 'Invalid password.';

            return response()->json($response, 401);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !(Hash::check($request->password, $user->password))) {
            return response()->json(['errors' => ['user' => 'Invalid credentials.']], 422);
        } else {
            if (!$user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
                return response()->json(['errors' => ['email' => 'Please verify your email to complete the registration process.']], 403);
            }
        }

        $sessionToken = $user->createToken('session_token')->plainTextToken;
        
        $response = ['sessionToken' => $sessionToken];
        
        if($request->remember) {
            $rememberToken = $user->createToken('remember_token', ['*'], now()->addMonth())->plainTextToken;
            $user->update(['remember_token' => $rememberToken]);
            $response['rememberToken'] = $rememberToken;
        } else {
            $user->update(['remember_token' => null]);
        }

        return response()->json($response, 200);
    }

    public function getUser(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    public function rememberSession(Request $request)
    {
        $user = $request->user();

        $sessionToken = $user->createToken('session_token')->plainTextToken;

        return response()->json(['sessionToken' => $sessionToken]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        
        return response()->json(['message' => 'Session closed successfully.'], 200);
    }
    

    public function verify(Request $request)
    {
        if (!URL::hasValidSignature($request)) return response()->json(['error' => 'Invalid verification URL.'], 401);

        $user = User::findOrFail($request->id);
    

        if ($user->hasVerifiedEmail()) return response()->json(['message' => 'The user has already been verified.'], 409);
    
        $user->markEmailAsVerified();
    
        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if ($request->password && (Hash::check($request->password, $user->password))) {
            $request->user()->delete();
    
            $request->user()->tokens()->delete();
    
            return response()->json(['message' => 'Account deleted successfully.']);
        } else {
            return response()->json(['errors' => ['password' => 'Invalid password']]);
        }
    }
}





