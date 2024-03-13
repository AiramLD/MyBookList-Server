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
        $userBook = new UserBook();

        $userBook->user_id = $request->user_id;
        $userBook->book_id = $request->book_id;
        $userBook->save();

        return response()->json($userBook);
    }

    /**
     * Display the specified resource.
     */
    //hacer este feedback
    public function show(UserBook $userBook)
    {
        return response()->json($userBook);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, UserBook $userBook)
    {
        $userBook = UserBook::find($request->id);

        return response()->json($userBook);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserBook $userBook)
    {
        $userBook = UserBook::find($request->id);
        $userBook->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserBook $userBook)
    {
        $userBook->delete();
    }
    
public function feedback(Request $request, $userbook_id) {
    // Verificar si el usuario está autenticado, puede depender de tu sistema de autenticación
    $user = auth()->user();

    if (!$user) {
        return response()->json(['error' => 'Usuario no autenticado.'], 401);
    }

    // Verificar si el usuario tiene este userbook
    $userBook = UserBook::find($userbook_id);

    if (!$userBook || $userBook->user_id !== $user->id) {
        return response()->json(['error' => 'No se encontró el libro o no tienes acceso a él.'], 404);
    }

    // Guardar el feedback en la base de datos
    $userBook->feedback = $request->input('feedback');
    $userBook->save();

    return response()->json(['success' => 'Feedback guardado correctamente.']);
}
}
