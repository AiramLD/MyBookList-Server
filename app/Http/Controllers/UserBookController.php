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
}
