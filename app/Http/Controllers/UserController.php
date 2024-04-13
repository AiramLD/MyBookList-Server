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
            'password_confirmation' => 'required|same:password',
        ]);


        
        // Verificar si la validación falla
        if ($validator->fails()) {
            
                $response = [
                    'errors' => null,
                    
                ];
            $errors = $validator->errors();
           
            // Error de nombre requerido (Required name error)
            if ($errors->has('name')) {
                $response['errors']['name'] = 'Name is required.'; // English error message
            }
            
            // Error de formato de correo electrónico (Invalid email format error)
            if ($errors->has('email')) {
                $response['errors']['email'] = 'Invalid email format.'; // English error message
            }
            
            if ($errors->has('email')) {
                $response['errors']['email'] = 'The email has already been taken.'; // English error message
            }
            
            // Error de contraseña requerida y restricciones (Required password and constraints error)
            if ($errors->has('password')) {
                $response['errors']['password'] = 'Password is required and must contain at least one letter and one number.'; // English error message
            }
            if ($errors->has('password_confirmation')) {
                $response['errors']['password_confirmation'] = 'Password must be the same.'; // English error message
            }
            return response()->json($response, 400);
        }else{
            // Crear un nuevo usuario (Create a new user)
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();
            
            // Enviar correo electrónico de verificación (Send verification email)
            $user->sendEmailVerificationNotification();
            
            // Responder con una confirmación (Respond with a confirmation)
            return response()->json(['message' => 'User registered successfully. Please check your email.'], 200);
        }
    }
    
    public function login(Request $request)
    {
        // Validate form data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {

            $response = [
                'errors' => null,
            ];

            $errors = $validator->errors();

            if($errors->has('email')) {
                $response['errors']['email'] = 'Email is required.'; // English error message
            }
            if($errors->has('password')) {
                $response['errors']['password'] = 'Password is required.'; // English error message
            }
            return response()->json($response, 401);
        }
    

        
        // Get the user by email address
        $user = User::where('email', $request->email)->first();
    
        // Check if the user exists
        if (!$user) {
            return response()->json(['errors' => ['email' => 'No user found with that email address.']], 422);
        }else{
            // Check if the user has verified their email
            if (!$user->hasVerifiedEmail()) {
                // If the user has not verified their email, send the verification email
                $user->sendEmailVerificationNotification();
                return response()->json(['errors' => ['email' => 'Please verify your email to complete the registration process.']], 403);
            }    
        }
    
      
        // Generate a session authentication token
        $sessionToken = $user->createToken('session_token')->plainTextToken;
        
        $response=[
         'session_token' => $sessionToken,
        ];
        
        // Check if the user already has a "remember" token
        if($request->remember) {
            $rememberToken = $user->createToken('remember_token')->plainTextToken;
            $user->update(['remember_token' => $rememberToken]);
            $response['remember_token'] = $rememberToken;
        }else{
            $user->update(['remember_token' => null]);
            }

    
        // Successful authentication
        return response()->json([
            $response,
        ], 200);
    }



    
public function getUser(Request $request)
{
    // Obtener el token de sesión de la cabecera de autorización
    $sessionToken = $request->bearerToken();

    // Verificar si se proporcionó un token de sesión
    if (!$sessionToken) {
        // No se proporcionó un token de sesión en el encabezado de autorización
        return response()->json(['errors' => ['sessionToken' => 'No session token provided in the Authorization header.']], 401);
    }

    // Obtener el ID del token de acceso personal desde el encabezado de autorización
    $tokenId = $request->user()->currentAccessToken()->id;

    // Obtener el usuario asociado al token de acceso personal
    $user = $request->user();

    // Verificar si se encontró un usuario asociado al token de acceso personal
    if (!$user) {
        // No se encontró ningún usuario asociado al token de acceso personal
        return response()->json(['errors' => ['sessionToken' => 'No user found with the provided personal access token.']], 401);
    }

    // Éxito: devolver el usuario
    return response()->json(['user' => $user]);
}


public function compareTokens(Request $request)
{
   // Obtener el token de recordatorio de la cabecera de autorización
   $rememberToken = $request->bearerToken();

   // Verificar si se proporcionó un token de recordatorio
   if (!$rememberToken) {
       // No se proporcionó un token de recordatorio en el encabezado de autorización
       return response()->json(['errors' => ['rememberToken' => 'No remember token provided in the Authorization header.']], 401);
   }

   // Obtener el ID del token de acceso personal desde el encabezado de autorización
   $tokenId = $request->user()->currentAccessToken()->id;

   // Obtener el usuario asociado al token de acceso personal
   $user = $request->user();

   // Verificar si se encontró un usuario asociado al token de acceso personal
   if (!$user) {
       // No se encontró ningún usuario asociado al token de acceso personal
       return response()->json(['errors' => ['rememberToken' => 'No user found with the provided personal access token.']], 401);
   }

   $sessionToken = $user->createToken('session_token')->plainTextToken;


   // Éxito: devolver el usuario
   return response()->json(['session_token' => $sessionToken]);
}
    public function logout(Request $request)
    {
        // Check if the user is authenticated
        if (!$request->user()) {
            // Unauthenticated user
            return response()->json(['error' => 'Cannot log out because the user is not authenticated.'], 401);
        }
    
        // Revoke all tokens of the authenticated user (including the remember token)
        $request->user()->tokens()->delete();
        
        // Session closed successfully
        return response()->json(['message' => 'Session closed successfully.'], 200);
    }
    

    public function verify(Request $request)
    {
        // Verificar si la URL está firmada correctamente (Check if URL is properly signed)
        if (!URL::hasValidSignature($request)) {
            // URL de verificación no válida (Invalid verification URL)
            return response()->json(['error' => 'Invalid verification URL.'], 401);
        }
    
        // Buscar al usuario por su ID (Find the user by ID)
        $user = User::findOrFail($request->id);
    
        // Verificar si el usuario ya está verificado (Check if the user is already verified)
        if ($user->hasVerifiedEmail()) {
            // Usuario ya verificado (User already verified)
            return response()->json(['message' => 'The user has already been verified.'], 409);
        }
    
        // Marcar al usuario como verificado (Mark the user as verified)
        $user->markEmailAsVerified();
    
        // Correo electrónico verificado correctamente (Email verified successfully)
        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
    



    /**
     * Remove the specified resource from storage.
     */
    
     public function deleteAccount(Request $request)
{
    // Obtener el usuario autenticado (Get the authenticated user)
    $user = Auth::user();

    // Verificar si el usuario está autenticado (Check if the user is authenticated)
    if (!$user) {
        // Usuario no autenticado (Unauthenticated user)
        return response()->json(['error' => 'Unauthenticated user.'], 401);
    }

    // Eliminar el usuario de la base de datos (Delete the user from the database)
    $request->user()->delete();

    // Desconectar al usuario (Logout the user)
    $request->user()->tokens()->delete();
    
    // Cuenta eliminada correctamente (Account deleted successfully)
    return response()->json(['message' => 'Account deleted successfully.']);
}


public function forgotPassword(Request $request)
{
    // Validate the provided email
    $request->validate(['email' => 'required|email']);
    
    // Generate a password reset token and send the email
    $status = Password::sendResetLink(
        $request->only('email')
    );
    
    // Check the status of the email sending
    if ($status === Password::RESET_LINK_SENT) {
        // Email sent successfully
        return response()->json(['message' => 'Email sent successfully.'], 200);
    } else {
        // Failed to send email
        return response()->json(['error' => 'Failed to send email.'], 400);
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





