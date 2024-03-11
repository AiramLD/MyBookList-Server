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
        // Validar los datos del formulario (opcional)
        $request->validate([
            'title' => 'required|string',
        ]);

        // Crear un nuevo libro con los datos recibidos del formulario

        $book = Book::create([
            'title' => $request->input('title'),
            // Aquí puedes asignar valores a otras columnas si es necesario
        ]);

        // Devolver el libro recién creado como respuesta
        return $book;
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        // Devolver el libro especificado en la URL
        return $book;
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
}
