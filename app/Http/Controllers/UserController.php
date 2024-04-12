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
        return response()->json(['message' => 'Por favor, verifica tu correo electrónico para completar el proceso de registro.'], 401);
    }

    // Verificar si la contraseña coincide
    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Contraseña incorrecta.'], 401);
    }
    
    // Generar un token de autenticación de sesión
    $sessionToken = $user->createToken('session_token')->plainTextToken;

    // Verificar si ya tiene un token de "recordar"
    $rememberToken = $user->remember_token;

    // Si no tiene un token de "recordar", generarlo y guardarlo en la base de datos
    if (!$rememberToken) {
        $rememberToken = $user->createToken('remember_token')->plainTextToken;
        $user->update(['remember_token' => $rememberToken]);
    }

    // Autenticación exitosa
    return response()->json([
        'user' => $user, 
        'session_token' => $sessionToken, 
        'remember_token' => $rememberToken,
    ]);
}

public function compareTokens(Request $request)
{
    // Validar que el cuerpo de la solicitud contenga el token enviado desde el frontend
    $validator = Validator::make($request->all(), [
        'front_token' => 'required|string',
    ]);

    // Verificar si la validación falla
    if ($validator->fails()) {
        // Error de validación
        return response()->json(['error' => 'El token enviado desde el frontend es inválido o no está presente.'], 422);
    }

    // Obtener el usuario autenticado mediante el token de "recordar"
    $user = User::where('remember_token', $request->front_token)->first();

    // Verificar si se encontró un usuario con ese token de "recordar"
    if (!$user) {
        // No se encontró ningún usuario con el token proporcionado
        return response()->json(['error' => 'El token de recordar no coincide con ningún usuario.'], 401);
    }

    // Autenticar automáticamente al usuario
    Auth::login($user);

    // Generar un token de autenticación de sesión para el usuario
    $sessionToken = $user->createToken('session_token')->plainTextToken;

    // Autenticación exitosa
    return response()->json([
        'user' => $user, 
        'session_token' => $sessionToken,
    ]);
}



public function logout(Request $request)
{
    // Verificar si el usuario está autenticado
    if (!$request->user()) {
        // Usuario no autenticado
        return response()->json(['error' => 'No se puede cerrar sesión porque el usuario no está autenticado.'], 401);
    }

    // Revocar todos los tokens del usuario autenticado (incluyendo el token de remember)
    $request->user()->tokens()->delete();
    
    // Sesión cerrada exitosamente
    return response()->json(['message' => 'Sesión cerrada exitosamente.'], 200);
}

public function register(Request $request)
{
    // Validar los datos del formulario
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'email' => 'required|email|unique:users,email',
        'password' => [
            'required',
            'string',
            'min:8',
            // Utilizar una expresión regular para asegurarse de que la contraseña contenga al menos una letra y un número
            'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ],
    ]);

    // Verificar si la validación falla
    if ($validator->fails()) {
        $errors = $validator->errors();

        // Error de nombre requerido
        if ($errors->has('name')) {
            return response()->json(['error' => 'El nombre es requerido.'], 422);
        }

        // Error de formato de correo electrónico
        if ($errors->has('email')) {
            return response()->json(['error' => 'El formato del correo electrónico es inválido.'], 422);
        }

        // Error de contraseña requerida
        if ($errors->has('password')) {
            return response()->json(['error' => 'La contraseña es requerida y debe tener al menos 8 caracteres, incluyendo al menos una letra y un número.'], 422);
        }
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
    return response()->json(['message' => 'Usuario registrado correctamente. Se ha enviado un correo electrónico de verificación.'],200);
} 

public function verify(Request $request)
{
    // Verificar si la URL está firmada correctamente
    if (!URL::hasValidSignature($request)) {
        // URL de verificación no válida
        return response()->json(['error' => 'URL de verificación no válida.'], 401);
    }

    // Buscar al usuario por su ID
    $user = User::findOrFail($request->id);

    // Verificar si el usuario ya está verificado
    if ($user->hasVerifiedEmail()) {
        // Usuario ya verificado
        return response()->json(['message' => 'El usuario ya ha sido verificado anteriormente.'], 409);
    }

    // Marcar al usuario como verificado
    $user->markEmailAsVerified();

    // Correo electrónico verificado correctamente
    return response()->json(['message' => 'Correo electrónico verificado correctamente.'], 200);
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
             // Usuario no autenticado
             return response()->json(['error' => 'Usuario no autenticado.'], 401);
         }
     
         // Eliminar el usuario de la base de datos
         $request->user()->delete();
     
         // Desconectar al usuario
         $request->user()->tokens()->delete();
         
         // Cuenta eliminada correctamente
         return response()->json(['message' => 'Cuenta eliminada correctamente.']);
     }
     

     public function forgotPassword(Request $request)
     {
         // Validar el correo electrónico proporcionado
         $request->validate(['email' => 'required|email']);
     
         // Generar un token de restablecimiento de contraseña y enviar el correo
         $status = Password::sendResetLink(
             $request->only('email')
         );
     
         // Verificar el estado del envío del correo electrónico
         if ($status === Password::RESET_LINK_SENT) {
             // Correo electrónico enviado con éxito
             return response()->json(['message' => 'Correo electrónico enviado con éxito.'], 200);
         } else {
             // No se pudo enviar el correo electrónico
             return response()->json(['error' => 'No se pudo enviar el correo electrónico.'], 400);
         }
     }
     

    //  public function resetPassword(Request $request)
    //  {
    //      // Validar los datos proporcionados en la solicitud
    //      $request->validate([
    //          'email' => 'required|email',
    //          'token' => 'required|string',
    //          'password' => 'required|string|min:8|confirmed',
    //      ]);
     
    //      // Intentar restablecer la contraseña
    //      $status = Password::reset(
    //          $request->only('email', 'password', 'password_confirmation', 'token'),
    //          function ($user, $password) {
    //              // Guardar la nueva contraseña para el usuario
    //              $user->password = bcrypt($password);
    //              $user->save();
    //          }
    //      );
     
    //      // Verificar el resultado del restablecimiento de contraseña
    //      if ($status === Password::PASSWORD_RESET) {
    //          // Contraseña restablecida con éxito
    //          return response()->json(['message' => 'Contraseña restablecida con éxito.'], 200);
    //      } else {
    //          // No se pudo restablecer la contraseña
    //          return response()->json(['error' => 'No se pudo restablecer la contraseña.'], 400);
    //      }
    //  }
     

}





