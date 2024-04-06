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
    /**
     * Display a listing of the resource.
     */
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function generateSessionToken(Request $request)
    {
        $user = $request->user(); // Obtén el usuario autenticado
        $sessionToken = $this->authService->generateSessionToken($user);

        return response()->json(['session_token' => $sessionToken]);
    }
    
    public function index()
    {
        $user = User::all();
        
        return response()->json($user);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = new User;

        return response()->json($user);
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function login(Request $request)
{
    // Validar los datos del formulario
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
        'remember' => 'nullable|boolean',
    ]);

    // Verificar si la validación falla
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 422);
    }

    // Obtener el usuario por su dirección de correo electrónico
    $user = User::where('email', $request->email)->first();

    // Verificar si el usuario existe
    if (!$user) {
        return response()->json(['error' => 'No se encontró ningún usuario con ese correo electrónico.'], 404);
    }

    // Verificar si el usuario ha verificado su correo electrónico
    if (!$user->hasVerifiedEmail()) {
        // Si el usuario no ha verificado su correo electrónico, enviar el correo de verificación
        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'Por favor, verifica tu correo electrónico para completar el proceso de registro.']);
    }

    // Verificar si la contraseña coincide
    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Contraseña incorrecta.'], 401);
    }

    // // Verificar si el usuario ya ha utilizado esa contraseña
    // if (Hash::check($request->password, $user->password)) {
    //     return response()->json(['error' => 'Ya has utilizado esta contraseña antes.'], 409);
    // }

    // Generar un token de autenticación de sesión
    $sessionToken = $authService->generateSessionToken($user);

    // Generar un token de "remember" si el usuario lo solicitó
    $rememberToken = null;
    if ($request->input('remember')) {
        $rememberToken = $user->createToken('remember_token')->plainTextToken;
    }

    // Autenticación exitosa
    return response()->json([
        'user' => $user, 
        'session_token' => $sessionToken, 
        'remember_token' => $rememberToken
    ]);
}
    
    public function logout(Request $request)
{
    // Revocar todos los tokens del usuario autenticado (incluyendo el token de remember)
    $request->user()->tokens()->delete();
    
    return response()->json(['message' => 'Sesión cerrada exitosamente.']);
}

public function register(Request $request)
{
    // Validar los datos del formulario
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
    ]);

    // Verificar si la validación falla
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 422);
    }

    // Crear un nuevo usuario
    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->save();

    // Enviar correo electrónico de verificación
    $user->sendEmailVerificationNotification();

    // Responder con una confirmación
    return response()->json(['message' => 'Usuario registrado correctamente. Se ha enviado un correo electrónico de verificación.']);
}
     

     public function verify(Request $request)
     {
         // Verificar si la URL está firmada correctamente
         if (!URL::hasValidSignature($request)) {
             return response()->json(['error' => 'URL de verificación no válida.'], 401);
         }
     
         // Buscar al usuario por su ID
         $user = User::findOrFail($request->id);
     
         // Verificar si el usuario ya está verificado
         if ($user->hasVerifiedEmail()) {
             return response()->json(['message' => 'El usuario ya ha sido verificado anteriormente.']);
         }
     
         // Marcar al usuario como verificado
         $user->markEmailAsVerified();
     
         // Responder con una confirmación
         return response()->json(['message' => 'Correo electrónico verificado correctamente.']);
     }
      

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    
     public function deleteAccount(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado.'], 401);
        }

        // Eliminar el usuario de la base de datos
        $request->user()->delete();

        // Desconectar al usuario
        $request->user()->tokens()->delete();
    
        return response()->json(['message' => 'Cuenta eliminada correctamente.']);
    }
    

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    
        // Generar un token de restablecimiento de contraseña y enviar el correo
        $status = Password::sendResetLink(
            $request->only('email')
        );
    
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Correo electrónico enviado con éxito.'], 200);
        } else {
            return response()->json(['error' => 'No se pudo enviar el correo electrónico.'], 400);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Contraseña restablecida con éxito.'], 200)
            : response()->json(['error' => 'No se pudo restablecer la contraseña.'], 400);
    }
}





