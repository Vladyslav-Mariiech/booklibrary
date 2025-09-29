<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('authors');

        // Search by book title or author
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('authors', function($authorQuery) use ($search) {
                        $authorQuery->where('surname', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('patronymic', 'like', "%{$search}%");
                    });
            });
        }

        // Sort by book title
        $sortOrder = $request->get('sort', 'asc');
        $query->orderBy('title', $sortOrder);

        // Pagination
        $books = $query->paginate(15);
        $authors = Author::all();

        return view('crud.books.index', compact('books', 'authors'));
    }

    public function create()
    {
        return view('crud.books.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'authors' => 'required|array',
            'authors.*' => 'exists:authors,id',
            'published_at' => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUniqueImage($request->file('image'));
        }

        $book = Book::create($data);
        $book->authors()->sync($data['authors']);
        $book->load('authors');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'book' => $book,
                'message' => 'Book added successfully!'
            ]);
        }

        return redirect()->route('crud.books.index')->with('success', 'Book added successfully!');
    }

    public function show(Book $book)
    {
        $book->load('authors');
        return response()->json(['success' => true, 'book' => $book]);
    }

    public function edit(Request $request, Book $book)
    {
        $book->load('authors');
        $authors = Author::all();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'book' => $book,
                'authors' => $authors
            ]);
        }

        return view('crud.books.edit', compact('book', 'authors'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'authors' => 'required|array',
            'authors.*' => 'exists:authors,id',
            'published_at' => 'nullable|date',
            'remove_image' => 'nullable|boolean'
        ]);

        $this->handleImageUpdate($request, $book, $data);

        $book->update($data);
        $book->authors()->sync($data['authors']);
        $book->refresh()->load('authors');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Book updated successfully!',
                'book' => $book
            ]);
        }

        return redirect()->route('crud.books.index')->with('success', 'Book updated successfully!');
    }

    public function destroy(Request $request, Book $book)
    {
        if ($book->image) {
            Storage::disk('public')->delete($book->image);
        }

        $book->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Book deleted successfully!'
            ]);
        }

        return redirect()->route('crud.books.index')->with('success', 'Book deleted successfully!');
    }

    /**
     * Store image with unique name via Storage
     */
    private function storeUniqueImage($file)
    {
        $extension = $file->getClientOriginalExtension();
        $uniqueName = Str::uuid() . '.' . $extension;

        return $file->storeAs('books', $uniqueName, 'public');
    }

    /**
     * Handle image update
     */
    private function handleImageUpdate(Request $request, Book $book, array &$data)
    {
        if ($request->boolean('remove_image')) {
            if ($book->image) {
                Storage::disk('public')->delete($book->image);
            }
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            if ($book->image) {
                Storage::disk('public')->delete($book->image);
            }
            $data['image'] = $this->storeUniqueImage($request->file('image'));
        } else {
            unset($data['image']);
        }
    }
}
