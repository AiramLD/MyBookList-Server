<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Book;
use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserBookController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required',
            'title' => 'required',
            'pageCount' => 'required|integer|min:1',
            'state' => 'required|in:reading,completed,pending,paused,dropped',
            'progress' => 'required|integer|min:0|max:' . $request->pageCount,
            'score' => 'required|integer|min:0|max:5',
        ]);
    
        if ($validator->fails()) {

            $response = ['errors' => null];
 
            $errors = $validator->errors();

            if($errors->has('user_id')) $response['errors']['user_id'] = 'User not found.';
            if($errors->has('book_id')) $response['errors']['book_id'] = 'Book not found.';
            if($errors->has('title')) $response['errors']['title'] = 'Book title is required.';
            if($errors->has('pageCount')) $response['errors']['pageCount'] = 'Number of pages is required.';
            if($errors->has('progress')) $response['errors']['progress'] = 'Current page must be between 0 and ' . $request->pageCount . '.';
            if($errors->has('state'))  $response['errors']['state'] = 'state is required.';
            if($errors->has('score'))  $response['errors']['score'] = 'score is required.';

            return response()->json($response, 422);
        }

        $book = Book::updateOrCreate(
            ['id' => $request->book_id],
            [
                'title' => $request->title,
                'pageCount' => $request->pageCount,
            ]
        );
        if ($book) {
            $userBook = UserBook::updateOrCreate(
                ['user_id' => $request->user_id, 'book_id' => $request->book_id],
                [
                    'title' => $request->title,
                    'pageCount' => $request->pageCount,
                    'state' => $request->state,
                    'progress' => $request->progress,
                    'score' => $request->score,
                ]
            );
    
            if($userBook) return response()->json($userBook, 200);
        }
        return response()->json(['error' => 'Collection record could not be saved correctly.'], 400);
    }

    public function show($user_id, $book_id)
    {
        $userBook = UserBook::select(
            'user_books.book_id',
            'books.title',
            'books.pageCount',
            'user_books.progress',
            'user_books.score',
            'user_books.state',
            'user_books.created_at',
            'user_books.updated_at'
        )
        ->join('books', 'user_books.book_id', '=', 'books.id')
        ->where('user_id', $user_id)
        ->where('book_id', $book_id)
        ->first();

        if ($userBook)  {
            return response()->json($userBook, 200);
        } else {
            return response()->json(['error' => 'User-book entry not found.'], 404);
        }
    }

    public function getAll(Request $request)
    {
        $query = UserBook::select(
            'user_books.book_id',
            'books.title',
            'books.pageCount',
            'books.publishedDate',
            'user_books.progress',
            'user_books.score',
            'user_books.state',
            'user_books.created_at',
            'user_books.updated_at'
        )
        ->join('books', 'user_books.book_id', '=', 'books.id')
        ->where('user_id', $request->user_id);

        $state = $request->query('state');

        if ($state) {
            $query = $query->where('state', $state);

            $totalItems = $query->count();
            
            if ($totalItems > 0) {
                return response()->json([
                    'totalItems' => $totalItems,
                    'items' => $query->get()
                ], 200);
            }
        } else {
            return response()->json([
                'totalItems' => $query->count(),
                'items' => $query->get()
            ], 200);
        }

        return response()->json([], 404);
    }

    public function destroy($user_id, $book_id)
    {
        $userBook = UserBook::where('user_id', $user_id)
                            ->where('book_id', $book_id)
                            ->first();

        if ($userBook) {
            $userBook->delete();

            return response()->json(['message' => 'Collection record deleted successfully.'], 200);
        } else {
            return response()->json(['error' => 'Collection record not found.'], 404);
        }
    }
}

