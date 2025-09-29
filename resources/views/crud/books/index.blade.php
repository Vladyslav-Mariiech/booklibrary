@extends('layouts.crud')

@section('content')
    <div class="container">
        <h1>Books</h1>

        <!-- Control Panel -->
        <div class="control-panel">
            <button class="btn btn-primary" data-modal-target="#bookModal">
                Add Book
            </button>

            <!-- Search and Sort -->
            <div class="search-panel">
                <form method="GET" action="{{ route('crud.books.index') }}" class="search-form">
                    <div class="search-group">
                        <input type="text" name="search"
                               placeholder="Search by title, author..."
                               value="{{ request('search') }}"
                               class="search-input">
                        <button type="submit" class="btn btn-secondary">Search</button>
                        @if(request('search'))
                            <a href="{{ route('crud.books.index') }}" class="btn btn-secondary">Reset</a>
                        @endif
                    </div>
                </form>

                <div class="sort-group">
                    <label>Sort by title:</label>
                    <a href="{{ route('crud.books.index', ['sort' => 'asc', 'search' => request('search')]) }}"
                       class="btn btn-sm {{ request('sort', 'asc') === 'asc' ? 'btn-primary' : 'btn-secondary' }}">
                        ↑ A-Z
                    </a>
                    <a href="{{ route('crud.books.index', ['sort' => 'desc', 'search' => request('search')]) }}"
                       class="btn btn-sm {{ request('sort') === 'desc' ? 'btn-primary' : 'btn-secondary' }}">
                        ↓ Z-A
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        <table class="table">
            <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Authors</th>
                <th>Published Date</th>
                <th>Cover</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($books as $book)
                <tr>
                    <td>{{ $book->title }}</td>
                    <td>{{ Str::limit($book->description, 50) }}</td>
                    <td>
                        @foreach($book->authors as $author)
                            {{ $author->surname }} {{ $author->name }}<br>
                        @endforeach
                    </td>
                    <td>{{ $book->published_at?->format('d.m.Y') }}</td>
                    <td>
                        @if($book->image_url)
                            <img src="{{ $book->image_url }}" width="60" alt="Cover">
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm editBookBtn" data-id="{{ $book->id }}">
                            Edit
                        </button>

                        <form action="{{ route('crud.books.destroy', $book) }}" method="POST" style="display:inline;" class="delete-book-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No books found</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($books->hasPages())
            <div class="pagination-wrapper">
                {{ $books->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <!-- Modal "Add Book" -->
    <div class="modal" id="bookModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title">Add Book</h5>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="createBookForm" action="{{ route('crud.books.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Published Date</label>
                        <input type="date" name="published_at" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Authors *</label>
                        <div id="authors-checkboxes-container" style="border:1px solid #ccc; padding:10px; height:150px; overflow-y:auto;">
                            @foreach($authors as $author)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="authors[]" value="{{ $author->id }}" id="author-{{ $author->id }}">
                                    <label class="form-check-label" for="author-{{ $author->id }}">
                                        {{ $author->surname }} {{ $author->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Select book authors</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cover (jpg/png, max 2MB)</label>
                        <input type="file" name="image" class="form-control" accept="image/png,image/jpeg">
                    </div>

                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal "Edit Book" -->
    <div class="modal" id="editBookModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title">Edit Book</h5>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editBookForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-book-id" name="book_id">

                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" id="edit-title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Published Date</label>
                        <input type="date" name="published_at" id="edit-published-at" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Authors *</label>
                        <div id="edit-authors-container" style="border:1px solid #ccc; padding: 10px; height: 150px; overflow-y: auto;"></div>
                        <small class="text-muted">Select book authors</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Cover (jpg/png, max 2MB)</label>
                        <input type="file" name="image" id="edit-image" class="form-control" accept="image/png,image/jpeg">
                        <small class="text-muted">Leave empty if you don’t want to change the cover</small>
                    </div>

                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                </form>
            </div>
        </div>
    </div>
@endsection
