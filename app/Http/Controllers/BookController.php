<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los libros
        $books = Book::all();
        
        // Devolver los libros como respuesta
        return $books;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // No es necesario implementar esta acción si no estás usando vistas para crear libros
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos del libro, esto puede variar dependiendo de tus requisitos
        $request->validate([
            'id' => 'required',
            'title' => 'required|string',
            'publishedDate' => 'required',
            'num_pages'=> 'required|numeric',

            // Puedes agregar más reglas de validación según tus necesidades
        ]);

        // Crear un nuevo libro con los datos proporcionados por el usuario
        $book = new Book();
        
        $book->id = $request->input('id');
        $book->title = $request->input('title');
        $book->publishedDate = $request->input('publishedDate');
        $book->num_pages = $request->input('num_pages');
        // Puedes agregar más campos según los datos que recibas de la API de Google Books

        // Guardar el libro en la base de datos
        $book->save();

        // Responder con un mensaje de éxito
        return response()->json(['message' => 'Libro guardado correctamente.']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Buscar el libro por su ID
        $book = Book::find($id);

        // Verificar si el libro existe
        if (!$book) {
            return response()->json(['error' => 'El libro no se encontró.'], 404);
        }

        // Retornar el libro encontrado
        return response()->json($book);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        // No es necesario implementar esta acción si no estás usando vistas para editar libros
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        // Validar los datos del formulario (opcional)
        $request->validate([
            'title' => 'required|string',
            // Aquí puedes agregar más reglas de validación según tus necesidades
        ]);

        // Actualizar el libro con los datos recibidos del formulario
        $book->update([
            'title' => $request->input('title'),
            // Aquí puedes asignar valores a otras columnas si es necesario
        ]);

        // Devolver el libro actualizado como respuesta
        return $book;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        // Eliminar el libro especificado
        $book->delete();

        // Devolver una respuesta de éxito
        return response()->json(['message' => 'Book deleted successfully'], 200);
    }
    
    // public function feedback(Request $request, $book_id) {
    //     // Verificar si el usuario está autenticado, puede depender de tu sistema de autenticación
    //     $user = auth()->user();
    //     if (!$user) {
    //         return response()->json(['error' => 'Usuario no autenticado.'], 401);
    //     }else{
    //         $book = Book::find($book_id);
    //         if (!$book) {
    //             return response()->json(['error' => 'No se encontró el libro.'], 404);
    //         }
    //         $book->feedback = $request->input('feedback');
    //         $book->save();
    //         return response()->json($book);
    //     }

    // }
}
