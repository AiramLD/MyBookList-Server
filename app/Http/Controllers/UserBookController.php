<?php

namespace App\Http\Controllers;

use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

  
    
    public function store(Request $request)
    {
        // Validate the received data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'progress' => 'nullable|integer|min:0|max:100',
            'score' => 'nullable|integer|min:1|max:5',
            'status' => 'required|in:leido,pendiente,siguiendo,favorito,abandonado',
        ]);
    
        // Check for validation errors
        if ($validator->fails()) {
            $errors = $validator->errors();
    
            // Respond with validation error details
            return response()->json(['error' => 'Validation error.', 'details' => $errors], 422);
        }
    
        // Create a new user-book entry
        $userBook = new UserBook();
        $userBook->user_id = $request->user_id;
        $userBook->book_id = $request->book_id;
        $userBook->progress = $request->progress ?? 0;
        $userBook->score = $request->score ?? 0;
        $userBook->status = $request->status;
        $userBook->save();
    
        // Respond with a success message
        return response()->json(['message' => 'Book saved to user list successfully.'], 201);
    }
    

    
    
   
    public function show(Request $request)
    {
        // Validar los datos recibidos en la solicitud
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
        ]);
    
        // Obtener el user_id y el book_id de la solicitud
        $userId = $request->input('user_id');
        $bookId = $request->input('book_id');
    
        // Buscar el libro del usuario por su ID de usuario y ID de libro
        $userBook = UserBook::where('user_id', $userId)->where('book_id', $bookId)->first();
    
        // Verificar si el libro del usuario existe
        if (!$userBook) {
            return response()->json(['error' => 'User book not found.', 'details' => [
                'user_id' => ['The selected user id is invalid.'],
                'book_id' => ['The selected book id is invalid.']
            ]], 404);
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
   public function update(Request $request)
{
    // Validate the data received in the request
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'book_id' => 'required|exists:books,id',
        'progress' => 'nullable|integer|min:0|max:100',
        'score' => 'nullable|integer|min:1|max:5',
        'status' => 'nullable|in:leido,pendiente,siguiendo,favorito,abandonado',
    ]);

    // Check for validation errors
    if ($validator->fails()) {
        return response()->json(['error' => 'Validation error.', 'details' => $validator->errors()], 422);
    }

    // Find the user_book record
    $userBook = UserBook::where('user_id', $request->user_id)
                        ->where('book_id', $request->book_id)
                        ->first();

    // Check if the record exists
    if (!$userBook) {
        return response()->json(['error' => 'The user or the book does not exist.'], 404);
    }

    // Update the fields if provided in the request
    if ($request->has('progress')) {
        $userBook->progress = $request->progress;
    }
    if ($request->has('score')) {
        $userBook->score = $request->score;
    }
    if ($request->has('status')) {
        $userBook->status = $request->status;
    }

    // Save the changes to the database
    $userBook->save();

    // Respond with a success message
    return response()->json(['message' => 'User-book record updated successfully.'], 200);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'user_book_id' => 'required|exists:user_books,id'
        ]);
    
        // Obtener el ID del libro de usuario desde la solicitud
        $userBookId = $request->input('user_book_id');
    
        // Buscar el registro de libro de usuario
        $userBook = UserBook::find($userBookId);
    
        // Verificar si el registro existe
        if (!$userBook) {
            return response()->json(['error' => 'User-book record not found.'], 404);
        }
    
        // Eliminar el registro de libro de usuario
        $userBook->delete();
    
        // Devolver una respuesta de éxito
        return response()->json(['message' => 'User-book record deleted successfully.']);
    }
    

}
