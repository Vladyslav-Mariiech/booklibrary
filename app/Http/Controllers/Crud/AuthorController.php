<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $query = Author::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('surname', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('patronymic', 'like', "%{$search}%");
            });
        }

        $sortOrder = $request->get('sort', 'asc');
        $query->orderBy('surname', $sortOrder);

        $authors = $query->paginate(15);

        return view('crud.authors.index', compact('authors'));
    }

    public function create()
    {
        return view('crud.authors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'surname' => 'required|string|min:3|max:255',
            'name' => 'required|string|min:1|max:255',
            'patronymic' => 'nullable|string|max:255',
        ]);

        $author = Author::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Author added.',
                'author' => $author
            ]);
        }

        return redirect()->route('crud.authors.index')->with('success', 'Author added.');
    }

    public function show(Request $request, Author $author)
    {
        $author->load('books');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'author' => $author
            ]);
        }

        return view('crud.authors.show', compact('author'));
    }

    public function edit(Request $request, Author $author)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'author' => $author
            ]);
        }

        return view('crud.authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $data = $request->validate([
            'surname' => 'required|string|min:3|max:255',
            'name' => 'required|string|min:1|max:255',
            'patronymic' => 'nullable|string|max:255',
        ]);

        $author->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'The author has been updated.',
                'author' => $author->fresh()
            ]);
        }

        return redirect()->route('crud.authors.index')->with('success', 'The author has been updated.');
    }

    public function destroy(Request $request, Author $author)
    {
        $booksCount = $author->books()->count();

        if ($booksCount > 0) {
            $message = "You can't delete an author who has books. ({$booksCount})";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }

            return redirect()->back()->with('error', $message);
        }

        $author->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'The author has been removed.'
            ]);
        }

        return redirect()->route('crud.authors.index')->with('success', 'The author has been removed.');
    }
}
