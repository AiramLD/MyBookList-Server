<?php

namespace App\Http\Controllers;

use App\Models\UserBook;
use Illuminate\Http\Request;

class UserBookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userBook = UserBook::all();
        
        return response()->json($userBook);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return response()->json(['error' => 'Usuario no autenticado.'], 401);
        }
    
        // Validar los datos recibidos
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|exists:users,id',
            'progress' => 'nullable|numeric|min:0|max:100',
            'score' => 'nullable|numeric|min:1|max:5',
            'status' => 'required|in:leido,pendiente,siguiendo,favorito,abandonado',
        ]);
    
        // Verificar si hay errores en la validación
        if ($validator->fails()) {
            return response()->json(['error' => 'Error en la validación.', 'details' => $validator->errors()], 422);
        }
    
        // Crear una nueva entrada de libro de usuario
        $userBook = new UserBook();
        $userBook->book_id = $request->book_id;
        $userBook->user_id = $request->user_id;
        $userBook->progress = $request->progress;
        $userBook->score = $request->score;
        $userBook->status = $request->status;
        $userBook->save();
    
        // Responder con un mensaje de éxito
        return response()->json(['message' => 'Libro guardado en la lista de usuario correctamente.'], 201);
    }
    
    /**
     * Display the specified resource.
     */
    //hacer este feedback
    public function show($userId, $bookId)
{
    // Buscar el libro del usuario por su ID de usuario y ID de libro
    $userBook = UserBook::where('user_id', $userId)->where('book_id', $bookId)->first();

    // Verificar si el libro del usuario existe
    if (!$userBook) {
        return response()->json(['error' => 'El libro del usuario no se encontró.'], 404);
    }

    // Retornar el libro del usuario encontrado
    return response()->json($userBook);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, UserBook $userBook)
{
    // Buscar el libro del usuario por su ID
    $userBook = UserBook::find($request->id);

    // Verificar si el libro del usuario existe
    if (!$userBook) {
        return response()->json(['error' => 'El libro del usuario no se encontró.'], 404);
    }

    // Retornar el libro del usuario encontrado
    return response()->json($userBook);
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $userId, $bookId)
{
    // Validar los datos recibidos en la solicitud
    $request->validate([
        'progress' => 'nullable|integer|min:0|max:100',
        'score' => 'nullable|integer|min:1|max:5',
        'status' => 'nullable|in:leido,pendiente,siguiendo,favorito,abandonado',
    ]);

    // Buscar el registro de user_book
    $userBook = UserBook::where('user_id', $userId)->where('book_id', $bookId)->first();

    // Verificar si el registro existe
    if (!$userBook) {
        return response()->json(['error' => 'El usuario o el libro no existen.'], 404);
    }

    // Actualizar los campos si se proporcionan en la solicitud
    if ($request->has('progress')) {
        $userBook->progress = $request->progress;
    }
    if ($request->has('score')) {
        $userBook->score = $request->score;
    }
    if ($request->has('status')) {
        $userBook->status = $request->status;
    }

    // Guardar los cambios en la base de datos
    $userBook->save();

    // Responder con un mensaje de éxito
    return response()->json(['message' => 'Registro de usuario-libro actualizado correctamente.'], 200);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserBook $userBook)
    {
        // Verificar si el libro del usuario existe
        if (!$userBook) {
            return response()->json(['error' => 'La lista del usuario no se encontró.'], 404);
        }
    
        // Eliminar el libro del usuario
        $userBook->delete();
    
        // Devolver una respuesta de éxito
        return response()->json(['message' => 'La lista del usuario se ha eliminado correctamente.']);
    }
    

}
