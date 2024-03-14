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


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

     public function login(Request $request) {
         $credentials = $request->only('email', 'password');
     
         // Obtener el usuario por su dirección de correo electrónico
         $user = User::where('email', $credentials['email'])->first();
     
         // Verificar si el usuario existe y si la contraseña coincide
         if ($user && Hash::check($credentials['password'], $user->password)) {
             // Autenticación exitosa
             return response()->json(['user' => $user]);
         }
     
         // Autenticación fallida
         return response()->json(['error' => 'Credenciales incorrectas.'], 401);
     }
     

     public function logout(Request $request)
     {
         Auth::logout();
 
         return response()->json(['message' => 'Sesión cerrada exitosamente.']);
     }

     public function register(Request $request)
     {
         // Validar los datos del formulario
         $request->validate([
             'name' => 'required|string',
             'email' => 'required|email|unique:users,email',
             'password' => 'required|string|min:6',
         ]);
     
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

        // Retornar una respuesta adecuada
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
    public function destroy(Request $request, User $usuario)
    {
        // Eliminar el usuario de la base de datos
        $usuario->delete();

        // Enviar respuesta al cliente
        return response()->json([
            'mensaje' => 'Usuario eliminado correctamente',
        ], 200);
    }
    
    public function forgotPassword(Request $request)
    {
        // Validar el correo electrónico del usuario
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
    
        // Generar un token único
        $token = Str::random(60);
    
        // Guardar el token en la base de datos
        DB::table('password_resets')->updateOrInsert([
            'email' => $request->email,
        ], [
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);
    
        // Obtener el usuario por su correo electrónico
        $user = User::where('email', $request->email)->first();
    
        // Construir el contenido del correo electrónico
        $content = "Haga clic en el siguiente enlace para restablecer su contraseña: " . 
                   "<a href='" . route('password.reset', ['token' => $token]) . "'>Restablecer contraseña</a>";
    
        // Enviar el correo electrónico al usuario
        Mail::raw($content, function ($message) use ($user) {
            $message->to($user->email, $user->name)
                    ->subject('Solicitud de restablecimiento de contraseña');
        });
    
        // Responder con un mensaje de éxito
        return response()->json(['message' => 'Se ha enviado un correo electrónico con instrucciones para restablecer su contraseña.']);
    }


}
