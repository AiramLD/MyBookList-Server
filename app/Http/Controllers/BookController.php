<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    private function addBook(Request $request)
    {
        // Validate book data
        $validator = Validator::make($request->all(), [
            'id' => 'required|unique:books',
            'title' => 'required|string',
            'publishedDate' => 'nullable|date',
            'num_pages' => 'required|numeric',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Create a new book with the data provided by the user
        $book = new Book();
        $book->id = $request->input('id');
        $book->title = $request->input('title');
        $book->publishedDate = $request->input('publishedDate');
        $book->num_pages = $request->input('num_pages');
        // You can add more fields depending on the data you receive from the Google Books API

        // Save the book to the database
        $book->save();

        // Respond with a success message
        return response()->json(['message' => 'Book saved successfully.']);
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
            return response()->json(['errors' => ['id' => 'The book was not found.']], 404);
        }
    
        // Retornar el libro encontrado
        return response()->json($book);
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        // Validate form data (optional)
        $request->validate([
            'title' => 'required|string',
            // You can add more validation rules according to your needs
        ]);
    
        // Check if the book exists
        if (!$book) {
            return response()->json(['error' => 'The book was not found.'], 404);
        }
    
        // Update the book with the data received from the form
        $updated = $book->update([
            'title' => $request->input('title'),
            // You can assign values to other columns if necessary
        ]);
    
        // Check if the book was successfully updated
        if ($updated) {
            // Return the updated book as response
            return $book;
        } else {
            return response()->json(['error' => 'The book could not be updated.'], 400);
        }
    }
    
    

    public function destroy(Book $book)
    {
        // Check if the book exists
        if (!$book) {
            return response()->json(['error' => 'The book was not found.'], 404);
        }
    
        // Try to delete the book
        try {
            // Delete the specified book
            $book->delete();
    
            // Return a success response
            return response()->json(['message' => 'Book deleted successfully.'], 200);
        } catch (\Exception $e) {
            // Handle the case where the book cannot be deleted
            return response()->json(['error' => 'The book could not be deleted.'], 400);
        }
    }
    
    
 
}
