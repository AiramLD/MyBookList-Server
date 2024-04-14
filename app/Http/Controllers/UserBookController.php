<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Book;
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
            'book_id' => 'required',
            'book_title' => 'required',
            'num_pages' => 'required|integer',
            'current_page' => 'required|integer|min:1|max:' . $request->num_pages,
            'score' => 'nullable|integer|min:1|max:5',
            'status' => 'required|in:reading,pending,paused,completed,dropped',
        ]);
    
        // Check for validation errors
        if ($validator->fails()) {

            $response= [
                'errors' => null,
            ];
            $errors = $validator->errors();

            if($errors->has('user_id')) {
                $response['errors']['user_id'] = 'User not found.'; // English error message
            }
            if($errors->has('book_id')) {
                $response['errors']['book_id'] = 'Book not found.'; // English error message
            }
            if($errors->has('current_page')) {
                $response['errors']['current_page'] = 'Current page must be between 1 and ' . $request->num_pages . '.'; // English error message
            }
            if($errors->has('book_title')) {
                $response['errors']['book_title'] = 'Book title is required.'; // English error message
            }
            if($errors->has('num_pages')) {
                $response['errors']['num_pages'] = 'Number of pages is required.'; // English error message
            }
            if($errors->has('status')) {
                $response['errors']['status'] = 'Status is required.'; // English error message
            }
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Check if the user-book entry already exists
        $userBook = UserBook::where('user_id', $request->user_id)
                            ->where('book_id', $request->book_id)
                            ->first();
    
        if ($userBook) {
            return response()->json(['errors' => ['user_id' => 'The user-book entry already exists.']], 409);
        }
        $progressPercentage = ($request->current_page / $request->num_pages) * 100;

    
        // Create a new user-book entry
        $userBook = new UserBook();
        $userBook->user_id = $request->user_id;
        $userBook->book_id = $request->book_id;
        $userBook->book_title = $request->book_title;
        $userBook->num_pages = $request->num_pages;
        $userBook->current_page = $request->current_page;
        $userBook->progress = $progressPercentage;
        $userBook->score = $request->score ?? 0;
        $userBook->status = $request->status;
        $userBook->save();
    
        // Respond with a success message
        return response()->json(['message' => 'Book saved to user list successfully.'], 201);
    }

    
    
   
    public function show(Request $request)
{
    // Validar los datos recibidos en la solicitud
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'book_id' => 'required',
    ]);

    // Verificar si hay errores de validación
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 404);
    }

    // Buscar el usuario-libro
    $userBook = UserBook::where('user_id', $request->user_id)
                        ->where('book_id', $request->book_id)
                        ->first();

    // Verificar si existe la entrada de usuario-libro
    if (!$userBook) {
        return response()->json(['error' => 'User-book entry not found.'], 404);
    }

    // Ocultar el campo ID
    $userBook->makeHidden(['id']);

    // Responder con la entrada de usuario-libro
    return response()->json(['user_book' => $userBook], 200);
}



    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request)
{
    // Validar los datos recibidos en la solicitud
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'book_id' => 'required',
        'current_page' => 'required|integer|min:1',
        'score' => 'nullable|integer|min:1|max:5',
        'status' => 'required|in:reading,pending,paused,completed,dropped',
    ]);

    if ($validator->fails()) {
        $response = [
            'errors' => null,
        ];
        $errors = $validator->errors();

        if ($errors->has('user_id')) {
            $response['errors']['user_id'] = 'User not found.'; // English error message
        }
        if ($errors->has('book_id')) {
            $response['errors']['book_id'] = 'Book not found.'; // English error message
        }
        if ($errors->has('current_page')) {
            $response['errors']['current_page'] = 'Current page must be a positive integer.'; // English error message
        }
        if ($errors->has('status')) {
            $response['errors']['status'] = 'Status is required.'; // English error message
        }
        return response()->json($response, 404);
    }

    // Buscar el registro de libro de usuario
    $userBook = UserBook::where('user_id', $request->user_id)
                        ->where('book_id', $request->book_id)
                        ->first();

    // Verificar si el registro existe
    if (!$userBook) {
        return response()->json(['error' => 'The user-book entry does not exist.'], 404);
    }

    // Calcular el progreso como un porcentaje
    $progressPercentage = ($request->current_page / $userBook->num_pages) * 100;

    // Actualizar los campos si se proporcionaron en la solicitud
    $userBook->current_page = $request->current_page;
    $userBook->progress = $progressPercentage;
    $userBook->score = $request->score ?? 0;
    $userBook->status = $request->status;

    // Guardar los cambios en la base de datos
    $userBook->save();

    // Responder con un mensaje de éxito
    return response()->json(['message' => 'User-book record updated successfully.'], 200);
}


 
public function destroy(Request $request)
{
    // Validar los datos de la solicitud
    $validator = Validator::make($request->all(), [
        'user_book_id' => 'required|exists:user_books,id'
    ]);

    // Verificar si la validación falla
    if ($validator->fails()) {
        $response = [
            'errors' => $validator->errors(),
        ];
        return response()->json($response, 404);
    }

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



  
    public function getAllStatus(Request $request){

        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $userId = $user->id;
        $books = UserBook::select('book_id', 'book_title','num_pages','current_page', 'progress', 'score', 'status', 'created_at', 'updated_at')
                        ->where('user_id', $userId)
                        ->get();
        return response()->json(['books' => $books]);
    }
    

    public function getReading(Request $request)
    {
        // Obtener el usuario autenticado
        $user = $request->user();
    
        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Obtener el ID del usuario
        $userId = $user->id;
    
        // Obtener los libros leídos del usuario sin incluir el ID autogenerado
        $books = UserBook::select('book_id', 'book_title', 'num_pages','current_page', 'progress', 'score', 'status', 'created_at', 'updated_at')
                        ->where('user_id', $userId)
                        ->where('status', 'reading')
                        ->get();
    
        return response()->json(['books' => $books]);
    }
    
    public function getPending(Request $request)
    {
        // Obtener el usuario autenticado
        $user = $request->user();
    
        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Obtener el ID del usuario
        $userId = $user->id;
    
        // Obtener los libros pendientes del usuario sin incluir el ID autogenerado
        $books = UserBook::select('book_id', 'book_title', 'num_pages','current_page', 'progress', 'score', 'status', 'created_at', 'updated_at')
                        ->where('user_id', $userId)
                        ->where('status', 'pending')
                        ->get();
    
        return response()->json(['books' => $books]);
    }
    

    public function getPaused(Request $request)
    {
        // Obtener el usuario autenticado
        $user = $request->user();
        
        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Obtener el ID del usuario
        $userId = $user->id;
    
        // Obtener los libros que el usuario está siguiendo sin incluir el ID autoincremental
        $books = UserBook::where('user_id', $userId)
                        ->where('status', 'paused')
                        ->select(['user_id', 'book_id', 'book_title', 'num_pages','current_page','progress', 'score', 'status', 'created_at', 'updated_at'])
                        ->get();
    
        return response()->json(['books' => $books]);
    }
    


    public function getCompleted(Request $request)
    {
        // Obtener el usuario autenticado
        $user = $request->user();
        
        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Obtener el ID del usuario
        $userId = $user->id;
    
        // Obtener los libros favoritos del usuario sin incluir el ID autoincremental
        $books = UserBook::where('user_id', $userId)
                        ->where('status', 'completed')
                        ->select(['user_id', 'book_id', 'book_title','num_pages', 'current_page', 'progress', 'score', 'status', 'created_at', 'updated_at'])
                        ->get();
    
        return response()->json(['books' => $books]);
    }

    public function getDropped(Request $request)
    {
        // Obtener el usuario autenticado
        $user = $request->user();
        
        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Obtener el ID del usuario
        $userId = $user->id;
    
        // Obtener los libros abandonados por el usuario sin incluir el ID autoincremental
        $books = UserBook::where('user_id', $userId)
                        ->where('status', 'dropped')
                        ->select(['user_id', 'book_id', 'book_title','num_pages','current_page', 'progress', 'score', 'status', 'created_at', 'updated_at'])
                        ->get();
    
        return response()->json(['books' => $books]);
    }
}

