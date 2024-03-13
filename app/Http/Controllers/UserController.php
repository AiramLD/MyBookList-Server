<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


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
     

    public function logout() {
        $user = Auth::user();
        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });
    
        return response()->json(['message' => 'Sesión cerrada exitosamente.']);
    }

    public function register(Request $request){

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->save();
        return response()->json(['success' => 'Usuario creado correctamente.'], 200);
    }

    public function deleteAccount() {
        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            // El usuario está autenticado, obtener la instancia del usuario
            $user = Auth::user();
            
            $user2 = User::find($user->id);
            // Ahora puedes llamar al método delete() en la instancia del usuario
            $user2->delete();
    
            // Respondes con un mensaje de éxito
            return response()->json(['message' => 'Cuenta eliminada correctamente.']);
        }
    
        // Si el usuario no está autenticado, respondes con un error
        return response()->json(['error' => 'Usuario no autenticado.'], 401);
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
    public function destroy(User $user)
    {
        //
    }
}
